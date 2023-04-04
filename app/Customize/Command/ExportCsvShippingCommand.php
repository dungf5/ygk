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
use Customize\Entity\DtExportCSV;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstShippingWSEOS;
use Customize\Repository\DtOrderWSEOSRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
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
        $this->handleExportShippingCsv($path.date('/Y/m/'));
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

        $dtExporCSv = $this->commonService->getDtExportCsv();
        $increment = !empty($dtExporCSv) ? (int) $dtExporCSv['increment'] : 0;
        $file_name = 'SYUKA-NEW'.date('Ymd_').($increment + 1).'.csv';
        $file = $path.$file_name;

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

        $fp = fopen(strtolower(trim($file)), 'w');

        foreach ($mstShippingWSEOS as $item) {
            $fields = [
                $item['system_code'],
                $item['sales_company_code'],
                $item['sales_shop_code'],
                $item['delivery_no'],
                $item['delivery_type'],
                $item['delivery_day'],
                $item['delivery_flag_tmp'],
                $item['order_company_code'],
                $item['order_shop_code'],
                $item['shipping_company_code'],
                $item['shipping_shop_code'],
                $item['shipping_name'],
                $item['import_type'],
                $item['system_code1'],
                $item['sales_company_code1'],
                $item['sales_ship_code1'],
                $item['delivery_no1'],
                $item['delivery_line_no'],
                $item['delivery_type1'],
                $item['order_type'],
                $item['order_no'],
                $item['order_line_no'],
                $item['order_flag'],
                $item['order_staff_name'],
                $item['order_shop_name'],
                $item['make_code'],
                $item['maker_name'],
                $item['product_name'],
                $item['order_num'],
                $item['order_price'],
                $item['order_amount'],
                $item['delivery_num'],
                $item['delivery_price'],
                $item['delivery_amount'],
                $item['tax_type'],
                $item['order_date'],
                $item['shipping_date'],
                $item['remarks_line_no'],
                $item['jan_code'],
                $item['unit_code'],
                $item['shipping_num'],
                $item['order_unit_num'],
                $item['product_maker_code'],
                $item['open_price_type'],
                $item['price_basic'],
                $item['price_list'],
            ];
            fputcsv($fp, $fields);

            $item->setShippingSentFlg(1);
            $this->entityManager->getRepository(MstShippingWSEOS::class)->save($item);

            $dtOrderWsEOS = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy(['order_no' => $item['order_no'], 'order_line_no' => $item['order_line_no']]);
            if (!empty($dtOrderWsEOS)) {
                $dtOrderWsEOS->setOrderRegistedFlg(0);
                $dtOrderWsEOS->setShippingSentFlg(1);
                $this->entityManager->getRepository(DtOrderWSEOS::class)->save($dtOrderWsEOS);
            }
        }

        fclose($fp);

        // Save file information to DB
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $insertDate = [
            'file_name' => strtolower(trim($file_name)),
            'increment' => $increment + 1,
            'directory' => $path,
            'message' => null,
            'is_error' => 0,
            'is_send_mail' => 0,
            'in_date' => new \DateTime(),
            'up_date' => null,
        ];
        $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);

        return;
    }
}
