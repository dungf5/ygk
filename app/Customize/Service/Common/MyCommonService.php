<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Customize\Service\Common;
use Doctrine\ORM\EntityManagerInterface;


use Doctrine\ORM\Query\ResultSetMapping;
use Eccube\Repository\AbstractRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MyCommonService extends AbstractRepository
{


    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;



    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param EntityManagerInterface $entityManager
     * @required
     */
    public function __construct(EntityManagerInterface $entityManager

    ) {
        $this->entityManager = $entityManager;
    }

    public function getAddressReciveProduct($customerId)
    {
        $sql = "SELECT *   FROM dtb_customer";
        $em = $this->entityManager;

        $stmt = $em->getConnection()->prepare($sql);
       // var_dump($stmt->executeQuery([]));

    }
    public function getMstShipping()
    {
        $sql = "SELECT *   FROM mst_shipping";
        $statement = $this->entityManager->getConnection()->prepare('SELECT * FROM mst_shipping');
        $result = $statement->executeQuery();

        $rows = $result->fetchAllAssociative();
        return $rows;



    }



}
