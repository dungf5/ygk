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
                /* Up files to FTP server*/
                $path = getenv('FTP_UPLOAD_DIRECTORY') ?? '';
                $path_local = getenv('LOCAL_FTP_UPLOAD_DIRECTORY') ?? '/html/upload/';
                $path_local .= 'csv/shipping/';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $path_local = '.'.$path_local;
                }

                if (!empty($path)) {
                    $path_local .= date('Y/m/');

                    if (file_exists($path_local) == false) {
                        log_info("Local path ({$path_local}) is empty");
                        return;
                    }

                    $file_list = array_diff(scandir($path_local), ['.', '..']);

                    foreach ($file_list as $file) {
                        // Check file in dt_export_csv
                        if (!$this->checkFileUpload($path_local, $file)) {
                            continue;
                        }

                        $this->handleUploadFile($path, $file, $path_local);
                    }
                }
                break;

            default:
                break;
        }
    }

    private function checkFileUpload($path, $file)
    {
        try {
            $dtExport = $this->entityManager->getRepository(DtExportCSV::class)->findOneBy(['file_name' => strtolower(trim($file))]);

            if (empty($dtExport)) {
                // Save file information to DB
                $dtExporCSv = $this->commonService->getDtExportCsv();
                $increment = !empty($dtExporCSv) ? (int) $dtExporCSv['increment'] : 0;
                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                $insertDate = [
                    'file_name' => strtolower(trim($file)),
                    'increment' => $increment + 1,
                    'directory' => $path,
                    'message' => null,
                    'is_error' => 0,
                    'is_send_mail' => 0,
                    'in_date' => new DateTime(),
                    'up_date' => null,
                ];
                $this->entityManager->getRepository(DtExportCSV::class)->insertData($insertDate);

                return 1;
            } else {
                return !((int) $dtExport->getIsSendMail());
            }
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return 0;
        }
    }

    private function handleUploadFile($path, $file, $path_local)
    {
        try {
            $remote_file = $path.'/'.$file;
            $path_local .= $file;

            $result = $this->ftpService->upFiles($path, $remote_file, $path_local);
            $dtExport = $this->entityManager->getRepository(DtExportCSV::class)->findOneBy(['file_name' => strtolower(trim($file))]);

            // Send mail result
            if ($result['status'] == -1 || $result['status'] == 0) {
                if (!empty($dtExport)) {
                    // Update information dt_import_csv
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $data = [
                        'file_name' => strtolower(trim($file)),
                        'message' => $result['message'],
                        'is_error' => 1,
                        'is_send_mail' => 0,
                        'up_date' => new \DateTime(),
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->updateData($data);
                }

                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => getenv('EMAIL_WS_EOS') ?? '',
                    'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
                    'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 0,
                    'error_content' => $result['message'],
                ];
            } else {
                if (!empty($dtExport)) {
                    // Update information dt_import_csv
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $data = [
                        'file_name' => strtolower(trim($file)),
                        'message' => 'successfully',
                        'is_error' => 0,
                        'is_send_mail' => 1,
                        'up_date' => new \DateTime(),
                    ];
                    $this->entityManager->getRepository(DtExportCSV::class)->updateData($data);
                }

                log_info('[WS-EOS] Send Mail FTP.');
                $information = [
                    'email' => getenv('EMAIL_WS_EOS') ?? '',
                    'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
                    'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
                    'file_name' => 'Mail/ws_eos_ftp.twig',
                    'status' => 1,
                    'finish_time' => '('.$result['message'].') '.date('Y/m/d H:i:s'),
                ];
            }

            $this->mailService->sendMailExportWSEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }
}
