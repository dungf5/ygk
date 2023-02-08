<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;
use Customize\Service\Common\MyCommonService;

class MstShippingRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstShipping::class);
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer($customer_code = '', $login_type = '')
    {
        $qb = $this->createQueryBuilder('shipping');
        $qb->select('shipping.shipping_no', 'shipping.customer_code', 'shipping.shipping_status', 'shipping.shipping_plan_date', 'shipping.shipping_date', 'shipping.shipping_num', 'shipping.order_lineno', 'shipping.cus_order_no', 'shipping.cus_order_lineno');

        $qb->leftJoin(
                '\Customize\Entity\MstCustomer',
                'customer',
                Join::WITH,
                'customer.customer_code = shipping.customer_code');
        $qb->leftJoin(
                '\Customize\Entity\MstProduct',
                'product',
                Join::WITH,
                'product.product_code = shipping.product_code');
        $qb->leftJoin(
                '\Customize\Entity\MstDelivery',
                'delivery',
                Join::WITH,
                'delivery.shipping_no = shipping.shipping_no');

        $qb->where('shipping.delete_flg = 0')
            ->andWhere('shipping.shipping_date >= :shipping_date')
            ->setParameter('shipping_date', date("Y-m-d", strtotime("-14 MONTH")));
        $qb->andWhere('shipping.customer_code = :customer_code')
            ->setParameter('customer_code', $customer_code);

        $qb->addSelect('customer.customer_name', 'customer.company_name', 'product.jan_code', 'product.product_name', 'delivery.delivery_no');

        $qb->addGroupBy('shipping.order_no');
        $qb->addGroupBy('shipping.order_lineno');
        
        $qb->addOrderBy('shipping.shipping_date', 'DESC');

        // echo($qb->getQuery()->getSQL());
        // var_dump($qb->getParameters());
        // die();
        return $qb;
    }
}

