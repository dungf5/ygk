<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

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

        $qb->where('shipping.customer_code = :customer_code')
            ->setParameter('customer_code', $customer_code);
        $qb->addGroupBy('shipping.shipping_no');
        $qb->addSelect('shipping.shipping_no', 'customer.customer_name', 'customer.company_name', 'product.jan_code', 'product.product_name', 'shipping.shipping_status', 'shipping.shipping_plan_date', 'shipping.shipping_date', 'shipping.shipping_num', 'shipping.order_lineno', 'shipping.cus_order_no', 'shipping.cus_order_lineno');

        // echo($qb->getQuery()->getSQL());
        // var_dump($qb->getParameters());
        // die();
        return $qb;
    }
}
