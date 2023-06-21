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
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstProduct;
use Customize\Entity\MstShipping;
use Customize\Entity\MstShippingNatEOS;
use Customize\Entity\MstShippingWSEOS;
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

        $this->handleProcess(trim($param));

        log_info('End Process Import Shipping Data');

        return 0;
    }

    public function handleProcess($param)
    {
        log_info("param {$param}");

        switch (trim($param)) {
            case 'ws-eos':
                $this->handleImportShippingWSEOS();

                break;

            case 'nat-eos':
                $this->handleImportShippingNatEOS();
                break;

            default:
                break;
        }
    }

    private function handleImportShippingWSEOS()
    {
        try {
            log_info('Start Handle Import Data To mst_shipping_ws_eos');
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            // Get data to import
            $data = $this->entityManager->getRepository(DtOrderWSEOS::class)->findBy([
                'order_registed_flg' => 1,
                'shipping_sent_flg' => 0,
            ], [
                'shipping_shop_code' => 'ASC',
                'order_shop_code' => 'ASC',
                'order_no' => 'ASC',
                'order_line_no' => 'ASC',
            ]);

            foreach ($data as $item) {
                $shipping_num = $this->importShippingWSEOS($item->toArray());

                if ((int) $shipping_num > 0) {
                    $item->setShippingNum((int) $item->getShippingNum() + (int) $shipping_num);
                    $this->entityManager->getRepository(DtOrderWSEOS::class)->save($item);
                }

                if ((int) $item->getShippingNum() == (int) $item->getOrderNum()) {
                    $item->setShippingSentFlg(1);
                    $this->entityManager->getRepository(DtOrderWSEOS::class)->save($item);
                }
            }

            log_info('End Handle Import Data To mst_shipping_ws_eos');

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            $message = 'Handle Import Data To mst_shipping_ws_eos';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return;
        }
    }

    private function importShippingWSEOS($data)
    {
        $shipping_num = 0;

        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            $mstShipping = $this->entityManager->getRepository(MstShipping::class)->findOneBy([
                'cus_order_no' => $data['order_no'],
                'cus_order_lineno' => $data['order_line_no'],
                'shipping_status' => 2,
            ], [
                'shipping_date' => 'DESC',
            ]);

            if (empty($mstShipping)) {
                log_error('No data mst_shipping '.$data['order_no'].'-'.$data['order_line_no'].' status = 2');

                $message = 'Import Data To mst_shipping_ws_eos';
                $message .= "\n".'No data mst_shipping '.$data['order_no'].'-'.$data['order_line_no'].' status = 2';
                $this->pushGoogleChat($message);

                return $shipping_num;
            }

            $mstShippingWSEOS = $this->entityManager->getRepository(MstShippingWSEOS::class)->findOneBy([
                'order_no' => $data['order_no'],
                'order_line_no' => $data['order_line_no'],
                'shipping_no' => $mstShipping['shipping_no'],
            ]);

            if (!empty($mstShippingWSEOS)) {
                return $shipping_num;
            }

            // Insert mst_shipping_ws_eos
            if (empty($mstShippingWSEOS)) {
                log_info('Import data mst_shipping_ws_eos '.$data['order_no'].'-'.$data['order_line_no']);
                $data['shipping_date'] = $mstShipping['shipping_date'] ?? null;
                $data['product_maker_code'] = $mstShipping['product_code'] ?? null;
                $data['shipping_no'] = $mstShipping['shipping_no'] ?? null;

                $data['delivery_no'] = null;
                $data['delivery_line_no'] = null;
                $data['delivery_day'] = null;
                $data['delivery_num'] = $mstShipping['shipping_num'] ?? null;
                $data['delivery_price'] = null;
                $data['delivery_amount'] = null;

                $this->entityManager->getRepository(MstShippingWSEOS::class)->insertData($data);

                // Add handling by task #1888
                $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy(['jan_code' => $data['jan_code']]);

                $shipping_num = ($mstShipping['shipping_num'] ?? 0) * ((!empty($product) && $product['quantity'] > 1) ? $product['quantity'] : 1);
            }

            return $shipping_num;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            $message = 'Import Data To mst_shipping_ws_eos';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return $shipping_num;
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
                $shipping_num = $this->importShippingNatEOS($item->toArray());

                if ((int) $shipping_num > 0) {
                    $item->setShippingNum((int) $item->getShippingNum() + (int) $shipping_num);
                    $this->entityManager->getRepository(DtOrderNatEOS::class)->save($item);
                }

                if ((int) $item->getShippingNum() == (int) $item->getQty()) {
                    $item->setShippingSentFlg(1);
                    $this->entityManager->getRepository(DtOrderNatEOS::class)->save($item);
                }
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
        $shipping_num = 0;

        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            $mstShipping = $this->entityManager->getRepository(MstShipping::class)->findOneBy([
                'cus_order_no' => $data['reqcd'],
                'cus_order_lineno' => $data['order_lineno'],
                'shipping_status' => 2,
            ], [
                'shipping_date' => 'DESC',
            ]);

            if (empty($mstShipping)) {
                log_error('No data mst_shipping '.$data['reqcd'].'-'.$data['order_lineno'].' status = 2');

                $message = 'Import Data To mst_shipping_nat_eos';
                $message .= "\n".'No data mst_shipping '.$data['reqcd'].'-'.$data['order_lineno'].' status = 2';
                $this->pushGoogleChat($message);

                return $shipping_num;
            }

            $mstShippingNatEOS = $this->entityManager->getRepository(MstShippingNatEOS::class)->findOneBy([
                'reqcd' => $data['reqcd'],
                'order_lineno' => $data['order_lineno'],
                'shipping_no' => $mstShipping['shipping_no'],
            ]);

            if (!empty($mstShippingNatEOS)) {
                return $shipping_num;
            }

            // Insert mst_shipping_nat_eos
            if (empty($mstShippingNatEOS)) {
                log_info('Import data mst_shipping_nat_eos '.$data['reqcd'].'-'.$data['order_lineno']);

                $delivery_no = '0633'.str_pad(substr((string) $mstShipping['shipping_no'], -8), 8, '0', STR_PAD_LEFT);
                $data['delivery_no'] = $delivery_no.$this->calcDeliveryNoDigit($delivery_no);
                $data['shipping_date'] = !empty($mstShipping['shipping_date']) ? date('Ymd', strtotime($mstShipping['shipping_date'])) : '';
                $data['shipping_no'] = $mstShipping['shipping_no'] ?? null;

                $this->entityManager->getRepository(MstShippingNatEOS::class)->insertData($data);

                // Add handling by task #1889
                $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy(['jan_code' => $data['jan']]);

                $shipping_num = ($mstShipping['shipping_num'] ?? 0) * ((!empty($product) && $product['quantity'] > 1) ? $product['quantity'] : 1);
            }

            return $shipping_num;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            $message = 'Import Data To mst_shipping_nat_eos';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return $shipping_num;
        }
    }

