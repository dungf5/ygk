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
use Customize\Entity\DtImportCSV;
use Customize\Entity\DtOrderNatSort;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
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

/* Run Batch: php bin/console nat-sort-command [param] */
class NatSortCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;
    use CSVHeader;

    /**
     * @var EntityManagerInterface
     */
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

    private $params = [
        'get',
        'import',
        'export',
    ];

    protected static $defaultName = 'nat-sort-command';
    protected static $defaultDescription = 'Process Nat Sort Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
        $this->commonService = new MyCommonService($entityManager);
        $this->csvService = new CSVService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('check', null, InputOption::VALUE_OPTIONAL, 'Option check')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        log_info('---------------------------------------');
        log_info('Start Process Nat Sort');
        $param = $input->getArgument('arg1') ?? null;
        $option = $input->getOption('check');

        if (!$param) {
            log_error('No param. Process stopped.');

            $message = 'Process Nat Sort. No param. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        if (!in_array($param, $this->params)) {
            log_error('Param is invalid. Process stopped.');

            $message = 'Process Nat Sort. Param is invalid. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        $this->handleProcess($param);

        log_info('End Process Nat Sort');

        return 0;
    }

    public function handleProcess($param)
    {
        /* The local path to load csv file */
        $path = !empty(getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY')) ? getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY') : '/html/download/';

        switch (trim($param)) {
            case 'get':
                $this->processGetFile($path);
                break;

            case 'import':
                $path .= 'csv/nat/sort/';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $path = '.'.$path;
                }

                $this->processImport($path.date('Y/m'));
                break;

            case 'export':
                $path = getenv('LOCAL_FTP_UPLOAD_DIRECTORY') ?? '/html/upload/';
                $path .= 'csv/nat/sort/';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $path = '.'.$path;
                }
                $this->processExport($path);
                break;

            default:
                break;
        }
    }

    private function processGetFile($path)
    {
        log_info('Start Get File Nat Sort');
        /* Get files from FTP server*/
        $file_from = 'requestD_sort_'.date('Ymd').'.csv';
        $path_from = $path.'csv/nat/sort/';
        $path_to = $path.'csv/nat/sort/';
        $error_file = 'error.txt';
        $file_to = 'nat_sort_'.date('YmdHis').'.csv';

        $result = $this->csvService->transferFile($path_from, $path_to, $file_from, $file_to, $error_file);
        log_info($result['message']);

        // Send mail error
        if ($result['status'] == -1) {
            log_info('[NAT-SORT] Send Mail FTP.');
            $information = [
                'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                'file_name' => 'Mail/nat_ftp.twig',
                'status' => 0,
                'error_content' => $result['message'],
            ];

            try {
                $this->mailService->sendMailImportNatEOS($information);
            } catch (\Exception $e) {
                log_error($e->getMessage());
                $this->pushGoogleChat($e->getMessage());
            }
        }

        // Success
        if ($result['status'] == 1) {
            // Save file information to DB
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $insertDate = [
                'file_name' => $file_to,
                'directory' => $path_to.date('Y/m'),
                'message' => null,
                'is_sync' => 0,
                'is_error' => 0,
                'is_send_mail' => 1,
            ];

            $this->entityManager->getRepository(DtImportCSV::class)->insertData($insertDate);

            log_info('[NAT-SORT] Send Mail FTP.');
            $information = [
                'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                'file_name' => 'Mail/nat_ftp.twig',
                'status' => 1,
                'finish_time' => '('.$file_from.') '.date('Y/m/d H:i:s'),
            ];

            try {
                $this->mailService->sendMailImportNatEOS($information);
            } catch (\Exception $e) {
                log_error($e->getMessage());
                $this->pushGoogleChat($e->getMessage());
            }
        }

        log_info('End Get File Nat Sort');
    }

    private function processImport($path)
    {
        log_info('Start Process Import Order NAT SORT CSV for '.date('Y-m-d'));
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        if (empty($path)) {
            log_error('Path is empty ');

            $message = 'Process Import Order NAT SORT CSV for '.date('Y-m-d');
            $message .= "\n".'Path is empty ';
            $this->pushGoogleChat($message);

            return;
        }

        try {
            $file_list = scandir($path);
        } catch (\Exception $e) {
            log_error("Path {$path} is not existed.");

            $message = 'Process Import Order NAT SORT CSV for '.date('Y-m-d');
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
                $colNumber = !empty(getenv('NUMBER_COLUMN_NAT_EOS')) ? getenv('NUMBER_COLUMN_NAT_EOS') : 7;
                $csvData = $this->LoadFileReadData($path, $file, $colNumber);
                $result = $this->SaveDataNatSort($csvData);

                // Update information dt_import_csv
                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                $data = [
                    'id' => $fileExist['id'],
                    'file_name' => $fileExist['file_name'],
                    'message' => $result['message'] ?? '',
                    'is_sync' => 1,
                    'is_error' => $result['status'] ? 0 : 1,
                ];
                $this->entityManager->getRepository(DtImportCSV::class)->updateData($data);

                $this->pushGoogleChat('Import dt_order_nat_sort: '.$result['message']);
            }
        }

        log_info('End Process Import Order NAT SORT CSV for '.date('Y-m-d'));
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

    private function SaveDataNatSort($data)
    {
        if (empty($data) || !is_array($data)) {
            return [
                'status' => 0,
                'message' => 'data empty',
            ];
        }

        // Truncate dt_order_nat_sort
        $result = $this->handleDelete();
        if (!$result) {
            return [
                'status' => 0,
                'message' => 'truncate dt_order_nat_sort error',
            ];
        }

        log_info('Start Save Data Order NAT SORT');
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // Foreach row
        foreach ($data as $x => $row) {
            // Exclude title
            if ($x == 0) {
                continue;
            }

            // Foreach column
            $objData = [];

            foreach ($this->getNatEOSCsvOrderHeader() as $y => $col) {
                $objData["{$col}"] = trim($data[$x][$y]);
            }

            // insert dt_order_nat_sort
            $this->entityManager->getRepository(DtOrderNatSort::class)->insertData($objData);
        }

        log_info('End Save Data Order NAT SORT');

        return [
            'status' => 1,
            'message' => 'successfully',
        ];
    }

    private function handleDelete()
    {
        log_info('Start Delete all dt_order_nat_sort');
        $result = $this->truncateTable(DtOrderNatSort::class, $this->entityManager);
        log_info('End Delete all dt_order_nat_sort');

        if (!$result) {
            $this->pushGoogleChat("Process Delete Nat Sort Data.\nCan't truncate table dt_order_nat_sort. Please check log!!!");
        }

        return $result;
    }

    private function processExport($path)
    {
        if (empty($path)) {
            return;
        }

        $data = $this->commonService->getNatSortExportData();

        if (!count($data)) {
            log_info('No data');

            return;
        }

        $file_name = 'requestD_'.date('Ymd').'.csv';
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

        $fp = fopen(trim($file), 'w');

        if ($fp) {
            $headerFields = [];
            foreach ($this->getNatSortExportHeader() as $header) {
                $headerFields[] = mb_convert_encoding($header, 'Shift-JIS', 'UTF-8');
            }
            fputcsv($fp, $headerFields);

            foreach ($data as $item) {
                try {
                    $fields = [
                        mb_convert_encoding($item['reqcd'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['jan'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['mkrcd'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['natcd'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['qty'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding($item['cost'], 'Shift-JIS', 'UTF-8'),
                        mb_convert_encoding(!empty($item['delivery_day']) ? date('Ymd', strtotime($item['delivery_day'])) : '', 'Shift-JIS', 'UTF-8'),
                    ];

                    fputcsv($fp, $fields);

                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->pushGoogleChat($e->getMessage());
                }
            }

            fclose($fp);
        }

        // Check file after put data
        if (($fp = fopen(trim($file), 'r')) !== false) {
            $str = fread($fp, 100);
            $getFileCSV = file_get_contents($file, (bool) FILE_USE_INCLUDE_PATH);
            $getFileCSV = str_replace('"', '', $getFileCSV);
            file_put_contents($file, $getFileCSV);
            fclose($fp);

            if (empty($str)) {
                unlink(trim($file));
            }
        }

        return;
    }
}
