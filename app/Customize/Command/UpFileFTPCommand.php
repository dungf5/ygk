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

/* Run Batch: php bin/console up-file-ftp-command [param] */

class UpFileFTPCommand extends Command
{
    use PluginCommandTrait;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $commonService;
    private $csvService;
    private $ftpService;

    /**
     * @var MailService
     */
    protected $mailService;

    protected static $defaultName = 'up-file-ftp-command';
    protected static $defaultDescription = 'Process Up File Ftp Command';

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
        log_info('Start Process Up File FTP');

        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');
            log_info('End Process Up File FTP');

            return 0;
        }

        log_info('Param: '.$param);
        $this->processUploadFile(trim($param));

        log_info('End Process Up File FTP');

        return 0;
    }

    private function processUploadFile($param)
    {
        switch ($param) {
            case 'shipping':
                log_info('Start Up File Shipping');
                /* Up files to FTP server*/
                $path = getenv('FTP_UPLOAD_DIRECTORY') ?? '';
                $path_local = getenv('LOCAL_FTP_UPLOAD_DIRECTORY') ?? '/html/upload/';
                $path_local .= 'csv/shipping/';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $path_local = '.'.$path_local;
                }

                $path_local .= date('Y/m');
                $file = getenv('FTP_UPLOAD_SHIPPING_FILE_NAME') ?? 'SYUKA-NEW.csv';

                if (file_exists($path_local.'/'.$file) == false) {
                    log_info("File ({$path_local}/{$file}) is empty");

                    return;
                }

                $this->handleUploadFile($path, $file, $path_local);
                log_info('End Up File Shipping');

                break;

            default:
                break;
        }
    }

    private function handleUploadFile($path, $file, $path_local)
    {
        try {
            if (!empty($path)) {
                $remote_file = $path.'/'.$file;
            } else {
                $remote_file = $file;
            }

            $result = $this->ftpService->upFiles($remote_file, $path_local.'/'.$file);

            // Send mail result
            if ($result['status'] == -1 || $result['status'] == 0) {
                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => getenv('EMAIL_WS_EOS') ?? '',
                    'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
                    'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 0,
                    'error_content' => $result['message'],
                ];

                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => trim($file),
                        'directory' => $path,
                        'message' => $result['message'],
                        'is_error' => 1,
                        'is_send_mail' => 0,
                        'in_date' => new \DateTime(),
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                }
            } else {
                // Rename file to not send again
                $file_rename = date('YmdHis').'.csv';
                rename("{$path_local}/{$file}", $path_local.'/'.$file_rename);

                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => getenv('EMAIL_WS_EOS') ?? '',
                    'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
                    'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 1,
                    'finish_time' => '('.$result['message'].') '.date('Y/m/d H:i:s'),
                ];
                try {
                    // Save file information to DB
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $insertDate = [
                        'file_name' => trim($file_rename),
                        'directory' => $path_local,
                        'message' => 'successfully',
                        'is_error' => 0,
                        'is_send_mail' => 1,
                        'in_date' => new \DateTime(),
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                }
            }

            $this->mailService->sendMailExportWSEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }
}
