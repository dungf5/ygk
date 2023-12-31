<?php

namespace Customize\Repository;


use Customize\Entity\MstDelivery;
use Customize\Entity\MstShipping;
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
    public function getQueryBuilderByDeli($delivery_no)
    {

        //ordStatus.update_date,
        $sqlColumns="mstDeli.delivery_no
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
                    ,mstDeli.item_no
                    ,mstDeli.item_name
                    ,mstDeli.quanlity
                    ,mstDeli.unit
                    ,mstDeli.unit_price
                    ,mstDeli.amount
                    ,mstDeli.tax
                    ,mstDeli.lot_no
                    ,mstDeli.order_no
                    ,mstDeli.item_remark
                    ,mstDeli.total_amount
                    ,mstDeli.footer_remark1
                    ,mstDeli.shiping_name as shiping_code
                    ,mstDeli.otodoke_name as otodoke_code";
        $qb =  $this->getEntityManager()->createQueryBuilder();

        $qb ->select($sqlColumns)
            ->from('Customize\Entity\MstDelivery', 'mstDeli')
            ->where('mstDeli.delivery_no = :delivery_no')
            ->setParameter('delivery_no', $delivery_no);

        // Order By
        $qb->addOrderBy('mstDeli.delivery_lineno', 'asc');
        //var_dump( $qb->getQuery()->getSQL());
       $arResult = $qb->getQuery()->getArrayResult();
        return $arResult;
    }

}
