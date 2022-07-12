<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Query\Mysql\Cast;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\OrderItem;
use Eccube\Repository\QueryKey;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OrderItemRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     */
    public function __construct(RegistryInterface $registry, Queries $queries)
    {
        parent::__construct($registry, OrderItem::class);
        $this->queries = $queries;
    }
    /**
     *
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer($customerCode)
    {

        //ordStatus.update_date,
        $qb = $this->getEntityManager()->createQueryBuilder();
        //$qb = $this->createQueryBuilder('i');
        $qb = $qb
            ->select('ordStatus.ec_order_no,ordStatus.order_line_no,ordStatus.ec_order_lineno,ordStatus.order_status as order_status_id,mstp.ec_product_id as product_id ,mstp.product_name,mstp.product_code,
            ordStatus.order_status,ordStatus.reserve_stock_num,ordStatus.update_date,ordStatus.order_remain_num,mstShip.shipping_status,mstShip.inquiry_no,mstShip.shipping_date,mstShip.shipping_no,mstShip.shipping_no as t1')
            ->from('Customize\Entity\DtOrderStatus', 'ordStatus')
            ->innerJoin('Customize\Entity\MstProduct', 'mstp', Join::WITH, 'ordStatus.product_code=mstp.product_code')
            ->leftJoin('Customize\Entity\MstShipping', 'mstShip', Join::WITH, 'mstShip.order_no=ordStatus.order_no and mstShip.order_lineno=ordStatus.order_line_no')
            ->where('ordStatus.customer_code  = :customerCode')
            ->setParameter('customerCode', $customerCode);

        // Order By
        $qb->addOrderBy('ordStatus.ec_order_no', 'DESC');
        $qb->addOrderBy('ordStatus.order_line_no ', 'asc');

     //  var_dump( $qb->getQuery()->getSQL(),$customerCode);die();

        return $qb;//$this->queries->customize("", $qb, []);
    }


}
