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
    protected static $defaultDescription = 'Add a short description for your command';

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

        /* Get files from FTP server*/
        $path = getenv('FTP_DIRECTORY') ?? '';
        $path_local = getenv('LOCAL_FTP_DIRECTORY') ?? '/html/dowload/csv/order/';

        if (!empty($path)) {
            $result = $this->ftpService->getFiles($path, $path_local);
            log_info($result['message']);

            // Send mail error
            if ($result['status'] == -1) {
                log_info('[WS-EOS] 注文メールの送信を行います.');
                $information = [
                    'email' => getenv('EMAIL_WS_EOS') ?? '',
                    'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
                    'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
                    'file_name' => 'Mail/order_ws_eos.twig',
                    'status' => 0,
                    'error_content' => $result['message'],
                ];

                try {
                    $this->mailService->sendMailWSEOS($information);
                } catch (\Exception $e) {
                    log_error($e->getMessage());
                }
            }

        log_info('End Process Get File FTP');
        $io->success('End Process Get File FTP');

        return 0;
    }
}
