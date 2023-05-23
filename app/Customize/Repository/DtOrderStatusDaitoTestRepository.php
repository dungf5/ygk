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
use Customize\Entity\DtOrderStatusDaitoTest;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderStatusDaitoTestRepository extends AbstractRepository
{
    use CurlPost;
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderStatusDaitoTest::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return 0;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new DtOrderStatusDaitoTest();
//        $object->setOrderNo('');
//        $object->setOrderLineNo('0');
//        $object->setOrderStatus(1);
//        $object->setOrderStatus(3);
        $object->setCusOrderNo($data['order_no'] ?? '');
        $object->setCusOrderLineno($data['order_line_no'] ?? '');
//        $object->setEcOrderNo($data['dtb_order_no'] ?? '');
//        $object->setEcOrderLineno($data['dtb_order_line_no'] ?? '');
//        $object->setCustomerCode('7001');
//        $object->setShippingCode($data['shipping_code'] ?? '');
//        $object->setOtodokeCode($data['otodoke_code'] ?? '');
//        $object->setProductCode($data['product_code'] ?? '');
//        $object->setOrderRemainNum((int) $data['order_num']);
//        $object->setFlowType('2');
//        $object->setEcType('2');
//        $object->setOrderDate(new \DateTime($data['order_date'] ?? ''));
        log_info('Call insertData to dt_order_status '.$object->getCusOrderNo().'-'.$object->getCusOrderLineno());
        return $this->Execute($object, 1);

//        log_info('Call insertData to dt_order_status '.$data['order_no'].'-'.$data['order_line_no']);
//
//        try {
//            $myCommonService = new MyCommonService($this->getEntityManager());
//            $myCommonService->insertDtOrderStatusByQuery($data);
//
//            return 1;
//        } catch (\Exception $e) {
//            $message = 'Import data dt_order_status '.$data['order_no'].'-'.$data['order_line_no'].' error';
//            $message .= "\n".$e->getMessage();
//            log_error($message);
//            $this->pushGoogleChat($message);
//
//            return 0;
//        }
    }

    private function Execute($object, $count)
    {
        try {
            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->pushGoogleChat('Loi loi loi: '.$e->getMessage());
        }

        if (!empty($object->getCreateDate())) {
            return 1;
        } else {
            $message = 'Import data dt_order_status '.$object->getCusOrderNo().'-'.$object->getCusOrderLineno().' error';
            $message .= "\nProcess execute again";
            log_error($message);
            $this->pushGoogleChat($message);

            $count++;

            if ($count > 5) {
                return 0;
            }

            return $this->Execute($object, $count);
        }
    }
}
