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
    public function getQueryBuilderByCustomer($paramSearch = [], $customerCode, $shippingCode = '', $otodokeCode = '', $loginType = null)
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
        $where      = " ordStatus.order_date >= :orderDate {$condition}";

        // Add condition
        if (!empty($shippingCode)) {
            $where .= ' AND ordStatus.shipping_code  = :shippingCode ';
        }

        if (!empty($otodokeCode)) {
            $where .= ' AND ordStatus.otodoke_code  = :otodokeCode ';
        }

        if (isset($paramSearch['search_status_type'])) {
            if (in_array((int)$paramSearch['search_status_type'], [0, 1, 2, 3, 4, 9])) {
                $where .= ' AND ordStatus.order_status  in (:orderStatus) ';
            }
        }

        if (!empty($paramSearch['search_order_date'])) {
            $where .= ' AND (';

            foreach ($paramSearch['search_order_date'] as $key => $value) {
                $where .= ' ordStatus.order_date  like :orderDate'.$key.' OR';
            }

            $where  = trim($where, 'OR');
            $where .= ' ) ';
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
        if (!empty($shippingCode)) {
            $qb = $qb->setParameter(':shippingCode', "$shippingCode");
        }

        if (!empty($otodokeCode)) {
            $qb = $qb->setParameter(':otodokeCode', "$otodokeCode");
        }

        if (isset($paramSearch['search_status_type'])) {
            if ((int)$paramSearch['search_status_type'] == 0) {
                $qb = $qb->setParameter(':orderStatus', [0]);
            }
            if ((int)$paramSearch['search_status_type'] == 1) {
                $qb = $qb->setParameter(':orderStatus', [1]);
            }
            if ((int)$paramSearch['search_status_type'] == 2) {
                $qb = $qb->setParameter(':orderStatus', [2]);
            }
            if ((int)$paramSearch['search_status_type'] == 3) {
                $qb = $qb->setParameter(':orderStatus', [3]);
            }
            if ((int)$paramSearch['search_status_type'] == 4) {
                $qb = $qb->setParameter(':orderStatus', [4]);
            }
            if ((int)$paramSearch['search_status_type'] == 9) {
                $qb = $qb->setParameter(':orderStatus', [9]);
            }
        }

        if (!empty($paramSearch['search_order_date'])) {
            foreach ($paramSearch['search_order_date'] as $key => $value) {
                $qb = $qb->setParameter(':orderDate' . $key, $value."-%");
            }
        }
        /*End - Set param search */

        //group
        $qb->addGroupBy('ordStatus.order_no');
        $qb->addGroupBy('ordStatus.order_line_no');

        // Order By
        $qb->addOrderBy('ordStatus.order_date', 'DESC');
        $qb->addOrderBy('ordStatus.cus_order_no', 'DESC');
        $qb->addOrderBy('ordStatus.cus_order_lineno', 'asc');

        //dd( $qb->getQuery()->getSQL(), $customerCode, $shippingCode, $otodokeCode);
        //$this->queries->customize("", $qb, []);

        return $qb;
    }
}
