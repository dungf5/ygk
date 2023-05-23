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

        // dt_order
        $objOrder = new DtOrderDaitoTest();
        $objOrder->setCustomerCode($data['customer_code'] ?? '');
        $objOrder->setSeikyuCode($data['seikyu_code'] ?? '');
        $objOrder->setOrderNo($data['order_no'] ?? '');
        $objOrder->setOrderLineno($data['order_line_no'] ?? 0);
        $objOrder->setShippingCode($data['shipping_code'] ?? '');
        $objOrder->setOtodokeCode($data['otodoke_code'] ?? '');
        $objOrder->setOrderDate(new \DateTime($data['order_date'] ?? ''));
        $objOrder->setDeliPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
        $objOrder->setShipingPlanDate($data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : '');
        $objOrder->setItemNo($data['jan_code'] ?? '');
        $objOrder->setDemandQuantity($data['demand_quantity'] ?? 0);
        $objOrder->setDemandUnit($data['demand_unit'] ?? '');
        $objOrder->setOrderPrice((float) $data['order_price'] ?? 0);
        $objOrder->setUnitPriceStatus('FOR');
        $objOrder->setShipingDepositCode($data['location'] ?? '');
        $objOrder->setDeploy('XB');
        $objOrder->setCompanyId('XB');
        $objOrder->setProductCode($data['product_code'] ?? '');
        $objOrder->setDynaModelSeg2($data['order_no'] ?? '');
        $objOrder->setDynaModelSeg3('2');
//        $objOrder->setDynaModelSeg4($data['dtb_order_no'] ?? '');
        $objOrder->setDynaModelSeg4(null);
//        $objOrder->setDynaModelSeg5($data['dtb_order_line_no'] ?? 0);
        $objOrder->setDynaModelSeg5(0);
        $objOrder->setDynaModelSeg6($data['remarks_line_no'] ?? null);
        $objOrder->setRequestFlg('Y');
        $objOrder->setFvehicleno($data['fvehicleno'] ?? '');
        $objOrder->setFtrnsportcd($data['ftrnsportcd'] ?? '');

        log_info('Call insertData to dt_order '.$objOrder->getOrderNo().'-'.$objOrder->getOrderLineno());

        // dt_order_status
        $objOrderStatus = new DtOrderStatusDaitoTest();
        $objOrderStatus->setOrderNo('');
        $objOrderStatus->setOrderLineNo('0');
        $objOrderStatus->setOrderStatus(1);
        $objOrderStatus->setCusOrderNo($data['order_no'] ?? '');
        $objOrderStatus->setCusOrderLineno($data['order_line_no'] ?? 0);
//        $objOrderStatus->setEcOrderNo($data['dtb_order_no'] ?? '');
        $objOrderStatus->setEcOrderNo(null);
//        $objOrderStatus->setEcOrderLineno($data['dtb_order_line_no'] ?? 0);
        $objOrderStatus->setEcOrderLineno(0);
        $objOrderStatus->setCustomerCode('7001');
        $objOrderStatus->setShippingCode($data['shipping_code'] ?? '');
        $objOrderStatus->setOtodokeCode($data['otodoke_code'] ?? '');
        $objOrderStatus->setProductCode($data['product_code'] ?? '');
        $objOrderStatus->setOrderRemainNum((int) $data['order_num'] ?? 0);
        $objOrderStatus->setFlowType('2');
        $objOrderStatus->setEcType('2');
        $objOrderStatus->setOrderDate(new \DateTime($data['order_date'] ?? ''));

        log_info('Call insertData to dt_order_status '.$objOrderStatus->getCusOrderNo().'-'.$objOrderStatus->getCusOrderLineno());

        return $this->Execute($objOrder, $objOrderStatus);

//        log_info('Call insertData to dt_order '.$data['order_no'].'-'.$data['order_line_no']);
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

    private function Execute($objOrder, $objOrderStatus)
    {
        $this->getEntityManager()->persist($objOrder);
        //$this->getEntityManager()->persist($objOrderStatus);
        $this->getEntityManager()->flush();

        if (!empty($objOrder->getCreateDate()) && !empty($objOrderStatus->getCreateDate())) {
            return 1;
        } else {
            $message = '';
            if (empty($objOrder->getCreateDate())) {
                $message = 'Import data dt_order '.$objOrder->getOrderNo().'-'.$objOrder->getOrderLineno().' error';
            } elseif (empty($objOrderStatus->getCreateDate())) {
                $message = 'Import data dt_order_status '.$objOrderStatus->getCusOrderNo().'-'.$objOrderStatus->getCusOrderLineno().' error';
            }

            $message .= "\nProcess execute again";
            log_error($message);
            $this->pushGoogleChat($message);

//            $count++;
//
//            if ($count > 5) {
//                return 0;
//            }

            //return $this->Execute($objOrder, $objOrderStatus, $count);

            return 0;
        }
    }
}
