<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\StockList;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Customize\Service\Common\MyCommonService;

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
    public function getData($product_code = '', $location = null)
    {
        if ( is_null($location)) return null;

        $qb     = $this->createQueryBuilder('s');
        $qb->where('s.product_code = :product_code  AND s.stock_location = :stock_location')
            ->setParameter('product_code', $product_code)
            ->setParameter('stock_location', $location);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
