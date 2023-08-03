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

use Customize\Entity\OrderItem;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Eccube\Doctrine\Query\Queries;
use Eccube\Repository\AbstractRepository;
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
     * @param array $paramSearch
     * @param string $customer_code
     * @param string $login_type
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer($paramSearch = [], $customer_code = '', $login_type = '')
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'order_status.shipping_code = :customer_code';
                break;

            case 'otodoke_code':
                $condition = 'order_status.otodoke_code = :customer_code';
                break;

            default:
                $condition = 'order_status.customer_code = :customer_code';
                break;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('shipping.shipping_no');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');
        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            'product.product_code = order_status.product_code'
        );
        $qb->leftJoin(
            'Customize\Entity\MstShipping',
            'shipping',
            Join::WITH,
            'shipping.cus_order_no = order_status.cus_order_no AND shipping.cus_order_lineno = order_status.cus_order_lineno'
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
            'order_status.remarks3',
            'order_status.remarks4',
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
            'shipping.shipping_no',
            'order_status.shipping_num'
        );
        $qb->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = order_status.shipping_code) shipping_name');
        $qb->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = order_status.otodoke_code) otodoke_name');
        $qb->where('shipping.delete_flg IS NULL OR shipping.delete_flg <> 0')
            ->andWhere($condition)
            ->andWhere('order_status.order_date >= :order_date')
            ->setParameter('order_date', date('Y-m-d', strtotime('-14 MONTH')))
            ->setParameter(':customer_code', $customer_code);

        if ($paramSearch['search_order_status'] != '') {
            $qb->andWhere('order_status.order_status  = :search_order_status')
                ->setParameter(':search_order_status', $paramSearch['search_order_status']);
        }

        if ($paramSearch['search_order_date'] != 0) {
            $qb->andWhere('order_status.order_date like :search_order_date')
                ->setParameter(':search_order_date', $paramSearch['search_order_date'].'-%');
        }

        if ($paramSearch['search_order_shipping'] != '0') {
            $qb->andWhere('order_status.shipping_code  = :search_order_shipping')
                ->setParameter(':search_order_shipping', $paramSearch['search_order_shipping']);
        }

        if ($paramSearch['search_order_otodoke'] != '0') {
            $qb->andWhere('order_status.otodoke_code  = :search_order_otodoke ')
                ->setParameter(':search_order_otodoke', $paramSearch['search_order_otodoke']);
        }

        if ($paramSearch['search_order_no'] != '') {
            $qb->andWhere('order_status.cus_order_no  like :search_order_no ')
                ->setParameter(':search_order_no', $paramSearch['search_order_no'].'%');
        }

        //group
        $qb->addGroupBy('order_status.cus_order_no');
        $qb->addGroupBy('order_status.cus_order_lineno');

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
     * @return QueryBuilder
     */
    public function getDeliveryByCustomer($paramSearch = [], $customer_code = '', $login_type = '')
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'order_status.shipping_code = :customer_code';
                break;

            case 'otodoke_code':
                $condition = 'order_status.otodoke_code = :customer_code';
                break;

            default:
                $condition = 'order_status.customer_code = :customer_code';
                break;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('shipping.shipping_no');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');
        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            'product.product_code = order_status.product_code'
        );
        $qb->innerJoin(
            'Customize\Entity\MstShipping',
            'shipping',
            Join::WITH,
            'shipping.cus_order_no = order_status.cus_order_no AND shipping.cus_order_lineno = order_status.cus_order_lineno'
        );
        $qb->innerJoin(
            'Customize\Entity\MstDelivery',
            'delivery',
            Join::WITH,
            "delivery.shipping_no = shipping.shipping_no AND TRIM(delivery.order_no) = CONCAT(TRIM(shipping.cus_order_no),'-',TRIM(shipping.cus_order_lineno))"
        );

        $qb->addSelect(
            'delivery.delivery_no',
            'delivery.delivery_lineno',
            'delivery.shiping_name',
            'delivery.otodoke_name',
            'delivery.sale_type',
            'delivery.order_no',
            "DATE_FORMAT(delivery.delivery_date,'%Y-%m-%d') AS delivery_date",
            'shipping.shipping_date',
            'shipping.shipping_no',
            'shipping.ec_order_no',
            'shipping.ec_order_lineno'
        );

        $qb->where('shipping.delete_flg IS NOT NULL AND shipping.delete_flg <> 0')
            ->andWhere('order_status.order_date >= :order_date')
            ->andWhere('delivery.delivery_lineno = 1')
            ->andWhere($condition)
            ->setParameter(':order_date', date('Y-m-d', strtotime('-14 MONTH')))
            ->setParameter(':customer_code', $customer_code);

        if (!empty($paramSearch['delivery_no'])) {
            $qb->andWhere('delivery.delivery_no = :delivery_no')
                ->setParameter(':delivery_no', $paramSearch['delivery_no']);
        }

        if ($paramSearch['search_shipping_date'] != 0) {
            $qb->andWhere('delivery.delivery_date like :search_shipping_date')
                ->setParameter(':search_shipping_date', $paramSearch['search_shipping_date'].'-%');
        }

        if ($paramSearch['search_order_shipping'] != '0') {
            $qb->andWhere('TRIM(delivery.shiping_name) = (select mc3.company_name from Customize\Entity\MstCustomer mc3 where mc3.customer_code = :search_order_shipping)')
                ->setParameter(':search_order_shipping', $paramSearch['search_order_shipping']);
        }

        if ($paramSearch['search_order_otodoke'] != '0') {
            $qb->andWhere('TRIM(delivery.otodoke_name) in (select mc4.company_name from Customize\Entity\MstCustomer mc4 where mc4.customer_code = :search_order_otodoke)')
                ->setParameter(':search_order_otodoke', $paramSearch['search_order_otodoke']);
        }

        if ($paramSearch['search_sale_type'] != '0') {
            if ($paramSearch['search_sale_type'] == '1') {
                $qb->andWhere(" TRIM(delivery.sale_type) = '通常' ");
            }

            if ($paramSearch['search_sale_type'] == '2') {
                $qb->andWhere(" TRIM(delivery.sale_type) = '返品' ");
            }
        }

        if (!empty($paramSearch['search_shipping_date_from'])) {
            $qb->andWhere("DATE_FORMAT(delivery.delivery_date,'%Y-%m-%d') >= :shipping_date_from")
                ->setParameter(':shipping_date_from', $paramSearch['search_shipping_date_from']);
        }

        if (!empty($paramSearch['search_shipping_date_to'])) {
            $qb->andWhere("DATE_FORMAT(delivery.delivery_date,'%Y-%m-%d') <= :shipping_date_to")
                ->setParameter(':shipping_date_to', $paramSearch['search_shipping_date_to']);
        }

        $qb->addGroupBy('delivery.delivery_no');
        $qb->addGroupBy('shipping.order_no');

        $qb->addOrderBy('delivery.delivery_no', 'DESC');
        $qb->addOrderBy('shipping.shipping_date', 'DESC');

        //dump($qb->getQuery()->getSQL());
        //dump($qb->getParameters());
        //die();
        return $qb;
    }

    public function getQueryBuilderReturnByCustomer($paramSearch = [], $order_status = [])
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('order_status.cus_order_no', 'order_status.cus_order_lineno');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');
        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            'product.product_code = order_status.product_code'
        );
        $qb->innerJoin(
            'Customize\Entity\MstProductReturnsInfo',
            'product_returns_info',
            Join::WITH,
            'product_returns_info.jan_code = product.jan_code AND product_returns_info.product_code = product.product_code'
        );
        $qb->leftJoin(
            'Customize\Entity\MstShipping',
            'shipping',
            Join::WITH,
            'shipping.cus_order_no = order_status.cus_order_no AND shipping.cus_order_lineno = order_status.cus_order_lineno'
        );

        $qb->addSelect(
            'shipping.shipping_no',
            'shipping.shipping_date',
            'product.jan_code',
            'product.product_name',
            'order_status.shipping_num',
            'product_returns_info.returns_status_flag',
            'product_returns_info.returns_num',
            'product_returns_info.returns_status_flag',
        );
        $qb->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = order_status.shipping_code) shipping_name');
        $qb->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = order_status.otodoke_code) otodoke_name');
        $qb->where('shipping.delete_flg IS NULL OR shipping.delete_flg <> 0');
        $qb->andWhere('order_status.order_date >= :order_date')
            ->setParameter('order_date', date('Y-m-d', strtotime('-24 MONTH')));
        $qb->andWhere('shipping.shipping_status = :shipping_status')
            ->setParameter('shipping_status', 2);

        if (count($order_status) > 0) {
            $where = '';
            foreach ($order_status as $k => $os) {
                if (!empty($where)) {
                    $where .= ' OR ';
                }
                $where .= " ( order_status.cus_order_no = :order_status_cus_order_no_{$k} AND order_status.cus_order_lineno = :order_status_cus_order_lineno_{$k} ) ";
                $qb->setParameter("order_status_cus_order_no_{$k}", $os['cus_order_no']);
                $qb->setParameter("order_status_cus_order_lineno_{$k}", $os['cus_order_lineno']);
            }
            $qb->andWhere($where);
        }

        if ($paramSearch['search_jan_code'] != '') {
            $qb->andWhere('product.jan_code LIKE :search_jan_code')
                ->setParameter(':search_jan_code', "%{$paramSearch['search_jan_code']}%");
        }

        if ($paramSearch['search_shipping_date'] != 0) {
            $qb->andWhere('shipping.shipping_date LIKE :search_shipping_date')
                ->setParameter(':search_shipping_date', "{$paramSearch['search_shipping_date']}-%");
        }

        if ($paramSearch['search_shipping'] != '0') {
            $qb->andWhere('shipping.shipping_code = :search_shipping')
                ->setParameter(':search_shipping', $paramSearch['search_shipping']);
        }

        if ($paramSearch['search_otodoke'] != '0') {
            $qb->andWhere('shipping.otodoke_code = :search_otodoke')
                ->setParameter(':search_otodoke', $paramSearch['search_otodoke']);
        }

        //group
        $qb->addGroupBy('order_status.order_no');
        $qb->addGroupBy('order_status.order_line_no');

        // Order By
        $qb->addOrderBy('order_status.order_date', 'DESC');
        $qb->addOrderBy('order_status.cus_order_no', 'DESC');
        $qb->addOrderBy('order_status.cus_order_lineno', 'ASC');

        // dump($qb->getQuery()->getSQL());
        // dump($qb->getParameters());
        // die();
        return $qb;
    }

    /**
     * @param array $paramSearch
     * @param string $customer_code
     * @param string $login_type
     *
     * @return QueryBuilder
     */
    public function getShippingByCustomer($paramSearch = [], $customer_code = '', $login_type = '')
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'order_status.shipping_code = :customer_code';
                break;

            case 'otodoke_code':
                $condition = 'order_status.otodoke_code = :customer_code';
                break;

            default:
                $condition = 'order_status.customer_code = :customer_code';
                break;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('shipping.shipping_no');
        $qb->from('Customize\Entity\DtOrderStatus', 'order_status');

        $qb->innerJoin(
            'Customize\Entity\MstProduct',
            'product',
            Join::WITH,
            'product.product_code = order_status.product_code'
        );

        $qb->innerJoin(
            'Customize\Entity\MstShipping',
            'shipping',
            Join::WITH,
            'shipping.cus_order_no = order_status.cus_order_no AND shipping.cus_order_lineno = order_status.cus_order_lineno'
        );

        $qb->leftJoin(
            'Customize\Entity\MstDelivery',
            'delivery',
            Join::WITH,
            "delivery.shipping_no = shipping.shipping_no AND TRIM(delivery.order_no) = CONCAT(TRIM(shipping.cus_order_no),'-',TRIM(shipping.cus_order_lineno))"
        );

        $qb->addSelect(
            'shipping.shipping_no',
            'shipping.customer_code',
            'shipping.shipping_status',
            'shipping.shipping_plan_date',
            'shipping.shipping_date',
            'shipping.shipping_num',
            'shipping.order_no',
            'shipping.order_lineno',
            'shipping.cus_order_no',
            'shipping.cus_order_lineno',
            'shipping.ec_order_no',
            'shipping.ec_order_lineno',
            'shipping.shipping_company_code',
            'shipping.inquiry_no',
            'product.jan_code',
            'product.product_name',
            'product.quantity',
            'delivery.delivery_no'
        );

        $qb->addSelect('(SELECT mst_cus.company_name FROM Customize\Entity\MstCustomer mst_cus WHERE mst_cus.customer_code = order_status.shipping_code) shipping_name')
            ->addSelect('(SELECT mst_cus2.company_name FROM Customize\Entity\MstCustomer mst_cus2 WHERE mst_cus2.customer_code = order_status.otodoke_code) otodoke_name')
            ->where('shipping.delete_flg IS NOT NULL AND shipping.delete_flg <> 0')
            ->andWhere('order_status.order_date >= :order_date')
            ->andWhere($condition)
            ->setParameter(':order_date', date('Y-m-d', strtotime('-14 MONTH')))
            ->setParameter(':customer_code', $customer_code);

        if ($paramSearch['shipping_no'] != '') {
            $qb->andWhere('shipping.shipping_no = :shipping_no')
                ->setParameter(':shipping_no', $paramSearch['shipping_no']);
        }

        switch ($paramSearch['shipping_status']) {
            case 1:
                $qb->andWhere('shipping.shipping_status = :shipping_status')
                    ->setParameter(':shipping_status', 1);
                break;
            case 2:
                $qb->andWhere('shipping.shipping_status = :shipping_status')
                    ->setParameter(':shipping_status', 2);
                break;
        }

        if ($paramSearch['order_shipping'] != '0') {
            $qb->andWhere('order_status.shipping_code = :shipping_code')
                ->setParameter('shipping_code', $paramSearch['order_shipping']);
        }

        if ($paramSearch['order_otodoke'] != '0') {
            $qb->andWhere('order_status.otodoke_code = :order_otodoke')
                ->setParameter(':order_otodoke', $paramSearch['order_otodoke']);
        }

        $qb->addGroupBy('shipping.cus_order_no');
        $qb->addGroupBy('shipping.cus_order_lineno');

        $qb->addOrderBy('order_status.order_date', 'DESC');
        $qb->addOrderBy('order_status.cus_order_no', 'DESC');
        $qb->addOrderBy('order_status.cus_order_lineno', 'asc');

        //dump($qb->getQuery()->getSQL());
        //dump($qb->getParameters());
        //die();
        return $qb;
    }
}
