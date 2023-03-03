<?php

namespace Customize\Repository;


use Customize\Entity\Price;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PriceRepository extends AbstractRepository
{
    /**
     * PriceRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Price::class);
    }

    /***
     * @param string $product_code
     * @param string $customer_code
     * @return ArrayCollection|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getData($product_code = '', $customer_code ='')
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.product_code = :product_code AND p.customer_code = :customer_code')
            ->setParameter('product_code', $product_code)
            ->setParameter('customer_code', $customer_code);
        return $qb
            ->getQuery()->getOneOrNullResult();
    }
}
