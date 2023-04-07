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

use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
use Customize\Service\FTPService;
use Customize\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console get-file-ftp-command */

class GetFileFTPCommand extends Command
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

    protected static $defaultName = 'get-file-ftp-command';
    protected static $defaultDescription = 'Process Get File Ftp Command';

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
        log_info('Start Process Get File FTP');

        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');
            log_info('End Process Get File FTP');

            return 0;
        }

        log_info('Param: '.$param);
        $this->processGetFile(trim($param));

        log_info('End Process Get File FTP');

        return 0;
    }

    private function processGetFile($param)
    {
        switch ($param) {
            case 'ws-eos':
                log_info('Start Get File Order WS-EOS');
                /* Get files from FTP server*/
                $path = getenv('FTP_DOWNLOAD_DIRECTORY') ?? '';
                $path_local = getenv('LOCAL_FTP_DOWNLOAD_DIRECTORY') ?? '/html/download/';
                $path_local .= 'csv/order/';
                $file = getenv('FTP_DOWNLOAD_ORDER_FILE_NAME') ?? 'HACHU-NEW.csv';

                if (!empty($path)) {
                    $path = $path.'/'.$file;
                } else {
                    $path = $file;
                }

                if (!str_ends_with(trim($path), '.csv')) {
                    log_error("{$path} is not a csv file");
                    return;
                }

                $result = $this->ftpService->getFiles(trim($path), $path_local);
                log_info($result['message']);

                // Send mail error
                if ($result['status'] == -1) {
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
                        $this->mailService->sendMailImportWSEOS($information);
                    } catch (\Exception $e) {
                        log_error($e->getMessage());
                    }
                }

                log_info('End Get File Order WS-EOS');
                break;

            default:
                break;
        }
    }
}
