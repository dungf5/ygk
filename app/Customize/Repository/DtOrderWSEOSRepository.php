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

use Customize\Entity\DtOrderWSEOS;
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

            $object = new DtOrderWSEOS();
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
            $object->setOrderRegistedFlg(0);

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return;
        } catch (\Exception $e) {
            log_info('Insert dt_order_ws_eos error');
            log_info($e->getMessage());

            return;
        }
    }

    public function updateData($object, $data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            $object = $this->entityManager->getRepository(DtOrderWSEOS::class)->findOneBy([
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
}
