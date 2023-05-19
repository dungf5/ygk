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
use Customize\Service\CurlPost;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderRepository extends AbstractRepository
{
    use CurlPost;
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
        if (empty($data)) {
            return 0;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new DtOrder();
        $object->setCustomerCode($data['customer_code'] ?? '');
        $object->setSeikyuCode($data['seikyu_code'] ?? '');
        $object->setOrderNo($data['order_no'] ?? '');
        $object->setOrderLineno($data['order_line_no'] ?? '');
        $object->setShippingCode($data['shipping_code'] ?? '');
        $object->setOtodokeCode($data['otodoke_code'] ?? '');
        $object->setOrderDate(new \DateTime($data['order_date'] ?? ''));
        $object->setDeliPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
        $object->setShipingPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
        $object->setItemNo($data['jan_code'] ?? '');
        $object->setDemandQuantity($data['demand_quantity']);
        $object->setDemandUnit($data['demand_unit']);
        $object->setOrderPrice((float) $data['order_price']);
        $object->setUnitPriceStatus('FOR');
        $object->setShipingDepositCode($data['location']);
        $object->setDeploy('XB');
        $object->setCompanyId('XB');
        $object->setProductCode($data['product_code'] ?? '');
        $object->setDynaModelSeg2($data['order_no'] ?? '');
        $object->setDynaModelSeg3('2');
        $object->setDynaModelSeg4($data['dtb_order_no'] ?? '');
        $object->setDynaModelSeg5($data['dtb_order_line_no'] ?? '');
        $object->setDynaModelSeg6($data['remarks_line_no'] ?? null);
        $object->setRequestFlg('Y');
        $object->setFvehicleno($data['fvehicleno']);
        $object->setFtrnsportcd($data['ftrnsportcd']);

        log_info('Call insertData to dt_order '.$object->getOrderNo().'-'.$object->getOrderLineno());

        return $this->Execute($object);
    }

    private function Execute($object)
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();

        if (!empty($object->getCreateDate())) {
            return 1;
        } else {
            $message = 'Import data dt_order '.$object->getOrderNo().'-'.$object->getOrderLineno().' error';
            $message .= "\nProcess execute again";
            log_error($message);
            $this->pushGoogleChat($message);

            return $this->Execute($object);
        }
    }
}
