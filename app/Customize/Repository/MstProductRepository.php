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

use Customize\Entity\MstProduct;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstProductRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstProduct::class);
    }

    /***
     * @param string $ec_product_id
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getData($ec_product_id = '')
    {
        $curentDateTime = date('Y-m-d H:i:s');
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.ec_product_id = :ec_product_id')
            ->andWhere("DATE_FORMAT(IFNULL(p.discontinued_date, '9999-12-31 00:00:00'), '%Y-%m-%d %H:%i:%s') >= DATE_FORMAT(:curent_date_time, '%Y-%m-%d %H:%i:%s')")
            ->setParameter(':ec_product_id', $ec_product_id)
            ->setParameter(':curent_date_time', $curentDateTime);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
