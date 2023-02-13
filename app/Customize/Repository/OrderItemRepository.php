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
    public function getQueryBuilderByCustomer($paramSearch = [], $customerCode, $loginType = null)
    {
        if ($loginType == "represent_code" || $loginType == "customer_code" || $loginType == "change_type") {
            $condition      = ' and ordStatus.customer_code  = :customerCode ';
        }

        elseif ($loginType == "shipping_code") {
            $condition      = ' and ordStatus.shipping_code  = :customerCode ';
        }

        elseif ($loginType == "otodoke_code") {
            $condition      = ' and ordStatus.otodoke_code  = :customerCode ';
        }

        else {
            $condition      = ' and ordStatus.customer_code  = :customerCode ';
        }

        $col        = "
            ordStatus.ec_type,
            ordStatus.order_line_no,
            ordStatus.cus_order_no,
            ordStatus.cus_order_lineno,
            ordStatus.ec_order_no,
            ordStatus.ec_order_lineno,
            ordStatus.order_date,
            ordStatus.order_status,
            ordStatus.remarks1,
            ordStatus.remarks2,
            ordStatus.update_date,
            ordStatus.order_remain_num,
            ordStatus.reserve_stock_num,
            mstp.jan_code,
            mstp.ec_product_id as product_id ,
            mstp.product_name,
            mstp.product_code,
            mstp.quantity,
            mstShip.shipping_status,
            mstShip.inquiry_no,
            mstShip.shipping_date,
            mstShip.shipping_no
        ";

        $qb         = $this->getEntityManager()->createQueryBuilder();
        $where      = " ordStatus.order_date >= :orderDate {$condition} AND mstShip.delete_flg <> 0";

        // Add condition
        if ( $paramSearch['search_order_status'] != '') {
            $where .= ' AND ordStatus.order_status  = :orderStatus ';
        }

        if ( $paramSearch['search_order_date'] != 0 ) {
            $where .= ' AND ordStatus.order_date like :search_order_date ';
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $where .= ' AND ordStatus.shipping_code  = :orderShipping ';
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $where .= ' AND ordStatus.otodoke_code  = :orderOtodoke ';
        }
        // End - Add condition

        $qb = $qb->select($col)
            ->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = ordStatus.shipping_code) shipping_name')
            ->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = ordStatus.otodoke_code) otodoke_name')
            ->from('Customize\Entity\DtOrderStatus', 'ordStatus')
            ->innerJoin(
                'Customize\Entity\MstProduct',
                'mstp',
                Join::WITH,
                'ordStatus.product_code = mstp.product_code'
            )
            ->leftJoin(
                'Customize\Entity\MstShipping',
                'mstShip',
                Join::WITH,
                'mstShip.cus_order_no = ordStatus.cus_order_no
                and mstShip.cus_order_lineno = ordStatus.cus_order_lineno'
            )
            ->where($where)
            ->setParameter(':customerCode', $customerCode)
            ->setParameter(':orderDate', Date("Y-m-d", strtotime("- 14 months")));

        /*Set param search */
        if ( $paramSearch['search_order_status'] != '' ) {
            $qb = $qb->setParameter(':orderStatus', $paramSearch['search_order_status']);
        }

        if ( $paramSearch['search_order_date'] != 0 ) {
            $qb = $qb->setParameter(':search_order_date', $paramSearch['search_order_date']."-%");
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $qb = $qb->setParameter(':orderShipping', $paramSearch['search_order_shipping']);
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $qb = $qb->setParameter(':orderOtodoke', $paramSearch['search_order_otodoke']);
        }
        /*End - Set param search */

        //group
        $qb->addGroupBy('ordStatus.order_no');
        $qb->addGroupBy('ordStatus.order_line_no');

        // Order By
        $qb->addOrderBy('ordStatus.order_date', 'DESC');
        $qb->addOrderBy('ordStatus.cus_order_no', 'DESC');
        $qb->addOrderBy('ordStatus.cus_order_lineno', 'asc');

        //dd( $qb->getQuery()->getSQL(), $customerCode);
        //$this->queries->customize("", $qb, []);

        return $qb;
    }

    /**
     *
     *
     * @return QueryBuilder
     */
    public function getDeliveryByCustomer($paramSearch = [], $customerCode, $loginType = null)
    {
        if ($loginType == "represent_code" || $loginType == "customer_code" || $loginType == "change_type") {
            $condition      = ' and dos.customer_code  = :customerCode ';
        }

        elseif ($loginType == "shipping_code") {
            $condition      = ' and dos.shipping_code  = :customerCode ';
        }

        elseif ($loginType == "otodoke_code") {
            $condition      = ' and dos.otodoke_code  = :customerCode ';
        }

        else {
            $condition      = ' and dos.customer_code  = :customerCode ';
        }

        $col        = "
                        md.delivery_no,
                        md.delivery_lineno,
                        ms.shipping_date,
                        md.shiping_name,
                        md.otodoke_name,
                        ms.shipping_no
                    ";

        $qb         = $this->getEntityManager()->createQueryBuilder();
        $where      = " ms.delete_flg <> 0 AND ms.shipping_date >= :shippingDate {$condition}";

        // Add condition
        if (!empty($paramSearch['delivery_no'])) {
            $where .= ' AND md.delivery_no = :deliveryNo ';
        }

        if ( $paramSearch['search_shipping_date'] != 0 ) {
            $where .= ' AND  ms.shipping_date  like :shippingDate ';
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $where .= ' AND md.shiping_name = (select mc3.company_name from Customize\Entity\MstCustomer mc3 where mc3.customer_code = :orderShipping) ';
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $where .= ' AND md.otodoke_name in (select mc4.company_name from Customize\Entity\MstCustomer mc4 where mc4.customer_code = :orderOtodoke) ';
        }
        // End - Add condition

        $qb = $qb->select($col)
            ->from('Customize\Entity\DtOrderStatus', 'dos')
            ->innerJoin(
                'Customize\Entity\MstShipping',
                'ms',
                Join::WITH,
                'ms.cus_order_no = dos.cus_order_no and
		        ms.cus_order_lineno = dos.cus_order_lineno'
            )
            ->leftJoin(
                'Customize\Entity\MstDelivery',
                'md',
                Join::WITH,
                "md.shipping_no = ms.shipping_no"
            )
            ->where($where)
            ->setParameter(':customerCode', $customerCode)
            ->setParameter(':shippingDate', Date("Y-m-d", strtotime("- 14 months")));

        /*Set param search */
        if (!empty($paramSearch['delivery_no'])) {
            $qb = $qb->setParameter(':deliveryNo', $paramSearch['delivery_no']);
        }

        if ( $paramSearch['search_shipping_date'] != 0 ) {
            $qb = $qb->setParameter(':shippingDate', $paramSearch['search_shipping_date']."-%");
        }

        if ( $paramSearch['search_order_shipping'] != 0 ) {
            $qb = $qb->setParameter(':orderShipping', $paramSearch['search_order_shipping']);
        }

        if ( $paramSearch['search_order_otodoke'] != 0 ) {
            $qb = $qb->setParameter(':orderOtodoke', $paramSearch['search_order_otodoke']);
        }
        /*End - Set param search */

        //group
        $qb->addGroupBy('dos.order_no');
        $qb->addGroupBy('dos.order_line_no');

        // Order By
        $qb->addOrderBy('ms.shipping_date', 'DESC');

        //dd( $qb->getQuery()->getSQL(), $paramSearch, $customerCode);
        //$this->queries->customize("", $qb, []);

        return $qb;
    }
}
