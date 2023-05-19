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
use Customize\Entity\DtOrderNatEOS;
use Customize\Entity\MstShipping;
use Customize\Entity\MstShippingNatEOS;
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

/* Run Batch: php bin/console import-shipping-data-command [param] */
class ImportShippingDataCommand extends Command
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
    private $success = [];
    private $rate = 0;
    private $customer_code = '';
    private $customer = null;
    private $customer_shipping = null;
    private $customer_relation = null;

    protected static $defaultName = 'import-shipping-data-command';
    protected static $defaultDescription = 'Process Import Shipping Data Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
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
        log_info('Start Process Import Shipping Data');
        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');

            $message = 'Process Import Shipping Data. No param. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        $this->handleProcess($param);

        log_info('End Process Import Shipping Data');

        return 0;
    }

    public function handleProcess($param)
    {
        log_info("param {$param}");

        switch (trim($param)) {
            case 'nat-eos':
                $this->handleImportShippingNatEOS();
                break;

            default:
                break;
        }
    }

    private function handleImportShippingNatEOS()
    {
        try {
            log_info('Start Handle Import Data To mst_shipping_nat_eos');
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            // Get data to import
            $data = $this->entityManager->getRepository(DtOrderNatEOS::class)->findBy([
                'order_registed_flg' => 1,
                'shipping_sent_flg' => 0,
            ], [
                'reqcd' => 'ASC',
                'order_lineno' => 'ASC',
            ]);

            if (empty($data)) {
                log_info('No data');

                return;
            }

            foreach ($data as $item) {
                $this->importShippingNatEOS($item->toArray());
            }

            log_info('End Handle Import Data To mst_shipping_nat_eos');

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            $message = 'Handle Import Data To mst_shipping_nat_eos';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return;
        }
    }

    private function importShippingNatEOS($data)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            $mstShipping = $this->entityManager->getRepository(MstShipping::class)->findOneBy([
                'cus_order_no' => $data['reqcd'],
                'cus_order_lineno' => $data['order_lineno'],
                'shipping_status' => 2,
            ]);

            if (empty($mstShipping)) {
                log_error('No data mst_shipping '.$data['reqcd'].'-'.$data['order_lineno'].' status = 2');

                $message = 'Import Data To mst_shipping_nat_eos';
                $message .= "\n".'No data mst_shipping '.$data['reqcd'].'-'.$data['order_lineno'].' status = 2';
                $this->pushGoogleChat($message);

                return 0;
            }

            $mstShippingNatEOS = $this->entityManager->getRepository(MstShippingNatEOS::class)->findOneBy([
                'reqcd' => $data['reqcd'],
                'order_lineno' => $data['order_lineno'],
            ]);

            if (!empty($mstShippingNatEOS)) {
                log_error('mst_shipping_nat_eos is existed '.$data['reqcd'].'-'.$data['order_lineno']);
                $message = 'Import Data To mst_shipping_nat_eos';
                $message .= "\n".'Record is existed '.$data['reqcd'].'-'.$data['order_lineno'];
                $this->pushGoogleChat($message);

                return 1;
            }

            // Insert mst_shipping_nat_eos
            if (empty($mstShippingNatEOS)) {
                log_info('Import data mst_shipping_nat_eos '.$data['reqcd'].'-'.$data['order_lineno']);

                $data['delivery_no'] = '9999'.str_pad(substr((string) $mstShipping['shipping_no'], -8), 8, '0', STR_PAD_LEFT);
                $data['shipping_date'] = !empty($mstShipping['shipping_date']) ? date('Ymd', strtotime($mstShipping['shipping_date'])) : '';
                $data['shipping_no'] = $mstShipping['shipping_no'] ?? null;

                return $this->entityManager->getRepository(MstShippingNatEOS::class)->insertData($data);
            }

            return 0;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            $message = 'Import Data To mst_shipping_nat_eos';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return 0;
        }
    }
}
