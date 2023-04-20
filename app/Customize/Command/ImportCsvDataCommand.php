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

use Customize\Config\WSEOS;
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\DtImportCSV;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\DtOrderWSEOSCopy;
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

/* Run Batch: php bin/console import-csv-data-command [param] */
class ImportCsvDataCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;
    use WSEOS;

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
    private $csvService;
    private $ftpService;

    protected static $defaultName = 'import-csv-data-command';
    protected static $defaultDescription = 'Process Import Csv Data Command';

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
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        log_info('---------------------------------------');
        log_info('Start Process Import Csv Data');
        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');

            $message = 'Process Import Csv Data. No param. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        $this->handleProcess($param);

        log_info('End Process Import Csv Data');
        //$io->success('End Process Import Csv Data');

        return 0;
    }

    public function handleProcess($param)
    {
        /* The local path to load csv file */
        $path = !empty(getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY')) ? getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY') : '/html/download/';

        switch (trim($param)) {
            case 'ws-eos':
                $path .= 'csv/order/';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $path = '.'.$path;
                }

                $this->handleImportCsvOrderWSEOS($path.date('Y/m'));

                break;

            default:
                break;
        }
    }

    private function handleImportCsvOrderWSEOS($path)
    {
        log_info('Start Process Import Order WS-EOS CSV for month '.date('m'));

        if (empty($path)) {
            log_error('Path is empty ');

            $message = 'Process Import Order WS-EOS CSV for month '.date('m');
            $message .= "\n".'Path is empty ';
            $this->pushGoogleChat($message);

            return;
        }

        try {
            $file_list = scandir($path);
        } catch (\Exception $e) {
            log_error("Path {$path} is not existed.");

            $message = 'Process Import Order WS-EOS CSV for month '.date('m');
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);
            $file_list = [];
        }

        if (!empty($file_list) && is_array($file_list) && count($file_list)) {
            foreach ($file_list as $file) {
                if (!str_ends_with($file, '.csv')) {
                    continue;
                }

                $fileExist = $this->entityManager->getRepository(DtImportCSV::class)->findOneBy(['file_name' => $file]);
                if (empty($fileExist)) {
                    continue;
                }

                if ($fileExist['is_sync'] == 1) {
                    continue;
                }

                // Load file read data
                $colNumber = !empty(getenv('NUMBER_COLUMN_WS_EOS')) ? getenv('NUMBER_COLUMN_WS_EOS') : 42;
                $csvData = $this->LoadFileReadData($path, $file, $colNumber);
                $result = $this->SaveDataFromFileToDB($csvData);

                // Update information dt_import_csv
                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                $data = [
                    'id' => $fileExist['id'],
                    'file_name' => $fileExist['file_name'],
                    'message' => $result['message'] ?? '',
                    'is_sync' => 1,
                    'is_error' => $result['status'] ? 0 : 1,
                    'up_date' => new \DateTime(date('Y-m-d H:i:s')),
                ];
                $this->entityManager->getRepository(DtImportCSV::class)->updateData($data);

                // Send mail
                $this->SendMailWSEOSImport($result['status'], $data);
            }
        }

        log_info('End Process Import Order WS-EOS CSV for month '.date('m'));
    }

    private function LoadFileReadData($path, $file, $colNumber)
    {
        $path = $path.'/'.$file;

        log_info("Start load file {$path} and save data");

        $result = $this->csvService->readFile($path, $colNumber);

        if ($result['status'] == 1) {
            return $result['message'];
        } else {
            //Log error
            log_error($result['message']);
            $this->pushGoogleChat("Read File {$path} ".$result['message']);

            return [];
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
        $cache_file = getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY') ?? '/html/download/';
        $cache_file .= 'csv/order/';

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
            foreach ($this->getWSEOSCsvOrderHeader() as $y => $col) {
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

            // Insert dt_order_ws_eos
            if (empty($objectExist)) {
                log_info('Insert dt_order_ws_eos '.$objData['order_no'].'-'.$objData['order_line_no']);

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

    private function SendMailWSEOSImport($status = 1, $data)
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

            $this->mailService->sendMailImportWSEOS($information);

            // Update information dt_import_csv
            $data = [
                'id' => $data['id'],
                'is_send_mail' => 1,
            ];
            $this->entityManager->getRepository(DtImportCSV::class)->updateData($data);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }
}
