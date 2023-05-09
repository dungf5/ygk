<?php

namespace Customize\Repository;


use Eccube\Repository\AbstractRepository;
use Customize\Entity\NatStockList;
use Symfony\Bridge\Doctrine\RegistryInterface;

class NatStockListRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NatStockList::class);
    }

}
