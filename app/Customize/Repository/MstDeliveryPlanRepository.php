<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstDeliveryPlan;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstDeliveryPlanRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstDeliveryPlan::class);
    }

    /***
     * @param string $product_code
     * @param string $customer_code
     * @return ArrayCollection|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getData($product_code = '')
    {
        $qb     = $this->createQueryBuilder('s');
        $qb
            ->where('s.product_code = :product_code')
            ->setParameter('product_code', $product_code)
            ->andWhere('s.delivery_date >= CURRENT_DATE()')
            ->orderBy("ABS( DATE_DIFF( s.delivery_date, CURRENT_DATE() ) )", 'ASC')
            ->groupBy('s.stock_location');
            
        return $qb->getQuery()->getOneOrNullResult();
    }
}
