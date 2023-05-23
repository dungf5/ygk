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
use Customize\Entity\DtOrderDaitoTest;
use Customize\Entity\DtOrderStatusDaitoTest;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderDaitoTestRepository extends AbstractRepository
{
    use CurlPost;

    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderDaitoTest::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return 0;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new DtOrderDaitoTest();
        $object->setCustomerCode($data['customer_code'] ?? '');
        $object->setSeikyuCode($data['customer_code'] ?? '');
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
        $object->setFtrnsportcd('87001');

        log_info('Call insertData to dt_order '.$data['order_no'].'-'.$data['order_line_no']);

        $object2 = new DtOrderStatusDaitoTest();
        $object2->setOrderNo('');
        $object2->setOrderLineNo('0');
        $object2->setOrderStatus(1);
        $object2->setCusOrderNo($data['order_no'] ?? '');
        $object2->setCusOrderLineno($data['order_line_no'] ?? '');
        $object2->setEcOrderNo($data['dtb_order_no'] ?? '');
        $object2->setEcOrderLineno($data['dtb_order_line_no'] ?? '');
        $object2->setCustomerCode('7001');
        $object2->setShippingCode($data['shipping_code'] ?? '');
        $object2->setOtodokeCode($data['otodoke_code'] ?? '');
        $object2->setProductCode($data['product_code'] ?? '');
        $object2->setOrderRemainNum((int) $data['order_num']);
        $object2->setFlowType('2');
        $object2->setEcType('2');
        $object2->setOrderDate(new \DateTime($data['order_date'] ?? ''));
        log_info('Call insertData to dt_order_status '.$object->getCusOrderNo().'-'.$object->getCusOrderLineno());
        return $this->Execute($object, $object2, 1);


//
//        try {
//            $myCommonService = new MyCommonService($this->getEntityManager());
//            $myCommonService->insertDtOrderByQuery($data);
//
//            return 1;
//        } catch (\Exception $e) {
//            $message = 'Import data dt_order '.$data['order_no'].'-'.$data['order_line_no'].' error';
//            $message .= "\n".$e->getMessage();
//            log_error($message);
//            $this->pushGoogleChat($message);
//
//            return 0;
//        }
    }

    private function Execute($object, $object2, $count)
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->persist($object2);
        $this->getEntityManager()->flush();

        if (!empty($object->getCreateDate()) && !empty($object2->getCreateDate())) {
            return 1;
        } else {
            $message = 'Import data dt_order '.$object->getOrderNo().'-'.$object->getOrderLineno().' error';
            $message .= "\nProcess execute again";
            log_error($message);
            $this->pushGoogleChat($message);

            $count++;

            if ($count > 5) {
                return 0;
            }

            return $this->Execute($object, $object2, $count);
        }
    }
}
