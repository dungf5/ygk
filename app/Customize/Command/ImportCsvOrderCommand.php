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
use Customize\Entity\DtImportCSV;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\DtOrderWSEOSCopy;
use Customize\Entity\MstProduct;
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

/* Run Batch: php bin/console import-csv-order-command */
class ImportCsvOrderCommand extends Command
{
    use PluginCommandTrait;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $csvService;
    private $ftpService;
    /**
     * @var MailService
     */
    private $mailService;

    protected static $defaultName = 'import-csv-order-command';
    protected static $defaultDescription = 'Process Import Csv Order Command';

    private $headers = [
        'order_type',
        'web_order_type',
        'order_date',
        'order_no',
        'system_code',
        'order_company_code',
        'order_shop_code',
        'order_staff_code',
        'sales_company_code',
        'sales_staff_code',
        'order_company_name',
        'delivery_flag',
        'shipping_company_code',
        'shipping_shop_code',
        'shipping_name',
        'shipping_address1',
        'shipping_address2',
        'shipping_post_code',
        'shipping_tel',
        'shipping_fax',
        'delivery_date',
        'export_type',
        'aprove_type',
        'order_cancel',
        'delete_flag',
        'order_voucher_type',
        'order_line_no',
        'order_flag',
        'order_system_code',
        'order_staff_name',
        'order_shop_name',
        'product_maker_code',
        'product_name',
        'order_num',
        'order_price',
        'order_amount',
        'tax_type',
        'remarks_line_no',
        'jan_code',
        'cash_type_code',
        'order_create_day',
        'order_update_day',
    ];

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->csvService = new CSVService($entityManager);
        $this->ftpService = new FTPService($entityManager);
        $this->mailService = $mailService;
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
        $path = getenv('LOCAL_FTP_DIRECTORY') ?? '/html/dowload/csv/order/';
        if (getenv('APP_IS_LOCAL') == 1) {
            $path = '.'.$path;
        }

        log_info('----------------------------------');

        // If the current day is the first of month. Run first for day - 1
        $currentDay = date('j');
        if ($currentDay == 1) {
            log_info('Start Process Import Order CSV for month '.date('m', strtotime('-1 day')));
            $this->handleImportCsvOrder($path.date('Y/m', strtotime('-1 day')));
            log_info('End Process Import Order CSV for month '.date('m', strtotime('-1 day')));
        }

        log_info('Start Process Import Order CSV for month '.date('m'));
        $this->handleImportCsvOrder($path.date('Y/m'));
        log_info('End Process Import Order CSV for month '.date('m'));

        //$io->success('End Process Import Order CSV');

