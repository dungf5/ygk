<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MoreOrder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MoreOrderRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MoreOrder::class);
    }

}
