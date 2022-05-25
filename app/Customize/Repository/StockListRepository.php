<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\StockList;
use Symfony\Bridge\Doctrine\RegistryInterface;

class StockListRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StockList::class);
    }

    /***
     * @param string $product_code
     * @param string $customer_code
     * @return ArrayCollection|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getData($product_code = '', $customer_code ='')
    {
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.product_code = :product_code AND s.customer_code = :customer_code')
            ->setParameter('product_code', $product_code)
            ->setParameter('customer_code', $customer_code);
        return $qb
            ->getQuery()->getOneOrNullResult();
    }
}