        return 0;
    }

    private function handleImportCsvOrder($path)
    {
        if (empty($path)) {
            return;
        }

        try {
            $file_list = scandir($path);
        } catch (\Exception $e) {
            log_error("Path {$path} is not existed.");
            $file_list = [];
        }

        if (!empty($file_list) && is_array($file_list) && count($file_list)) {
            foreach ($file_list as $file) {
                if (!str_ends_with($file, '.csv')) {
                    continue;
                }

                $fileExist = $this->entityManager->getRepository(DtImportCSV::class)->findOneBy(['file_name' => $file, 'is_sync' => 1]);
                if (!empty($fileExist)) {
                    continue;
                }

                // Get data from file and save DB
                $result = $this->LoadFileReadData($path, $file);

                // Update information dt_import_csv
                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                $data = [
                    'file_name' => $file,
                    'message' => $result['message'] ?? '',
                    'is_sync' => 1,
                    'is_error' => $result['status'] ? 0 : 1,
                    'up_date' => new \DateTime(),
                ];
                $this->entityManager->getRepository(DtImportCSV::class)->updateData($data);

                // Send mail
                $this->SendMailWSEOS($result['status'], $data);
            }
        }
    }

    private function LoadFileReadData($path, $file)
    {
        $path = $path.'/'.$file;
        $colNumber = getenv('NUMBER_COLUMN_WS_EOS') ?? 42;
        log_info("Start load file {$path} and save data");

        $result = $this->csvService->readFile($path, $colNumber);

        if ($result['status'] == 1) {
            $csvData = $result['message'];

            if (!empty($csvData)) {
                // Read and save data
                $result = $this->SaveDataFromFileToDB($csvData);

                return $result;
            } else {
                // Log empty
                log_info("File {$path} is empty");

                return [
                    'status' => 0,
                    'message' => 'data empty',
                ];
            }
        } else {
            //Log error
            log_info($result['message']);

            return [
                'status' => 0,
                'message' => $result['message'],
            ];
        }
    }

    private function SaveDataFromFileToDB($data)
    {
        if (empty($data) || !is_array($data)) {
            return [
                'status' => 0,
                'message' => 'data empty',
            ];
        }

        log_info('Start save/update data Order WS EOS');
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // Insert cache file to validation data
        $cache_file = getenv('LOCAL_FTP_DIRECTORY') ?? '/html/dowload/csv/order/';
        if (getenv('APP_IS_LOCAL') == 1) {
            $cache_file = '.'.$cache_file;
        }
        $cache_file .= 'ws_eos_cache_file'.date('Ymd').'.txt';
        // open file to write to
        if (!$handle = fopen($cache_file, 'a')) {
            log_error("Cannot open file ({$cache_file})");
        }

        // Foreach row
        foreach ($data as $x => $row) {
            // Foreach column
            $objData = [];
            foreach ($this->headers as $y => $col) {
                $objData["{$col}"] = trim($data[$x][$y]);
            }

            $objectExist = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy([
                'order_no' => $objData['order_no'] ?? '',
                'order_line_no' => $objData['order_line_no'] ?? '',
            ]);

            // Write to cache file
            if ($handle) {
                $cache_data = $objData['order_no'].'-'.$objData['order_line_no']."\n";
                if (fwrite($handle, $cache_data) === false) {
                    log_error("Cannot write ({$cache_data}) to file ({$cache_file})");
                }
            }

            // Set product data
            $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                'jan_code' => $objData['jan_code'] ?? '',
            ]);
            $objData['product_code'] = !empty($product) ? $product['product_code'] : '';

            // Insert dt_order_ws_eos
            if (empty($objectExist)) {
                log_info('Insert dt_order_ws_eos '.$objData['order_no'].'-'.$objData['order_line_no']);

                // Set more data
                $objData['customer_code'] = '7001';
                $objData['shipping_code'] = '7001001000';
                $objData['otodoke_code'] = '7001001'.str_pad($objData['shipping_shop_code'], 3, '0', STR_PAD_LEFT);

                $this->entityManager->getRepository(DtOrderWSEOS::class)->insertData($objData);
            }

            // Update
            else {
                $orderRegistedFlg = $objectExist['order_registed_flg'];

                switch ((int) $orderRegistedFlg) {
                    case 1:
                        $objectCopyExist = $this->entityManager->getRepository(DtOrderWSEOSCopy::class)->findOneBy([
                            'order_no' => $objData['order_no'] ?? '',
                            'order_line_no' => $objData['order_line_no'] ?? '',
                        ]);

                        // Insert dt_order_ws_eos_copy
                        if (empty($objectCopyExist)) {
                            log_info('Insert dt_order_ws_eos_copy '.$objData['order_no'].'-'.$objData['order_line_no']);
                            $this->entityManager->getRepository(DtOrderWSEOSCopy::class)->insertData($objData);
                        }

                        // Update dt_order_ws_eos_copy
                        else {
                            log_info('Update dt_order_ws_eos_copy '.$objData['order_no'].'-'.$objData['order_line_no']);
                            $this->entityManager->getRepository(DtOrderWSEOSCopy::class)->updateData($objData);
                        }
                        break;

                    default:
                        // Update dt_order_ws_eos
                        log_info('Update dt_order_ws_eos '.$objData['order_no'].'-'.$objData['order_line_no']);
                        $this->entityManager->getRepository(DtOrderWSEOS::class)->updateData($objData);
                        break;
                }
            }
        }

        //close
        if ($handle) {
            fclose($handle);
        }

        log_info('End save/update data Order WS EOS');

        return [
            'status' => 1,
            'message' => 'successfully',
        ];
    }

    private function SendMailWSEOS($status = 1, $data)
    {
        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/ws_eos_ftp.twig',
        ];

        // Send mail successfully
        if ($status == 1) {
            $information['status'] = 1;
            $information['finish_time'] = '('.$data['file_name'].') '.($data['up_date'])->format('Y/m/d H:i:s');
        }

        // Send mail error
        if ($status == 0) {
            $information['status'] = 0;
            $information['error_content'] = '('.$data['file_name'].') '.$data['message'];
        }

        try {
            log_info('[WS-EOS] 注文メールの送信を行います.');

            $this->mailService->sendMailWSEOS($information);

            // Update information dt_import_csv
            $data = [
                'file_name' => $data['file_name'],
                'is_send_mail' => 1,
            ];
            $this->entityManager->getRepository(DtImportCSV::class)->updateData($data);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }
}