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
    public function getQueryBuilderByCustomer($paramSearch = [], $order_status=[])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('shipping.shipping_no');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');
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

        $qb->addSelect(
            'order_status.ec_type',
            'order_status.order_line_no',
            'order_status.cus_order_no',
            'order_status.cus_order_lineno',
            'order_status.ec_order_no', 
            'order_status.ec_order_lineno', 
            'order_status.order_date', 
            'order_status.order_status', 
            'order_status.remarks1', 
            'order_status.remarks2', 
            'order_status.update_date', 
            'order_status.order_remain_num', 
            'order_status.reserve_stock_num', 
            'product.jan_code', 
            'product.ec_product_id as product_id', 
            'product.product_name', 
            'product.product_code', 
            'product.quantity', 
            'shipping.shipping_status', 
            'shipping.inquiry_no', 
            'shipping.shipping_date', 
            'shipping.shipping_no'
        );
        $qb->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = order_status.shipping_code) shipping_name');
        $qb->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = order_status.otodoke_code) otodoke_name');
        $qb->where('shipping.delete_flg IS NULL OR shipping.delete_flg <> 0')
            ->andWhere('order_status.order_date >= :shipping_date')
            ->setParameter('shipping_date', date("Y-m-d", strtotime("-14 MONTH")));
        if( count($order_status) > 0 ) {
            $where = '';
            foreach($order_status as $k=>$os ) {
                if( ! empty($where) ) $where .= ' OR ';
                $where .= " ( order_status.cus_order_no = :order_status_cus_order_no_{$k} AND order_status.cus_order_lineno = :order_status_cus_order_lineno_{$k} ) ";
                $qb->setParameter("order_status_cus_order_no_{$k}", $os['cus_order_no']);
                $qb->setParameter("order_status_cus_order_lineno_{$k}", $os['cus_order_lineno']);
            }
            $qb->andWhere( $where );
        }

        if ( $paramSearch['search_order_status'] != '' ) {
            $qb->andWhere( 'order_status.order_status  = :search_order_status' )
                ->setParameter(':search_order_status', $paramSearch['search_order_status']);
        }

        if ( $paramSearch['search_order_date'] != 0 ) {
            $qb->andWhere( 'order_status.order_date like :search_order_date' )
                ->setParameter(':search_order_date', $paramSearch['search_order_date']."-%");
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $qb->andWhere( 'order_status.shipping_code  = :search_order_shipping' )
                ->setParameter(':search_order_shipping', $paramSearch['search_order_shipping']);
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $qb->andWhere( 'order_status.otodoke_code  = :search_order_otodoke ' )
                ->setParameter(':search_order_otodoke', $paramSearch['search_order_otodoke']);
        }

        //group
        $qb->addGroupBy('order_status.order_no');
        $qb->addGroupBy('order_status.order_line_no');

        // Order By
        $qb->addOrderBy('order_status.order_date', 'DESC');
        $qb->addOrderBy('order_status.cus_order_no', 'DESC');
        $qb->addOrderBy('order_status.cus_order_lineno', 'asc');

        // dump($qb->getQuery()->getSQL());
        // dump($qb->getParameters());
        // die();
        return $qb;
    }

    /**
     *
     *
     * @return QueryBuilder
     */
    public function getDeliveryByCustomer($paramSearch = [], $order_status=[])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('shipping.shipping_no');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');
        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            "product.product_code = order_status.product_code"
        );
        $qb->innerJoin(
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

        $qb->addSelect(
            'delivery.delivery_no',
            'delivery.delivery_lineno',
            'delivery.shiping_name',
            'delivery.otodoke_name',
            'shipping.shipping_date',
            'shipping.shipping_no'
        );

        $qb->where('( shipping.delete_flg IS NOT NULL AND shipping.delete_flg <> 0 )')
            ->andWhere('shipping.shipping_date >= :shipping_date')
            ->setParameter('shipping_date', date("Y-m-d", strtotime("-14 MONTH")));
        if( count($order_status) > 0 ) {
            $where = '';
            foreach($order_status as $k=>$os ) {
                if( ! empty($where) ) $where .= ' OR ';
                $where .= " ( shipping.cus_order_no = :shipping_cus_order_no_{$k} AND shipping.cus_order_lineno = :shipping_cus_order_lineno_{$k} ) ";
                $qb->setParameter("shipping_cus_order_no_{$k}", $os['cus_order_no']);
                $qb->setParameter("shipping_cus_order_lineno_{$k}", $os['cus_order_lineno']);
            }
            $qb->andWhere( $where );
        }
        if (!empty($paramSearch['delivery_no'])) {
            $qb->andWhere( 'delivery.delivery_no = :delivery_no' )
                ->setParameter(':delivery_no', $paramSearch['delivery_no']);
        }

        if ( $paramSearch['search_shipping_date'] != 0 ) {
            $qb->andWhere( 'shipping.shipping_date like :search_shipping_date' )
                ->setParameter(':search_shipping_date', $paramSearch['search_shipping_date']."-%");
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $qb->andWhere( 'delivery.shiping_name = (select mc3.company_name from Customize\Entity\MstCustomer mc3 where mc3.customer_code = :search_order_shipping)' )
                ->setParameter(':search_order_shipping', $paramSearch['search_order_shipping']);
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $qb->andWhere( 'delivery.otodoke_name in (select mc4.company_name from Customize\Entity\MstCustomer mc4 where mc4.customer_code = :search_order_otodoke)' )
                ->setParameter(':search_order_otodoke', $paramSearch['search_order_otodoke']);
        }

        $qb->addGroupBy('delivery.delivery_no');

        $qb->addOrderBy('shipping.shipping_date', 'DESC');
        
        // dump($qb->getQuery()->getSQL());
        // dump($qb->getParameters());
        // die();
        return $qb;
    }
}
