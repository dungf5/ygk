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
    public function getQueryBuilderByCustomer($search_parameter=[], $customer_code='', $login_type='')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('customer.customer_code');
        $qb->from('Customize\Entity\MstCustomer', 'customer');
        $qb->innerJoin(
            'Customize\Entity\DtCustomerRelation',
            'customer_relation',
            Join::WITH,
            "customer.customer_code = ( CASE
                WHEN( LEFT( customer_relation.represent_code, 1 ) = 't' ) THEN customer_relation.otodoke_code
                WHEN( LEFT( customer_relation.represent_code, 1 ) = 's' ) THEN customer_relation.shipping_code
                ELSE customer_relation.customer_code 
            END )"
        );
        $qb->innerJoin(
            'Customize\Entity\DtOrderStatus',
            'order_status',
            Join::WITH,
            "( LEFT( customer_relation.represent_code, 1 ) = 't' AND customer_relation.otodoke_code = order_status.otodoke_code )
            OR ( LEFT( customer_relation.represent_code, 1 ) = 's' AND customer_relation.shipping_code = order_status.shipping_code )
            OR ( customer_relation.customer_code = order_status.customer_code )"
        );
        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            "product.product_code = order_status.product_code"
        );
        $qb->leftJoin(
            'Customize\Entity\MstShipping',
            'shipping',
            Join::WITH,
            "shipping.cus_order_no = order_status.cus_order_no AND shipping.cus_order_lineno = order_status.cus_order_lineno"
        );
        $qb->leftJoin(
            'Customize\Entity\MstDelivery',
            'delivery',
            Join::WITH,
            'delivery.shipping_no = shipping.shipping_no'
        );

        $qb->addSelect('shipping.shipping_no', 'shipping.customer_code', 'shipping.shipping_status', 'shipping.shipping_plan_date', 'shipping.shipping_date', 'shipping.shipping_num', 'shipping.order_no', 'shipping.order_lineno', 'shipping.cus_order_no', 'shipping.cus_order_lineno', 'shipping.ec_order_no', 'shipping.ec_order_lineno');
        $qb->where('shipping.delete_flg <> 0')
            ->andWhere('shipping.shipping_date >= :shipping_date')
            ->setParameter('shipping_date', date("Y-m-d", strtotime("-14 MONTH")));

        switch( $search_parameter['shipping_status'] ) {
            case 1:
                $qb->andWhere('shipping.shipping_status = :shipping_status')
                    ->setParameter('shipping_status', 1);
                break;
            case 2:
                $qb->andWhere('shipping.shipping_status = :shipping_status')
                    ->setParameter('shipping_status', 2);
                break;
        }
        if( $search_parameter['order_shipping'] > 0 ) {
            $qb->andWhere('order_status.shipping_code = :shipping_code')
                ->setParameter('shipping_code', $search_parameter['order_shipping']);
        }

        if( $search_parameter['order_otodoke'] > 0 ) {
            $qb->andWhere('order_status.otodoke_code = :order_otodoke')
                ->setParameter('order_otodoke', $search_parameter['order_otodoke']);
        }

        $qb->addSelect('product.jan_code', 'product.product_name', 'delivery.delivery_no');
        $qb->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = order_status.shipping_code) shipping_name');
        $qb->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = order_status.otodoke_code) otodoke_name');

        $qb->addGroupBy('shipping.order_no');
        $qb->addGroupBy('shipping.order_lineno');
        
        $qb->addOrderBy('shipping.shipping_date', 'DESC');
        
        // echo($qb->getQuery()->getSQL());
        // var_dump($qb->getParameters());
        // die();
        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getAllCustomer($customer_code='', $login_type='')
    {
        $qb = $this->createQueryBuilder('shipping');
        $qb->select('customer.customer_code', 'customer.customer_name', 'customer.company_name');

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

        $qb->addGroupBy('customer.customer_code');
        
        $qb->addOrderBy('shipping.shipping_date', 'DESC');

        // echo($qb->getQuery()->getSQL());
        // var_dump($qb->getParameters());
        // die();
        return $qb->getQuery()->getResult();
    }
}

