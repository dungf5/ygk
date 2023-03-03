<?php

namespace Customize\Repository;


use Eccube\Repository\AbstractRepository;
use Customize\Entity\MstCustomer;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MstCustomerRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MstCustomer::class);
    }

}
