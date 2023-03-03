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

use Customize\Entity\Order;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Eccube\Repository\QueryKey;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    public const COLUMNS = [
        'order' => 'o.name01', 'orderer' => 'o.id', 'shipping_id' => 's.id', 'purchase_product' => 'oi.product_name', 'quantity' => 'oi.quantity', 'payment_method' => 'o.payment_method', 'order_status' => 'o.OrderStatus', 'purchase_price' => 'o.total', 'shipping_status' => 's.shipping_date', 'tracking_number' => 's.tracking_number', 'delivery' => 's.name01',
    ];

    /**
     * OrderRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     */
    public function __construct(RegistryInterface $registry, Queries $queries)
    {
        parent::__construct($registry, Order::class);
        $this->queries = $queries;
    }

    /**
     * @param  \Eccube\Entity\Customer $Customer
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer(Customer $Customer)
    {
        //ordStatus.update_date,
        $qb = $this->createQueryBuilder('o')
            ->select('ordStatus.ec_order_no,ordStatus.order_line_no,ordStatus.ec_order_lineno,o.order_status_id,i.product_id,i.product_name,mstp.product_code,
            ordStatus.order_status,ordStatus.reserve_stock_num,ordStatus.update_date,ordStatus.order_remain_num,mstShip.shipping_status,mstShip.inquiry_no,mstShip.shipping_date')
           // ->leftJoin('Customize\Entity\MstShipping', 'mstShip',Join::WITH,'mstShip.ec_order_no=o.order_no')
            ->innerJoin('Customize\Entity\OrderItem', 'i', Join::WITH, 'i.order_id=o.id')
            ->innerJoin('Customize\Entity\Product', 'p', Join::WITH, 'i.product_id=p.id')
            ->innerJoin('Customize\Entity\MstProduct', 'mstp', Join::WITH, 'mstp.ec_product_id=i.product_id')
            ->innerJoin('Customize\Entity\DtOrderStatus', 'ordStatus', Join::WITH, 'ordStatus.ec_order_no=o.order_no and ordStatus.ec_order_lineno=i.id')
            ->leftJoin('Customize\Entity\MstShipping', 'mstShip', Join::WITH, 'mstShip.ec_order_no=o.order_no and mstShip.ec_order_lineno=i.id')
            ->where('o.Customer = :Customer')
            ->setParameter('Customer', $Customer);

        // Order By
        $qb->addOrderBy('o.id', 'DESC');
        // dump( $qb->getQuery()->getSQL() ); die;

        return $this->queries->customize(QueryKey::ORDER_SEARCH_BY_CUSTOMER, $qb, ['customer' => $Customer]);
    }
}
