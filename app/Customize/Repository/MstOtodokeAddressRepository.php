<?php

namespace Customize\Repository;


use Customize\Entity\MstShipping;
use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstOtodokeAddress;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstOtodokeAddressRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstOtodokeAddress::class);
    }

}
