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
use Customize\Entity\NatStockList;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class NatStockListRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NatStockList::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return;
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $object = new NatStockList();
        $object->setJan($data['jan']);
        $object->setNatStockNum($data['nat_stock_num']);
        $object->setOrderLot($data['order_lot']);
        $object->setUnitPrice($data['unit_price']);
        $object->setPriceS01($data['price_s01']);

        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();

        return;
    }
}
