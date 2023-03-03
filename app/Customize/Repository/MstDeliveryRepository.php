<?php

namespace Customize\Repository;


use Customize\Entity\MstDelivery;
use Customize\Entity\MstShipping;
use Doctrine\ORM\Query\Expr\Join;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstDeliveryRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstDelivery::class);
    }
    public function getQueryBuilderByDeli($delivery_no,$order_no_line_no)
    {

        //ordStatus.update_date,
        $sqlColumns="SUBSTRING(m0_.order_no, POSITION(\"-\" IN m0_.order_no)+1) AS koduoc,mstDeli.delivery_no
                    ,mstDeli.delivery_date
                    ,mstDeli.deli_post_code
                    ,mstDeli.deli_addr01
                    ,mstDeli.deli_addr02
                    ,mstDeli.deli_addr03
                    ,mstDeli.deli_company_name
                    ,mstDeli.deli_department
                    ,mstDeli.postal_code
                    ,mstDeli.addr01
                    ,mstDeli.addr02
                    ,mstDeli.addr03
                    ,mstDeli.company_name
                    ,mstDeli.department
                    ,mstDeli.delivery_lineno
                    ,mstDeli.sale_type
                    ,mstProduct.jan_code as item_no
                    ,mstDeli.item_name
                    ,mstDeli.quanlity
                    ,mstDeli.unit
                    ,mstDeli.unit_price
                    ,mstDeli.amount
                    ,mstDeli.tax
                    ,mstDeli.order_no
                    ,mstDeli.item_remark
                    ,mstDeli.total_amount
                    ,mstDeli.footer_remark1
                    ,mstDeli.shiping_name as shiping_code
                    ,mstDeli.otodoke_name as otodoke_code
                    ,mstCus.department as deli_department_name
                    ";
        $qb =  $this->getEntityManager()->createQueryBuilder();
//order_no
        $orderNo = explode("-",$order_no_line_no)[0];
        $qb ->select($sqlColumns)
            ->from('Customize\Entity\MstDelivery', 'mstDeli')
            ->leftJoin('Customize\Entity\MstCustomer', 'mstCus',Join::WITH, 'mstCus.customer_code=mstDeli.deli_department')
            ->leftJoin('Customize\Entity\MstProduct', 'mstProduct',Join::WITH, 'mstProduct.product_code=mstDeli.item_no')
            ->where('mstDeli.order_no like :order_no')
            ->setParameter('order_no', "%{$orderNo}-%");
        //  ->setParameter('delivery_no', $delivery_no)
        //  ->where('mstDeli.delivery_no = :delivery_no and mstDeli.order_no =:order_no_line_no')

        // Order By
        $qb->addOrderBy('mstDeli.order_no', 'asc');
        //var_dump( $qb->getQuery()->getSQL());die();
       $arResult = $qb->getQuery()->getArrayResult();
        return $arResult;
    }

}