//    private function calcDeliveryNoDigit($number)
//    {
//        // make sure there is just numbers
//        $number = preg_replace('/[^0-9]/', '', $number);
//
//        // change order of values to use in foreach
//        $vals = array_reverse(str_split($number));
//
//        // multiply every other value by 2
//        $mult = true;
//        foreach ($vals as $k => $v) {
//            $vals[$k] = $mult ? $v * 2 : $v;
//            $vals[$k] = (string) ($vals[$k]);
//            $mult = !$mult;
//        }
//
//        // checks for two digits (>9)
//        $mp = array_map(function ($v) {
//            return ($v > 9) ? $v[0] + $v[1] : $v;
//        }, $vals);
//
//        // adds the values
//        $sum = array_sum($mp);
//
//        //gets the mod
//        $md = $sum % 10;
//
//        // checks how much for 10
//        // returns the value
//        return 10 - $md;
//    }

    private function calcDeliveryNoDigit($number)
    {
        $arr = str_split($number);
        $odd = 0;
        $mod = 0;
        for ($i = 0; $i < count($arr); $i++) {
            if (($i + 1) % 2 == 0) {
                $mod += intval($arr[$i]);
            } else {
                $odd += intval($arr[$i]);
            }
        }

        $cd = 10 - intval(substr((string) (($mod * 3) + $odd), -1));

        return $cd === 10 ? 0 : $cd;
    }
}
