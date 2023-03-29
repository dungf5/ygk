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
use Customize\Entity\MstShippingWSEOS;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstShippingWSEOSRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstShippingWSEOS::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return 0;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new MstShippingWSEOS();
            /* fixed value */
            $object->setSystemCode('4');
            $object->setSalesShopCode('0');
            $object->setDeliveryType('0');
            $object->setDeliveryFlagTmp('0');
            $object->setImportType('1');
            $object->setSystemCode1('4');
            $object->setSalesShipCode1('0');
            $object->setDeliveryType1('0');
            $object->setOrderType('0');
            $object->setTaxType('0');
            $object->setShippingSendFlg('1');
            $object->setShippingSentFlg('0');
            /* End - fixed value */

            $object->setSalesCompanyCode($data['sales_company_code']);
            $object->setOrderCompanyCode($data['order_company_code']);
            $object->setOrderShopCode($data['order_shop_code']);
            $object->setShippingCompanyCode($data['shipping_company_code']);
            $object->setShippingShopCode($data['shipping_shop_code']);
            $object->setShippingName($data['shipping_name']);
            $object->setSalesCompanyCode1($data['sales_company_code']);
            $object->setOrderNo($data['order_no']);
            $object->setOrderLineNo($data['order_line_no']);
            $object->setOrderFlag($data['order_flag']);
            $object->setOrderStaffName($data['order_staff_name']);
            $object->setOrderShopName($data['order_shop_name']);
            $object->setProductName($data['product_name']);
            $object->setOrderNum((int) $data['order_num']);
            $object->setOrderPrice((int) $data['order_price']);
            $object->setOrderAmount((int) $data['order_amount']);
            $object->setOrderDate($data['order_date'] ? date('Y-m-d', strtotime($data['order_date'])) : null);
            $object->setRemarksLineNo($data['remarks_line_no']);
            $object->setJanCode($data['jan_code']);
            $object->setShippingDate($data['shipping_date'] ? date('Y-m-d', strtotime($data['shipping_date'])) : null);
            $object->setProductMakerCode($data['product_maker_code']);
            $object->setShippingNo($data['shipping_no']);
            $object->setDeliveryNo($data['delivery_no']);
            $object->setDeliveryNo1($data['delivery_no']);
            $object->setDeliveryLineNo($data['delivery_line_no']);
            $object->setDeliveryDay($data['delivery_day'] ? date('Y-m-d H:i:s', strtotime($data['delivery_day'])) : null);
            $object->setDeliveryNum((int) $data['delivery_num']);
            $object->setDeliveryPrice((int) $data['delivery_price']);
            $object->setDeliveryAmount((int) $data['delivery_amount']);

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return 1;
        } catch (\Exception $e) {
            log_info('Insert mst_shipping_ws_eos error');
            log_info($e->getMessage());

            return 0;
        }
    }
}
