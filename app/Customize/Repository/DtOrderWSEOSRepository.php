<?php

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

namespace Customize\Repository;

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\DtOrderWSEOS;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderWSEOSRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderWSEOS::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtOrderWSEOS();
            $object->setOrderType((int) $data['order_type']);
            $object->setWebOrderType((int) $data['web_order_type']);
            $object->setOrderDate($data['order_date'] ? date('Y-m-d', strtotime($data['order_date'])) : null);
            $object->setOrderNo($data['order_no']);
            $object->setSystemCode($data['system_code']);
            $object->setOrderCompanyCode($data['order_company_code']);
            $object->setOrderShopCode($data['order_shop_code']);
            $object->setOrderStaffCode($data['order_staff_code']);
            $object->setSalesCompanyCode($data['sales_company_code']);
            $object->setSalesStaffCode($data['sales_staff_code']);
            $object->setOrderCompanyName($data['order_company_name']);
            $object->setDeliveryFlag($data['delivery_flag']);
            $object->setShippingCompanyCode($data['shipping_company_code']);
            $object->setShippingShopCode($data['shipping_shop_code']);
            $object->setShippingName($data['shipping_name']);
            $object->setShippingAddress1($data['shipping_address1']);
            $object->setShippingAddress2($data['shipping_address2']);
            $object->setShippingPostCode($data['shipping_post_code']);
            $object->setShippingTel($data['shipping_tel']);
            $object->setShippingFax($data['shipping_fax']);
            $object->setDeliveryDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : null);
            $object->setExportType((int) $data['export_type']);
            $object->setAproveType((int) $data['aprove_type']);
            $object->setOrderCancel((int) $data['order_cancel']);
            $object->setDeleteFlag((int) $data['delete_flag']);
            $object->setOrderVoucherType((int) $data['order_voucher_type']);
            $object->setOrderLineNo((int) $data['order_line_no']);
            $object->setOrderFlag($data['order_flag']);
            $object->setOrderSystemCode($data['order_system_code']);
            $object->setOrderStaffName($data['order_staff_name']);
            $object->setOrderShopName($data['order_shop_name']);
            $object->setProductMakerCode($data['product_maker_code']);
            $object->setProductName($data['product_name']);
            $object->setOrderNum((int) $data['order_num']);
            $object->setOrderPrice((int) $data['order_price']);
            $object->setOrderAmount((int) $data['order_amount']);
            $object->setTaxType($data['tax_type']);
            $object->setRemarksLineNo($data['remarks_line_no']);
            $object->setJanCode($data['jan_code']);
            $object->setCashTypeCode($data['cash_type_code']);
            $object->setOrderCreateDay($data['order_create_day'] ? date('Y-m-d', strtotime($data['order_create_day'])) : null);
            $object->setOrderUpdateDay($data['order_update_day'] ? date('Y-m-d', strtotime($data['order_update_day'])) : null);
            $object->setOrderImportDay(date('Ymd'));
            $object->setOrderRegistedFlg(0);
            $object->setErrorType(0);
            $object->setCustomerCode($data['customer_code']);
            $object->setShippingCode($data['shipping_code']);
            $object->setOtodokeCode($data['otodoke_code']);
            $object->setProductCode($data['product_code']);

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return;
        } catch (\Exception $e) {
            log_info('Insert dt_order_ws_eos error');
            log_info($e->getMessage());

            return;
        }
    }

    public function updateData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = $this->findOneBy([
                'order_no' => $data['order_no'] ?? '',
                'order_line_no' => $data['order_line_no'] ?? '',
            ]);

            if (!empty($object)) {
                $object->setOrderType((int) $data['order_type']);
                $object->setWebOrderType((int) $data['web_order_type']);
                $object->setOrderDate($data['order_date'] ? date('Y-m-d H:i:s', strtotime($data['order_date'])) : null);
                $object->setOrderNo($data['order_no']);
                $object->setSystemCode($data['system_code']);
                $object->setOrderCompanyCode($data['order_company_code']);
                $object->setOrderShopCode($data['order_shop_code']);
                $object->setOrderStaffCode($data['order_staff_code']);
                $object->setSalesCompanyCode($data['sales_company_code']);
                $object->setSalesStaffCode($data['sales_staff_code']);
                $object->setOrderCompanyName($data['order_company_name']);
                $object->setDeliveryFlag($data['delivery_flag']);
                $object->setShippingCompanyCode($data['shipping_company_code']);
                $object->setShippingShopCode($data['shipping_shop_code']);
                $object->setShippingName($data['shipping_name']);
                $object->setShippingAddress1($data['shipping_address1']);
                $object->setShippingAddress2($data['shipping_address2']);
                $object->setShippingPostCode($data['shipping_post_code']);
                $object->setShippingTel($data['shipping_tel']);
                $object->setShippingFax($data['shipping_fax']);
                $object->setDeliveryDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : null);
                $object->setExportType((int) $data['export_type']);
                $object->setAproveType((int) $data['aprove_type']);
                $object->setOrderCancel((int) $data['order_cancel']);
                $object->setDeleteFlag((int) $data['delete_flag']);
                $object->setOrderVoucherType((int) $data['order_voucher_type']);
                $object->setOrderLineNo((int) $data['order_line_no']);
                $object->setOrderFlag($data['order_flag']);
                $object->setOrderSystemCode($data['order_system_code']);
                $object->setOrderStaffName($data['order_staff_name']);
                $object->setOrderShopName($data['order_shop_name']);
                $object->setProductMakerCode($data['product_maker_code']);
                $object->setProductName($data['product_name']);
                $object->setOrderNum((int) $data['order_num']);
                $object->setOrderPrice((int) $data['order_price']);
                $object->setOrderAmount((int) $data['order_amount']);
                $object->setTaxType($data['tax_type']);
                $object->setRemarksLineNo($data['remarks_line_no']);
                $object->setJanCode($data['jan_code']);
                $object->setCashTypeCode($data['cash_type_code']);
                $object->setOrderCreateDay($data['order_create_day'] ? date('Y-m-d H:i:s', strtotime($data['order_create_day'])) : null);
                $object->setOrderUpdateDay($data['order_update_day'] ? date('Y-m-d H:i:s', strtotime($data['order_update_day'])) : null);
                $object->setErrorContent1(null);
                $object->setErrorContent2(null);
                $object->setErrorContent3(null);
                $object->setErrorContent4(null);
                $object->setErrorContent5(null);
                $object->setErrorContent6(null);
                $object->setErrorContent7(null);
                $object->setErrorContent8(null);
                $object->setErrorContent9(null);
                $object->setErrorContent10(null);

                $this->getEntityManager()->persist($object);
                $this->getEntityManager()->flush();
            }

            return;
        } catch (\Exception $e) {
            log_info('Update dt_order_ws_eos error');
            log_info($e->getMessage());

            return;
        }
    }

    public function updateError($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = $this->findOneBy([
                'order_no' => $data['order_no'],
                'order_line_no' => $data['order_line_no'],
            ]);

            if (!empty($object)) {
                foreach ($data as $key => $value) {
                    if ($key == 'error_content1') {
                        $object->setErrorContent1($value);
                    }
                    if ($key == 'error_content2') {
                        $object->setErrorContent2($value);
                    }
                    if ($key == 'error_content3') {
                        $object->setErrorContent3($value);
                    }
                    if ($key == 'error_content4') {
                        $object->setErrorContent4($value);
                    }
                    if ($key == 'error_content5') {
                        $object->setErrorContent5($value);
                    }
                    if ($key == 'error_content6') {
                        $object->setErrorContent6($value);
                    }
                    if ($key == 'error_content7') {
                        $object->setErrorContent7($value);
                    }
                    if ($key == 'error_content8') {
                        $object->setErrorContent8($value);
                    }
                    if ($key == 'error_content9') {
                        $object->setErrorContent9($value);
                    }
                    if ($key == 'error_content10') {
                        $object->setErrorContent10($value);
                    }
                }
                $object->setErrorType(1);

                $this->getEntityManager()->persist($object);
                $this->getEntityManager()->flush();
            }

            return;
        } catch (\Exception $e) {
            return;
        }
    }
}
