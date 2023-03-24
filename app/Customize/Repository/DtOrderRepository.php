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
use Customize\Entity\DtOrder;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrder::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return 0;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtOrder();
            $object->setCustomerCode($data['customer_code'] ?? '');
            $object->setSeikyuCode($data['customer_code'] ?? '');
            $object->setOrderNo($data['order_no'] ?? '');
            $object->setOrderLineno($data['order_line_no'] ?? '');
            $object->setShippingCode($data['shipping_code'] ?? '');
            $object->setOtodokeCode($data['otodoke_code'] ?? '');
            $object->setOrderDate(new \DateTime());
            $object->setDeliPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
            $object->setShipingPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
            $object->setItemNo($data['jan_code'] ?? '');
            $object->setDemandQuantity((int) $data['order_num']);
            $object->setDemandUnit($data['demand_unit']);
            $object->setOrderPrice((float) $data['order_price']);
            $object->setUnitPriceStatus('FOR');
            $object->setShipingDepositCode($data['location']);
            $object->setDeploy('XB');
            $object->setCompanyId('XB');
            $object->setProductCode($data['product_code'] ?? '');
            $object->setDynaModelSeg2($data['order_no'] ?? '');
            $object->setDynaModelSeg3('2');
            $object->setDynaModelSeg4($data['order_no'] ?? '');
            $object->setDynaModelSeg5($data['order_line_no'] ?? '');
            $object->setDynaModelSeg6($data['remarks_line_no'] ?? '');
            $object->setRequestFlg('Y');

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return 1;
        } catch (\Exception $e) {
            log_info('Insert dt_order error');
            log_info($e->getMessage());

            return 0;
        }
    }
}
