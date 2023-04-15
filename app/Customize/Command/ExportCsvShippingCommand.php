<?php

declare(strict_types=1);

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Command;

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstShippingWSEOS;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
use Customize\Service\CurlPost;
use Customize\Service\FTPService;
use Customize\Service\MailService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console export-csv-shipping-command */
class ExportCsvShippingCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $csvService;
    private $ftpService;
    private $commonService;
    /**
     * @var MailService
     */
    private $mailService;

    protected static $defaultName = 'export-csv-shipping-command';
    protected static $defaultDescription = 'Process Export Shipping Csv Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->csvService = new CSVService($entityManager);
        $this->ftpService = new FTPService($entityManager);
        $this->mailService = $mailService;
        $this->commonService = new MyCommonService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        /* The local path to load csv file */
        $path = getenv('LOCAL_FTP_UPLOAD_DIRECTORY') ?? '/html/upload/';
        $path .= 'csv/shipping/';

        if (getenv('APP_IS_LOCAL') == 1) {
            $path = '.'.$path;
        }

        log_info('----------------------------------');

        log_info('Start Process Export Shipping Csv for month '.date('m'));
        $this->handleExportShippingCsv($path.date('/Y/m'));
        log_info('End Process Export Shipping Csv for month '.date('m'));

        return 0;
    }

    private function handleExportShippingCsv($path)
    {
        if (empty($path)) {
            return;
        }

        $mstShippingWSEOS = $this->entityManager->getRepository(MstShippingWSEOS::class)->findBy([
            'shipping_send_flg' => 1,
            'shipping_sent_flg' => 0,
        ]);

        if (!count($mstShippingWSEOS)) {
            log_info('No data');

            return;
        }

        $file_name = getenv('FTP_UPLOAD_SHIPPING_FILE_NAME') ?? 'SYUKA-NEW.csv';
        $file = $path.'/'.$file_name;

        // Create directory local if have'n
        $arr_path_local = array_diff(explode('/', $path), ['.', '..']);
        $temp_path_local = '';

        if (getenv('APP_IS_LOCAL') == 1) {
            $temp_path_local = '.';
        }

        foreach ($arr_path_local as $subDir) {
            if (empty($subDir)) {
                continue;
            }
            $temp_path_local .= '/'.$subDir;

            if (file_exists($temp_path_local) == false) {
                mkdir($temp_path_local);
            }
        }
        $temp_path_local = null;
        // End - Create directory local if have'n

        $fp = fopen(trim($file), 'w');

        if ($fp) {
            foreach ($mstShippingWSEOS as $item) {
                try {
                    $dtOrderWsEOS = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy(['order_no' => $item['order_no'], 'order_line_no' => $item['order_line_no']]);

                    if (empty($dtOrderWsEOS) || $dtOrderWsEOS['shipping_sent_flg'] != 1) {
                        continue;
                    }

                    $mstDelivery = $this->commonService->getMstDelivery($item['shipping_no'], $item['order_no'], $item['order_line_no']);
                    $delivery_no = $mstDelivery['delivery_no'] ?? null;
                    $delivery_line_no = $mstDelivery['delivery_lineno'] ?? null;
                    $delivery_day = $mstDelivery['delivery_date'] ?? null;
                    $delivery_num = $mstDelivery['quanlity'] ?? 0;
                    $delivery_price = $mstDelivery['unit_price'] ?? 0;
                    $delivery_amount = $mstDelivery['amount'] ?? 0;

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();

                    $fields = [
                        mb_convert_encoding($item['system_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['sales_company_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['sales_shop_code'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_no'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_no, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['delivery_type'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding(!empty($item['delivery_day']) ? date('Ymd', strtotime($item['delivery_day'])) : '', 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding(!empty($delivery_day) ? date('Ymd', strtotime($delivery_day)) : '', 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['delivery_flag_tmp'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_company_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_shop_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['shipping_company_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['shipping_shop_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['shipping_name'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['import_type'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['system_code1'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['sales_company_code1'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['sales_ship_code1'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_no1'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_line_no'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_no, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_line_no, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['delivery_type1'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_type'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_no'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_line_no'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_flag'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_staff_name'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_shop_name'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['make_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['maker_name'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['product_name'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_num'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_price'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_amount'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_num'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_price'], 'Shift-JIS', 'UTF-8'),
                        //mb_convert_encoding($item['delivery_amount'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_num, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_price, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($delivery_amount, 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['tax_type'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding(!empty($item['order_date']) ? date('Ymd', strtotime($item['order_date'])) : '', 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding(!empty($item['shipping_date']) ? date('Ymd', strtotime($item['shipping_date'])) : '', 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['remarks_line_no'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['jan_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['unit_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['shipping_num'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['order_unit_num'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['product_maker_code'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['open_price_type'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['price_basic'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['price_list'], 'Shift-JIS', 'UTF-8'),
                    ];

                    $item->setShippingSendFlg(0);
                    $item->setShippingSentFlg(1);
                    $this->entityManager->getRepository(MstShippingWSEOS::class)->save($item);

                    if (!empty($dtOrderWsEOS)) {
                        $dtOrderWsEOS->setShippingSentFlg(1);
                        $this->entityManager->getRepository(DtOrderWSEOS::class)->save($dtOrderWsEOS);
                    }

                    fputcsv($fp, $fields);

                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->entityManager->getConnection()->rollBack();
                    $this->pushGoogleChat($e->getMessage());
                }
            }

            fclose($fp);
        }

        return;
    }
}
