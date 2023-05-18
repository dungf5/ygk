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
use Customize\Entity\MstShippingNatEOS;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstShippingNatEOSRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstShippingNatEOS::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return 0;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new MstShippingNatEOS();
        /* fixed value */
        $object->setShippingSendFlg('1');
        $object->setShippingSentFlg('0');
        /* End - fixed value */

        $object->setDeliveryNo($data['delivery_no']);
        $object->setJan($data['jan']);
        $object->setMkrcd($data['mkrcd']);
        $object->setNatcd($data['natcd']);
        $object->setDeliveryDay($data['shipping_date']);
        $object->setShippingNo($data['shipping_no']);
        $object->setReqcd($data['reqcd']);
        $object->setOrderLineno($data['order_lineno']);

        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();

        return 1;
    }
}
