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

use Customize\Config\CSVHeader;
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\NatStockList;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
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

/* Run Batch: php bin/console export-nat-stock-list-command */
class ExportNatStockCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;
    use CSVHeader;

    /** @var EntityManagerInterface */
    private $entityManager;
    /**
     * @var MailService
     */
    private $mailService;
    /**
     * @var MyCommonService
     */
    private $commonService;
    private $customer_code = '7015';
    private $shipping_code = '7015001000';

    protected static $defaultName = 'export-nat-stock-list-command';
    protected static $defaultDescription = 'Process Export Nat Stock List Data';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->commonService = new MyCommonService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        log_info('---------------------------------------');
        log_info('Start Process Export Nat Stock List Data');

        $this->handleProcess();

        log_info('End Process Export Nat Stock List Data');

        return 0;
    }

    private function handleProcess()
    {
        $data = $this->handleGetData();
        $this->handleExportData($data);
    }

    private function handleGetData()
    {
        log_info('Start get data');
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $data = $this->entityManager->getRepository(NatStockList::class)->findAll();
        log_info('End get data');

        return $data;
    }

    private function handleExportData($data)
    {
        log_info('Start export data from nat_stock_list to csv');

        if (empty($data)) {
            log_info('No data');
        }

        /* The local path to upload csv file */
        $path = getenv('LOCAL_FTP_UPLOAD_DIRECTORY') ?? '/html/upload/';
        $path .= 'csv/nat/';
        $file_name = 'zaiko_'.date('Ymd').'.csv';

        if (getenv('APP_IS_LOCAL') == 1) {
            $path = '.'.$path;
        }

        if (empty($path)) {
            return;
        }

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

        $file = $path.$file_name;
        $fp = fopen(trim($file), 'w');

        if ($fp) {
            $headerFields = [];
            foreach ($this->getNatExportCsvHeader() as $header) {
                $headerFields[] = mb_convert_encoding($header, 'Shift-JIS', 'UTF-8');
            }
            fputcsv($fp, $headerFields);

            foreach ($data as $item) {
                $fields = [
                    mb_convert_encoding($item['jan'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['mkrcd'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['mkrcd'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['nat_stock_num'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding(!empty($item['delivery_date']) ? date('Y/m/d', strtotime($item['delivery_date'])) : '', 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['quanlity'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['order_lot'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['unit_price'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['product_code'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['catalog_code'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['color'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['size'], 'Shift-JIS', 'UTF-8'),
                    mb_convert_encoding($item['stock_num'], 'Shift-JIS', 'UTF-8'),
                ];

                fputcsv($fp, $fields);
            }

            fclose($fp);
        }

        // Check file after put data
        if (($fp = fopen(trim($file), 'r')) !== false) {
            $str = fread($fp, 100);
            fclose($fp);

            if (empty($str)) {
                unlink(trim($file));
            }
        }

        log_info('End export data from nat_stock_list to csv');

        return;
    }
}
