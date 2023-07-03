<?php

namespace Customize\Repository;


use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstShippingRoute;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstShippingRouteRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstShippingRoute::class);
    }

}
