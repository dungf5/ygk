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
use Customize\Entity\DtBreakKey;
use Customize\Entity\DtOrder;
use Customize\Entity\DtOrderNatEOS;
use Customize\Entity\DtOrderStatus;
use Customize\Entity\DtOrderWSEOS;
use Customize\Entity\MstCustomer;
use Customize\Entity\MstProduct;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
use Customize\Service\CurlPost;
use Customize\Service\FTPService;
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

/* Run Batch: php bin/console validate-csv-data-command [param] {option} */
class ValidateCsvDataCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;

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
    private $csvService;
    private $ftpService;
    private $errors = [];
    private $success = [];
    private $rate = 0;
    private $customer_code = '';
    private $shipping_code = '';
    private $customer = null;
    private $customer_7001 = null;
    private $check_validate = false;

    protected static $defaultName = 'validate-csv-data-command';
    protected static $defaultDescription = 'Process Validate CSV Data Command';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->csvService = new CSVService($entityManager);
        $this->ftpService = new FTPService($entityManager);
        $this->mailService = $mailService;
        $this->commonService = new MyCommonService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('check', null, InputOption::VALUE_OPTIONAL, 'Option check validate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        log_info('---------------------------------------');
        log_info('Start Process Validate CSV Data');
        $param = $input->getArgument('arg1') ?? null;
        $option = $input->getOption('check');

        if (!empty($option) && ($option == 'true' || $option == '1')) {
            $this->check_validate = true;
        }

        if (!$param) {
            log_error('No param. Process stopped.');

            $message = 'Process Validate CSV Data. No param. Process stopped.';
            $this->pushGoogleChat($message);

            return 0;
        }

        $this->handleProcess($param);

        log_info('End Process Validate CSV Data');
        //$io->success('End Process Validate CSV Data');

        return 0;
    }

    public function handleProcess($param)
    {
        switch (trim($param)) {
            case 'ws-eos':
                $this->handleWSEOS();
                break;

            case 'nat-eos':
                $this->handleNatEOS();
                break;

            default:
                break;
        }
    }

    private function handleWSEOS()
    {
        /* Initial data */
        $this->customer_code = '7001';
        $this->shipping_code = '7001001000';

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $this->customer_7001 = $this->entityManager->getRepository(MstCustomer::class)->findOneBy([
            'customer_code' => $this->customer_code,
        ]);

        $this->customer = $this->entityManager->getRepository(MstCustomer::class)->findOneBy([
            'customer_code' => $this->shipping_code,
        ]);

        $this->rate = $this->commonService->getTaxInfo()['tax_rate'] ?? 0;
        /* End - Initial data */

        $this->handleValidateWSEOS();
        sleep(1);
        $this->sendMailWSEOSValidateError();

        if ($this->check_validate == false) {
            sleep(1);
            $this->handleImportOrderWSEOS();
            sleep(1);
            $this->sendMailOrderSuccess();
        }
    }

    private function handleValidateWSEOS()
    {
        try {
            log_info('Start Handle Validate WS EOS DATA '.($this->check_validate ? 'With Check' : 'Without Check'));
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            // Get data to validate ws eos
            $data = $this->entityManager->getRepository(DtOrderWSEOS::class)->findBy([
                'order_import_day' => date('Ymd'),
                'order_registed_flg' => 0,
            ]);

            if (empty($data)) {
                log_info('No data');

                return;
            }

            foreach ($data as $item) {
                $this->validateWSEOS($item['order_no'], $item['order_line_no']);
            }

            if (count($this->errors)) {
                foreach ($this->errors as $error) {
                    $this->entityManager->getRepository(DtOrderWSEOS::class)->updateError($error);
                }
            }

            log_info('End Handle Validate WS EOS DATA '.($this->check_validate ? 'With Check' : 'Without Check'));
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function validateWSEOS($order_no, $order_line_no)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $common = new MyCommonService($this->entityManager);

            $object = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy([
                'order_no' => $order_no,
                'order_line_no' => $order_line_no,
            ]);

            $otodoke_code = '7001001'.str_pad($object['shipping_shop_code'] ?? '', 3, '0', STR_PAD_LEFT);

            $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                'jan_code' => $object['jan_code'],
            ]);

            if (empty($object)) {
                log_info("No order ({$order_no}-{$order_line_no})");

                return;
            }

            // Set more data
            $object->setCustomerCode($this->customer_code);
            $object->setShippingCode($this->shipping_code);
            $object->setOtodokeCode($otodoke_code);
            $object->setProductCode(!empty($product) ? $product['product_code'] : '');

            // Array contain error (if any)
            $error = [];

            log_info("Validate order ({$order_no}-{$order_line_no})");

            // Check flag of check validate
            if ($this->check_validate) {
                // validate order_date
                if (empty($object['order_date']) || date('Y-m-d', strtotime($object['order_date'])) < date('Y-m-d')) {
                    $error['error_content1'] = '発注日付が過去日付になっています';
                }

                // Validate customer
                $dtCusRelation = $common->getDtCustomerRelation($this->customer_code, $this->shipping_code, $otodoke_code);
                if (empty($dtCusRelation)) {
                    $error['error_content2'] = '出荷先支店コード(顧客関連)が登録されていません';
                }

                // Validate shipping_shop_code
                if (empty($object['shipping_shop_code']) || empty($this->customer)) {
                    $error['error_content3'] = '出荷先支店コード(顧客情報)が登録されていません';
                }

                // validate delivery_date
                if (empty($object['delivery_date']) || (date('Y-m-d', strtotime($object['delivery_date'])) < date('Y-m-d'))) {
                    $error['error_content4'] = '納入希望日が過去日付になっています';
                }

                // Validate jan_code
                if (empty($object['jan_code']) || empty($product)) {
                    $error['error_content5'] = 'JANコードが存在しません';
                }

                // Validate discontinued_date
                if (!empty($product) && !empty($product['discontinued_date']) && date('Y-m-d') > date('Y-m-d', strtotime($product['discontinued_date']))) {
                    $error['error_content6'] = '対象商品は廃番品となっております';
                }

                // Validdate special_order_flg
                if (!empty($this->customer) && $this->customer['special_order_flg'] == 0 && !empty($product) && !empty($product['special_order_flg']) && strtolower($product['special_order_flg']) == 'y') {
                    $error['error_content7'] = '取り扱い対象商品ではありません';
                }

                // Validate order_num
                if (!empty($product)) {
                    if ((int) $object['order_num'] % (int) $product['quantity']) {
                        $error['error_content8'] = '発注数量の販売単位に誤りがあります';
                    }
                }

                // Validate price
                //if (!empty($product)) {
                //    $dtPrice = $common->getDtPrice($product['product_code'], $this->customer_code, $this->shipping_code);

                //    if (empty($dtPrice) || (int) $dtPrice['price_s01'] != (int) ($object['order_price'] / (!empty($product['quantity']) ? $product['quantity'] : 1))) {
                //        $error['error_content9'] = '発注単価が異なっています';
                //    }
                //}
            }

            if (count($error)) {
                $error['order_no'] = $order_no;
                $error['order_line_no'] = $order_line_no;

                $this->errors[] = $error;
            }

            $this->entityManager->getRepository(DtOrderWSEOS::class)->save($object);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function sendMailWSEOSValidateError()
    {
        if (!count($this->errors)) {
            return;
        }

        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/ws_eos_validate_error.twig',
            'error_data' => $this->errors,
        ];

        try {
            log_info('[WS-EOS] Send Mail Validate Error.');
            $this->mailService->sendMailErrorWSEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function handleImportOrderWSEOS()
    {
        try {
            log_info('Start Handle Import Data To dtb_order, dt_order, dt_order_status');
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);

            // Get data to import
            $data = $this->entityManager->getRepository(DtOrderWSEOS::class)->findBy([
                'order_import_day' => date('Ymd'),
                'order_registed_flg' => 0,
                'error_type' => 0,
            ], [
                'shipping_shop_code' => 'ASC',
                'order_shop_code' => 'ASC',
                'order_no' => 'ASC',
                'order_line_no' => 'ASC',
            ]);

            if (count($data)) {
                $order_id = [];
                $order_error = [];

                foreach ($data as $value) {
                    $item = $value->toArray();

                    // Check not exists order error_type = 1
                    if (isset($order_error[$item['order_no']]) || $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy(['order_no' => $item['order_no'], 'error_type' => '1'])) {
                        $order_error[$item['order_no']] = 1;

                        continue;
                    }

                    // Create dtb_order
                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    if (!isset($order_id[$item['order_no']])) {
                        $dtbOrderData = [
                            'customer' => null,
                            'name01' => '',
                            'name02' => '',
                        ];
                        $id = $this->entityManager->getRepository(Order::class)->insertData($dtbOrderData);
                        $order_id[$item['order_no']] = $id;

                        log_info('Import data dtb_order with id '.$id);
                    } else {
                        $id = $order_id[$item['order_no']];
                    }

                    $item['dtb_order_no'] = $id;
                    $item['dtb_order_line_no'] = $item['order_line_no'];

                    $result = $this->importDtOrder($item);
                    $result1 = $this->importDtOrderStatus($item);

                    if ($result && $result1) {
                        $this->entityManager->flush();
                        $this->entityManager->getConnection()->commit();

                        // Save to success array to send mail
                        $this->success["{$item['order_no']}"]['detail'][] = [
                            'jan_code' => $item['jan_code'],
                            'product_name' => $item['product_name'],
                            'order_price' => $item['order_price'],
                            'order_num' => $item['order_num'],
                        ];

                        $this->success["{$item['order_no']}"]['summary'] = [
                            'order_amount' => ($this->success["{$item['order_no']}"]['summary']['order_amount'] ?? 0) + ($item['order_price'] * $item['order_num']),
                            'tax' => $this->rate == 0 ? 0 : (int) ((($this->success["{$item['order_no']}"]['summary']['order_amount'] ?? 0) + ($item['order_price'] * $item['order_num'])) / $this->rate),
                            'order_company_name' => $this->customer['company_name'] ?? '',
                            'order_shop_name' => $this->customer['company_name'] ?? '',
                            'shipping_name' => $this->customer['company_name'] ?? '',
                            'postal_code' => $this->customer['postal_code'] ?? '',
                            'address' => ($this->customer['addr01'] ?? '').($this->customer['addr02'] ?? '').($this->customer['addr03'] ?? ''),
                            'phone_number' => $this->customer['phone_number'] ?? '',
                            'email' => $this->customer['email'] ?? '',
                            'delivery_date' => $item['delivery_date'],
                        ];

                        $this->success["{$item['order_no']}"]['summary']['total_amount'] = ($this->success["{$item['order_no']}"]['summary']['order_amount'] ?? 0) + (int) ($this->success["{$item['order_no']}"]['summary']['tax'] ?? 0);

                        $value->setOrderRegistedFlg(1);
                        $this->entityManager->getRepository(DtOrderWSEOS::class)->save($value);
                    } else {
                        $this->entityManager->getConnection()->rollBack();
                    }
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

    private function importDtOrder($data)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $common = new MyCommonService($this->entityManager);

            $dtOrder = $this->entityManager->getRepository(DtOrder::class)->findOneBy([
                'order_no' => $data['order_no'],
                'order_lineno' => $data['order_line_no'],
            ]);

            // Create order if empty
            if (empty($dtOrder)) {
                log_info('Import data dt_order '.$data['order_no'].'-'.$data['order_line_no']);

                $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                    'jan_code' => $data['jan_code'] ?? '',
                ]);

                $data['demand_unit'] = (!empty($product) && $product['quantity'] > 1) ? 'CS' : 'PC';
                $data['demand_quantity'] = (!empty($product) && $product['quantity'] > 1) ? (int) ($data['order_num'] / $product['quantity']) : (int) $data['order_num'];

                //$data['order_price'] = (!empty($product) && $product['quantity'] > 1) ? ($data['order_price'] * $product['quantity']) : $data['order_price'];

                $dtPrice = $common->getDtPrice($product['product_code'], $this->customer_code, $this->shipping_code);

                if (!empty($dtPrice)) {
                    $data['order_price'] = $dtPrice['price_s01'] ?? 0;
                } else {
                    $data['order_price'] = $product['unit_price'] ?? 0;
                }

                $location = $this->commonService->getCustomerLocation($data['customer_code']);
                $data['location'] = $location ?? 'XB0201001';

                $customer_fusrdec1 = $this->customer_7001['fusrdec1'] ?? 0;
                $sum_order_amout = $common->getSumOrderAmoutWSEOS($data['order_no']);
                $data['fvehicleno'] = (int) $sum_order_amout > (int) $customer_fusrdec1 ? '0' : '1';

                return $this->entityManager->getRepository(DtOrder::class)->insertData($data);
            }

            return 0;
        } catch (\Exception $e) {
            log_error('Insert dt_order error');
            log_error($e->getMessage());

            $message = 'Import data dt_order '.$data['order_no'].'-'.$data['order_line_no'].' error';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return 0;
        }
    }

    private function importDtOrderStatus($data)
    {
        try {
            $dtOrderStatus = $this->entityManager->getRepository(DtOrderStatus::class)->findOneBy([
                'cus_order_no' => $data['order_no'],
                'cus_order_lineno' => $data['order_line_no'],
            ]);

            // Create order_status if empty
            if (empty($dtOrderStatus)) {
                log_info('Import data dt_order_status '.$data['order_no'].'-'.$data['order_line_no']);

                return $this->entityManager->getRepository(DtOrderStatus::class)->insertData($data);
            }

            return 0;
        } catch (\Exception $e) {
            log_error('Insert dt_order_status error');
            log_error($e->getMessage());

            $message = 'Import data dt_order_status '.$data['order_no'].'-'.$data['order_line_no'].' error';
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

        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/ws_eos_order_success.twig',
        ];

        $order_success = [];
        foreach ($this->success as $key => $success) {
            $information['success_data'] = $success;

            try {
                log_info('[WS-EOS] Send Mail Order Success. '.$key);
                $this->mailService->sendMailOrderSuccessWSEOS($information);
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
                    $dtOrder = $this->entityManager->getRepository(DtOrder::class)->findBy([
                        'order_no' => $order_success[$i - ($break_key - count($this->success)) - 1],
                    ]);

                    foreach ($dtOrder as $order) {
                        $fvehicleno = $order->getFvehicleno();
                        $fvehicleno2 = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

                        $order->setFvehicleno($fvehicleno.$fvehicleno2);
                        $this->entityManager->getRepository(DtOrder::class)->save($order);
                    }
                }
            }
        }

        return;
    }

    private function handleNatEOS()
    {
        /* Initial data */
        $this->customer_code = '7015';
        $this->shipping_code = '7015001000';
        /* End - Initial data */

        $this->handleValidateNatEOS();
        sleep(1);
        $this->sendMailNatEOSValidateError();
    }

    private function handleValidateNatEOS()
    {
        try {
            log_info('Start Handle Validate NAT EOS DATA '.($this->check_validate ? 'With Check' : 'Without Check'));
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            // Get data to validate nat eos
            $data = $this->entityManager->getRepository(DtOrderNatEOS::class)->findBy([
                'order_import_day' => date('Ymd'),
                'order_registed_flg' => 0,
            ]);

            if (empty($data)) {
                log_info('No data');

                return;
            }

            foreach ($data as $item) {
                $this->validateNatEOS($item['reqcd'], $item['jan']);
            }

            if (count($this->errors)) {
                foreach ($this->errors as $error) {
                    $this->entityManager->getRepository(DtOrderNatEOS::class)->updateError($error);
                }
            }

            log_info('End Handle Validate NAT EOS DATA '.($this->check_validate ? 'With Check' : 'Without Check'));
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function validateNatEOS($reqcd, $jan)
    {
        try {
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $common = new MyCommonService($this->entityManager);

            $object = $this->entityManager->getRepository(DtOrderNatEOS::class)->findOneBy([
                'reqcd' => $reqcd,
                'jan' => $jan,
            ]);

            if (empty($object)) {
                log_info("No order ({$reqcd}-{$jan})");

                return;
            }

            $otodoke_code = '7015001000';
            $product = $this->entityManager->getRepository(MstProduct::class)->findOneBy([
                'jan_code' => $object['jan'],
            ]);

            // Set more data
            $object->setCustomerCode($this->customer_code);
            $object->setShippingCode($this->shipping_code);
            $object->setOtodokeCode($otodoke_code);
            $object->setProductCode(!empty($product) ? $product['product_code'] : '');

            // Array contain error (if any)
            $error = [];

            log_info("Validate order ({$reqcd}-{$jan})");

            // Check flag of check validate
            if ($this->check_validate) {
                // Validate jan_code
                if (empty($object['jan']) || empty($product)) {
                    $error['error_content1'] = 'JANコードが存在しません';
                }

                // Validate discontinued_date
                if (!empty($product) && !empty($product['discontinued_date']) && date('Y-m-d') > date('Y-m-d', strtotime($product['discontinued_date']))) {
                    $error['error_content2'] = '対象商品は廃番品となっております';
                }

                // Validdate special_order_flg
                if (!empty($product) && !empty($product['special_order_flg']) && strtolower($product['special_order_flg']) == 'y') {
                    $error['error_content3'] = '取り扱い対象商品ではありません';
                }

                // Validate qty
                if (!empty($product)) {
                    if ((int) $object['qty'] % (int) $product['quantity']) {
                        $error['error_content4'] = '発注数の販売単位に誤りがあります';
                    }
                }

                // Validate cost
                if (!empty($product)) {
                    $dtPrice = $common->getDtPrice($product['product_code'], $this->customer_code, $this->shipping_code);

                    if (empty($dtPrice) || (int) $dtPrice['price_s01'] != (int) $object['cost']) {
                        $error['error_content5'] = '発注単価が異なっています';
                    }
                }

                // validate delivery_day
                if (empty($object['delivery_day']) || (date('Y-m-d', strtotime($object['delivery_day'])) < date('Y-m-d'))) {
                    $error['error_content6'] = '納期が過去日付になっています';
                }
            }

            if (count($error)) {
                $error['reqcd'] = $reqcd;
                $error['jan'] = $jan;

                $this->errors[] = $error;
            }

            $this->entityManager->getRepository(DtOrderNatEOS::class)->save($object);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }

    private function sendMailNatEOSValidateError()
    {
        if (!count($this->errors)) {
            return;
        }

        $information = [
            'email' => getenv('EMAIL_WS_EOS') ?? '',
            'email_cc' => getenv('EMAILCC_WS_EOS') ?? '',
            'email_bcc' => getenv('EMAILBCC_WS_EOS') ?? '',
            'file_name' => 'Mail/nat_eos_validate_error.twig',
            'error_data' => $this->errors,
        ];

        try {
            log_info('[NAT-EOS] Send Mail Validate Error.');
            $this->mailService->sendMailErrorNatEOS($information);

            return;
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->pushGoogleChat($e->getMessage());

            return;
        }
    }
}
