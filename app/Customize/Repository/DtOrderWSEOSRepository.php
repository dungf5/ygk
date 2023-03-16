<?php

namespace Customize\Repository;


use Eccube\Repository\AbstractRepository;
use Customize\Entity\DtOrderWSEOS;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderWSEOSRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderWSEOS::class);
    }

}
