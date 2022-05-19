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
        $sql = 'SELECT *   FROM dtb_customer';
        $em = $this->entityManager;

        $stmt = $em->getConnection()->prepare($sql);
        // var_dump($stmt->executeQuery([]));
    }

    public function getMstShipping()
    {
        $sql = 'SELECT *   FROM mst_shipping';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery();

        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function getCustomerAddress()
    {
        $sql = 'SELECT *   FROM dtb_customer_address';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery();
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    /***
     * Otodoke  nhan hang hoa
     * @param $customer_id
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCustomerOtodoke($customer_id, $shipping_code)
    {
        //otodoke_code dia chi nhan hang
        $sql = 'SELECT a.*   FROM dtb_customer_address a
                join dt_customer_relation b on b.otodoke_code =a.id and a.customer_id=b.customer_code
                where a.customer_id=? and b.shipping_code=?
                ';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery([$customer_id, $shipping_code]);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    /***
     * seikyu_code  noi nhan hoa don
     * @param $customer_id
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCustomerSeikyuCode($customer_id,$shipping_code)
    {
        //seikyu_code  noi nhan hoa don
        $sql = 'SELECT a.*   FROM dtb_customer_address a
                join dt_customer_relation b on b.seikyu_code =a.id and a.customer_id=b.customer_code
                where a.customer_id=? and b.shipping_code=?
                ';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery([$customer_id,$shipping_code]);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    /***
     * @param $shipping_code
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCart($shipping_code, $pre_order_id)
    {
        $sql = 'update  dtb_order SET shipping_code=? where pre_order_id = ?';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeStatement([$shipping_code, $pre_order_id]);
    }
}
