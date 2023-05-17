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
use Customize\Entity\DtOrderStatus;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderStatusRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderStatus::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return 0;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new DtOrderStatus();
        $object->setOrderNo('');
        $object->setOrderLineNo('0');
        $object->setOrderStatus(1);
        $object->setCusOrderNo($data['order_no'] ?? '');
        $object->setCusOrderLineno($data['order_line_no'] ?? '');
        $object->setEcOrderNo($data['dtb_order_no'] ?? '');
        $object->setEcOrderLineno($data['dtb_order_line_no'] ?? '');
        $object->setCustomerCode('7001');
        $object->setShippingCode($data['shipping_code'] ?? '');
        $object->setOtodokeCode($data['otodoke_code'] ?? '');
        $object->setProductCode($data['product_code'] ?? '');
        $object->setOrderRemainNum((int) $data['order_num']);
        $object->setFlowType('2');
        $object->setEcType('2');
        $object->setOrderDate(new \DateTime($data['order_date'] ?? ''));

        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();

        return 1;
    }
}
