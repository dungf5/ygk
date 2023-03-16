<?php

namespace Customize\Repository;


use Eccube\Repository\AbstractRepository;
use Customize\Entity\DtOrderWSEOSCopy;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderWSEOSCopyRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderWSEOSCopy::class);
    }

}
