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
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstCustomer;
use Customize\Entity\MstProduct;
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

/* Run Batch: php bin/console validate-csv-data-command */
class ValidateCsvDataCommand extends Command
{
    use PluginCommandTrait;

    /** @var EntityManagerInterface */
    private $entityManager;
    /**
     * @var MailService
     */
    private $mailService;
    private $errors = [];

    protected static $defaultName = 'validate-csv-data-command';
    protected static $defaultDescription = 'Process Validate Csv Data Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
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
        log_info('---------------------------------------');
        log_info('Start Process Validate Csv Data Command');
        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');

            return 0;
        }

        //if ($input->getOption('option1')) {}
        $this->handleValidateCsvData(trim($param));

        log_info('End Process Validate Csv Data Command');
        $io->success('End Process Validate Csv Data Command');

        return 0;
    }

    private function handleValidateCsvData($param)
    {
        switch ($param) {
            case 'ws-eos':
                    $this->handleValidateWSEOS();
                break;

            default:
                break;
        }
    }

    private function handleValidateWSEOS()
    {
        try {
            log_info('Start Handle Validate WS EOS DATA');

            $cache_file = getenv('LOCAL_FTP_DIRECTORY') ?? '/html/dowload/csv/order/';
            $cache_file .= 'ws_eos_cache_file.txt';
            if (getenv('APP_IS_LOCAL') == 1) {
                $cache_file = '.'.$cache_file;
            }

            // open file to write to
            $fp = @fopen($cache_file, 'r');
            if ($fp) {
                while (($buffer = fgets($fp, 4096)) !== false) {
                    $buffer = explode('-', $buffer);

                    if (count($buffer) == 2) {
                        $order_no = $buffer[0];
                        $order_line_no = $buffer[1];
                        $this->validateWSEOS($order_no, $order_line_no);
                    }
                }
                if (!feof($fp)) {
                    log_error('unexpected fgets() fail');
                }
                fclose($fp);
                //unlink($cache_file);
            }

            if (count($this->errors)) {
                foreach ($this->errors as $error) {
                    $this->entityManager->getRepository(DtOrderWSEOS::class)->updateError($error);
                }

                $this->sendMailErrorWSEOS($this->errors);
            }

            log_info('End Handle Validate WS EOS DATA');
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }

    private function validateWSEOS($order_no, $order_line_no)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            $object = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy([
                'order_no' => $order_no,
                'order_line_no' => $order_line_no,
            ]);

            $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                'jan_code' => $object['jan_code'],
            ]);

            $customer = $this->entityManager->getRepository(MstCustomer::class)->findOneBy([
                'customer_code' => $object['shipping_shop_code'],
            ]);

            if (empty($object)) {
                log_info("No order ({$order_no}-{$order_line_no})");

                return;
            }

            // Array contain error if any
            $error = [
                'order_no' => $order_no,
                'order_line_no' => $order_line_no,
            ];

            log_info("Validate order ({$order_no}-{$order_line_no})");

            // validate order_date
            if (empty($object['order_date']) || (date('Y-m-d', strtotime($object['order_date'])) < date('Y-m-d'))) {
                $error['error_content1'] = '発注日付が過去日付になっています';
            }

            // Validate shipping_shop_code
            if (empty($object['shipping_shop_code']) || empty($customer)) {
                $error['error_content3'] = '出荷先支店コードが登録されていません';
            }

            // validate delivery_date
            if (empty($object['delivery_date']) || (date('Y-m-d', strtotime($object['delivery_date'])) < date('Y-m-d'))) {
                $error['error_content4'] = '納入希望日が過去日付になっています';
            }

            // Validate jan_code
            if (empty($object['jan_code']) || empty($product)) {
                $error['error_content5'] = 'JANコードが存在しません';
            }

            // Validate order_num
            if (!empty($product)) {
                if ((int) $object['order_num'] % (int) $product['quantity']) {
                    $error['error_content6'] = '発注数量の販売単位に誤りがあります';
                }
            }

            if (!empty($product) && !empty($object['customer_code']) && !empty($object['shipping_code'])) {

            }

            if (count($error)) {
                $this->errors[] = $error;
                $error = null;
            }

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }

    private function sendMailErrorWSEOS($errors = [])
    {
        if (empty($errors)) {
            return;
        }

        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/error_ws_eos.twig',
            'error_data' => $errors,
        ];

        try {
            log_info('[WS-EOS] Send Mail Error.');

            $this->mailService->sendMailErrorWSEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return;
        }
    }
}
