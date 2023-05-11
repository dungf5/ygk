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

/* Run Batch: php bin/console put-file-ftp-command [param] */

class PutFileFTPCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $commonService;
    private $csvService;
    private $ftpService;

    /**
     * @var MailService
     */
    protected $mailService;

    protected static $defaultName = 'put-file-ftp-command';
    protected static $defaultDescription = 'Process Put File Ftp Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->commonService = new MyCommonService($entityManager);
        $this->csvService = new CSVService($entityManager);
        $this->ftpService = new FTPService($entityManager);
        $this->mailService = $mailService;
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
        log_info('Start Process Put File FTP');

        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');
            log_info('End Process Put File FTP');

            return 0;
        }

        log_info('Param: '.$param);
        $this->processUploadFile(trim($param));

        log_info('End Process Put File FTP');

        return 0;
    }

    private function processUploadFile($param)
    {
        switch ($param) {
            case 'shipping':
                log_info('Start Put File Shipping');
                /* Put files to FTP server*/
                $this->handleUploadFileShipping();
                break;

            case 'nat-stock':
                log_info('Start Put File Nat Stock List');
                /* Put files to FTP server*/
                $this->handleUploadFileNatStockList();
                break;

            default:
                break;
        }
    }

    private function handleUploadFileShipping()
    {
        try {
            $file_from = !empty(getenv('FTP_UPLOAD_SHIPPING_FILE_NAME')) ? getenv('FTP_UPLOAD_SHIPPING_FILE_NAME') : 'SYUKA-NEW.csv';

            if (!str_ends_with(trim($file_from), '.csv')) {
                log_error("{$file_from} is not a csv file");

                $this->pushGoogleChat("Put file FTP: {$file_from} is not a csv file");

                return;
            }

            $path_local = !empty(getenv('LOCAL_FTP_UPLOAD_DIRECTORY')) ? getenv('LOCAL_FTP_UPLOAD_DIRECTORY') : '/html/upload/';
            $path_from = $path_local.'csv/unso/';
            $path_to = $path_local.'csv/shipping/';
            $error_file = 'error.txt';
            $file_to = date('YmdHis').'.csv';

            $result = $this->csvService->transferFile($path_from, $path_to, $file_from, $file_to, $error_file);
            log_info($result['message']);

            // Send mail result
            if ($result['status'] == -1 || $result['status'] == 0) {
                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                    'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                    'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 0,
                    'error_content' => $result['message'],
                ];

                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => $file_to,
                        'directory' => $path_to.date('Y/m'),
                        'message' => $result['message'],
                        'is_error' => 1,
                        'is_send_mail' => 0,
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->pushGoogleChat($e->getMessage());
                }
            } else {
                log_info('[WS-EOS] Send Mail FTP.');

                $information = [
                    'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                    'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                    'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 1,
                    'finish_time' => '('.$file_from.') '.date('Y/m/d H:i:s'),
                ];

                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => $file_to,
                        'directory' => $path_to.date('Y/m'),
                        'message' => 'successfully',
                        'is_error' => 0,
                        'is_send_mail' => 1,
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->pushGoogleChat($e->getMessage());
                }
            }

            $this->mailService->sendMailExportWSEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function handleUploadFileNatStockList()
    {
        try {
            $file_from = 'zaiko_'.date('Ymd').'.csv';

            if (!str_ends_with(trim($file_from), '.csv')) {
                log_error("{$file_from} is not a csv file");

                $this->pushGoogleChat("Put file FTP: {$file_from} is not a csv file");

                return;
            }

            $path_local = !empty(getenv('LOCAL_FTP_UPLOAD_DIRECTORY')) ? getenv('LOCAL_FTP_UPLOAD_DIRECTORY') : '/html/upload/';
            $path_from = $path_local.'csv/nat/';
            $path_to = $path_local.'csv/nat/';
            $error_file = 'error.txt';
            $file_to = date('YmdHis').'.csv';

            $result = $this->csvService->transferFile($path_from, $path_to, $file_from, $file_to, $error_file);
            log_info($result['message']);

            // Send mail result
            if ($result['status'] == -1 || $result['status'] == 0) {
                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                    'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                    'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 0,
                    'error_content' => $result['message'],
                ];

                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => $file_to,
                        'directory' => $path_to.date('Y/m'),
                        'message' => $result['message'],
                        'is_error' => 1,
                        'is_send_mail' => 0,
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->pushGoogleChat($e->getMessage());
                }
            } else {
                log_info('[WS-EOS] Send Mail FTP.');

                $information = [
                    'email' => !empty(getenv('EMAIL_WS_EOS')) ? getenv('EMAIL_WS_EOS') : 'order_support@xbraid.net',
                    'email_cc' => !empty(getenv('EMAILCC_WS_EOS')) ? getenv('EMAILCC_WS_EOS') : '',
                    'email_bcc' => !empty(getenv('EMAILBCC_WS_EOS')) ? getenv('EMAILBCC_WS_EOS') : '',
                    'file_name' => 'Mail/nat_stock_ftp.twig',
                    'status' => 1,
                    'finish_time' => '('.$file_from.') '.date('Y/m/d H:i:s'),
                ];

                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => $file_to,
                        'directory' => $path_to.date('Y/m'),
                        'message' => 'successfully',
                        'is_error' => 0,
                        'is_send_mail' => 1,
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                    $this->pushGoogleChat($e->getMessage());
                }
            }

            $this->mailService->sendMailExportNatStock($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

}
