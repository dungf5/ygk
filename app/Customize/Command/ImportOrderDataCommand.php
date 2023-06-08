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
use Customize\Entity\DtbOrderDaitoTest;
use Customize\Entity\DtBreakKey;
use Customize\Entity\DtCustomerRelation;
use Customize\Entity\DtOrder;
use Customize\Entity\DtOrderNatEOS;
use Customize\Entity\DtOrderStatus;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstCustomer;
use Customize\Entity\MstProduct;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
use Customize\Service\MailService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Eccube\Entity\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console import-order-data-command [param] */
class ImportOrderDataCommand extends Command
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

    protected static $defaultName = 'import-order-data-command';
    protected static $defaultDescription = 'Process Import Order Data Command';

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
        log_info('Start Process Import Order Data');
        $param = $input->getArgument('arg1') ?? null;

        if (!$param) {
            log_error('No param. Process stopped.');

            $message = 'Process Import Order Data. No param. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        $this->handleProcess($param);

        log_info('End Process Import Order Data');

        return 0;
    }

    public function handleProcess($param)
    {
        log_info("param {$param}");

        switch (trim($param)) {
            case 'nat-eos':
                /* Initial data */
                $this->customer_code = '7015';

                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                $this->customer = $this->entityManager->getRepository(MstCustomer::class)->findOneBy([
                    'customer_code' => $this->customer_code,
                ]);

                $this->customer_relation = $this->entityManager->getRepository(DtCustomerRelation::class)->findOneBy([
                    'customer_code' => $this->customer_code,
                ]);

                $shipping_code = $this->customer_relation['shipping_code'] ?? '';

                $this->customer_shipping = $this->entityManager->getRepository(MstCustomer::class)->findOneBy([
                    'customer_code' => $shipping_code,
                ]);

                $this->rate = $this->commonService->getTaxInfo()['tax_rate'] ?? 0;
                /* End - Initial data */

                $this->handleImportOrderNatEOS();
                sleep(1);
                $this->sendMailOrderSuccess();
                break;

            default:
                break;
        }
    }

    private function handleImportOrderNatEOS()
    {
        try {
            log_info('Start Handle Import Data To dtb_order, dt_order, dt_order_status');
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            // Get data to import
            $data = $this->entityManager->getRepository(DtOrderNatEOS::class)->findBy([
                'order_import_day' => date('Ymd'),
                'order_registed_flg' => 0,
                'error_type' => 0,
            ], [
                'reqcd' => 'ASC',
                'order_lineno' => 'ASC',
            ]);

            if (empty($data)) {
                log_info('No data');

                return;
            }

            $order_id = [];
            $order_error = [];

            foreach ($data as $value) {
                $item = $value->toArray();

                // Check not exists order error_type = 1
                //if (isset($order_error[$item['reqcd']]) || $this->entityManager->getRepository(DtOrderNatEOS::class)->findOneBy(['reqcd' => $item['reqcd'], 'error_type' => '1'])) {
                //    $order_error[$item['reqcd']] = 1;

                //    continue;
                //}

                $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                    'jan_code' => $item['jan'] ?? '',
                ]);

                if (empty($product)) {
                    log_error('No product with jan '.$item['jan']);
                    $message = 'Import data dt_order '.$item['reqcd'].'-'.$item['order_lineno'].' error';
                    $message .= "\n".'No product with jan '.$item['jan'];
                    $this->pushGoogleChat($message);

                    continue;
                }

                // Create dtb_order
                $this->entityManager->getConfiguration()->setSQLLogger(null);
                $this->entityManager->getConnection()->beginTransaction();

                if (!isset($order_id[$item['reqcd']])) {
                    $index = 1;
                    $id = $this->handleInsertDtbOrder();
                    $order_id[$item['reqcd']] = [$id, $index];

                    log_info('Import data dtb_order with id '.$id);
                } else {
                    $index = (int) $order_id[$item['reqcd']][1] + 1;
                    $id = $order_id[$item['reqcd']][0];

                    $order_id[$item['reqcd']] = [$id, $index];
                }

                //$wId = sprintf('%08d', $id);
                //$item['dtb_order_no'] = 'w_'.$wId;
                $item['dtb_order_no'] = $id;
                $item['dtb_order_line_no'] = $index;

                $item['order_no'] = $item['reqcd'];
                $item['order_line_no'] = $item['order_lineno'];
                $item['order_num'] = $item['qty'];
                $item['delivery_date'] = $item['delivery_day'];
                $item['jan_code'] = $item['jan'];
                $item['customer_code'] = $this->customer_code;
                $item['seikyu_code'] = $this->customer_relation['seikyu_code'] ?? '';
                $item['shipping_code'] = $this->customer_relation['shipping_code'] ?? '';
                $item['otodoke_code'] = $this->customer_relation['otodoke_code'] ?? '';
                $item['product_name'] = $product['product_name'] ?? '';

                $result = $this->importDtOrder($item, $product);
                $result1 = $this->importDtOrderStatus($item);

                if ($result && $result1) {
                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();

                    // Save to success array to send mail
                    $order_num = (!empty($product) && $product['quantity'] > 1) ? (int) ($item['order_num'] / $product['quantity']) : (int) $item['order_num'];
                    $this->success["{$item['reqcd']}"]['detail'][] = [
                        'jan_code' => $item['jan'],
                        'product_name' => $item['product_name'],
                        'order_price' => $item['cost'],
                        'order_num' => $order_num,
                    ];

                    $this->success["{$item['reqcd']}"]['summary'] = [
                        'order_amount' => ($this->success["{$item['reqcd']}"]['summary']['order_amount'] ?? 0) + ($item['cost'] * $order_num),
                        'tax' => $this->rate == 0 ? 0 : (int) ((($this->success["{$item['reqcd']}"]['summary']['order_amount'] ?? 0) + ($item['cost'] * $order_num)) * $this->rate / 100),
                        'order_company_name' => $this->customer_shipping['company_name'] ?? '',
                        'order_shop_name' => $this->customer_shipping['company_name'] ?? '',
                        'shipping_name' => $this->customer_shipping['company_name'] ?? '',
                        'postal_code' => $this->customer_shipping['postal_code'] ?? '',
                        'address' => ($this->customer_shipping['addr01'] ?? '').($this->customer_shipping['addr02'] ?? '').($this->customer_shipping['addr03'] ?? ''),
                        'phone_number' => $this->customer_shipping['phone_number'] ?? '',
                        'email' => $this->customer_shipping['email'] ?? '',
                        'delivery_date' => $item['delivery_day'],
                    ];

                    $this->success["{$item['reqcd']}"]['summary']['total_amount'] = ($this->success["{$item['reqcd']}"]['summary']['order_amount'] ?? 0) + (int) ($this->success["{$item['reqcd']}"]['summary']['tax'] ?? 0);

                    $value->setOrderRegistedFlg(1);
                    $this->entityManager->getRepository(DtOrderWSEOS::class)->save($value);
                } else {
                    $this->entityManager->getConnection()->rollBack();
                }
            }

            log_info('End Handle Import Data To dtb_order, dt_order, dt_order_status');

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function handleInsertDtbOrder()
    {
        $dtbOrderData = [
            'customer' => null,
            'name01' => '',
            'name02' => '',
        ];
        //$id = $this->entityManager->getRepository(Order::class)->insertData($dtbOrderData);
        $id = $this->entityManager->getRepository(DtbOrderDaitoTest::class)->insertData($dtbOrderData);

        if (!empty($id)) {
            return $id;
        } else {
            sleep(1);

            return $this->handleInsertDtbOrder();
        }
    }

    private function importDtOrder($data, $product)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $common = new MyCommonService($this->entityManager);

            $dtOrder = $this->entityManager->getRepository(DtOrder::class)->findOneBy([
                'order_no' => $data['reqcd'],
                'order_lineno' => $data['order_lineno'],
            ]);

            // Create order if empty
            if (empty($dtOrder)) {
                log_info('Import data dt_order '.$data['reqcd'].'-'.$data['order_lineno']);

                $data['demand_unit'] = (!empty($product) && $product['quantity'] > 1) ? 'CS' : 'PC';
                $data['demand_quantity'] = (!empty($product) && $product['quantity'] > 1) ? (int) ($data['order_num'] / $product['quantity']) : (int) $data['order_num'];

                $dtPrice = $common->getDtPrice($product['product_code'], $this->customer_code, $this->customer_relation['shipping_code'] ?? '');

                if (!empty($dtPrice)) {
                    $unit_price = $dtPrice['price_s01'] ?? 0;
                } else {
                    $unit_price = $product['unit_price'] ?? 0;
                }

                $data['order_price'] = (!empty($product) && $product['quantity'] > 1) ? ($unit_price * ((int) ($data['order_num'] / $product['quantity']))) : $unit_price;

                $location = $this->commonService->getCustomerLocation($this->customer_code);
                $data['location'] = $location ?? '';
                $data['fvehicleno'] = '';
                $data['ftrnsportcd'] = '87001';

                return $this->entityManager->getRepository(DtOrder::class)->insertData($data);
            } else {
                return 1;
            }
        } catch (\Exception $e) {
            log_error('Insert dt_order error');
            log_error($e->getMessage());

            $message = 'Import data dt_order '.$data['reqcd'].'-'.$data['order_lineno'].' error';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return 0;
        }
    }

    private function importDtOrderStatus($data)
    {
        try {
            $dtOrderStatus = $this->entityManager->getRepository(DtOrderStatus::class)->findOneBy([
                'cus_order_no' => $data['reqcd'],
                'cus_order_lineno' => $data['order_lineno'],
            ]);

            // Create order_status if empty
            if (empty($dtOrderStatus)) {
                log_info('Import data dt_order_status '.$data['reqcd'].'-'.$data['order_lineno']);

                return $this->entityManager->getRepository(DtOrderStatus::class)->insertData($data);
            } else {
                return 1;
            }
        } catch (\Exception $e) {
            log_error('Insert dt_order_status error');
            log_error($e->getMessage());

            $message = 'Import data dt_order_status '.$data['reqcd'].'-'.$data['order_lineno'].' error';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return 0;
        }
    }

    private function sendMailOrderSuccess()
    {
        if (!count($this->success)) {
            return;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $common = new MyCommonService($this->entityManager);

        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/eos_order_success.twig',
        ];

        $order_success = [];
        foreach ($this->success as $key => $success) {
            $information['success_data'] = $success;

            try {
                log_info('[NAT-EOS] Send Mail Order Success. '.$key);
                $this->mailService->sendMailOrderSuccessEOS($information);
                $order_success[] = $key;
            } catch (\Exception $e) {
                log_error($e->getMessage());
                $this->pushGoogleChat($e->getMessage());
            }
        }

        // Update total order success to dt_break_key
        //$total_order_success = str_pad((string) count($this->success), 3, '0', STR_PAD_LEFT);
        $break_key_data = [
            'customer_code' => $this->customer_code,
            'break_key' => count($this->success),
        ];

        $break_key = $this->entityManager->getRepository(DtBreakKey::class)->insertOrUpdate($break_key_data);
        if ($break_key) {
            for ($i = 1; $i <= $break_key; $i++) {
                if ($i > ($break_key - count($this->success)) && isset($order_success[$i - ($break_key - count($this->success)) - 1])) {
                    $order_no = $order_success[$i - ($break_key - count($this->success)) - 1];

                    $dtOrder = $this->entityManager->getRepository(DtOrder::class)->findBy([
                        'order_no' => $order_no,
                    ]);

                    $customer_fusrdec1 = $this->customer['fusrdec1'] ?? 0;
                    $sum_order_amout = $common->getSumOrderAmout($order_no);
                    $fvehicleno_start = (int) $sum_order_amout > (int) $customer_fusrdec1 ? '0' : '1';

                    foreach ($dtOrder as $order) {
                        // Change by task #1812
                        //$fvehicleno_end = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                        $fvehicleno_end = '001';

                        $order->setFvehicleno($fvehicleno_start.$fvehicleno_end);
                        $this->entityManager->getRepository(DtOrder::class)->save($order);
                    }
                }
            }
        }

        return;
    }
}
