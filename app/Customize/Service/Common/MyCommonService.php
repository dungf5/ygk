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

use Customize\Entity\DtOrderStatus;
use Customize\Entity\MoreOrder;
use Customize\Entity\MstShipping;
use Customize\Repository\MoreOrderRepository;
use Doctrine\DBAL\Driver\Exception;
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

    /**
     * @param MoreOrder $moreOrder
     */
    public function getMstShippingCustomer($customerId, MoreOrder $moreOrder = null)
    {
        $column = "customer_code as shipping_no, ec_customer_id, customer_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number, create_date, update_date";
        $sql = " SELECT $column   FROM mst_customer a  join
                (
                SELECT b.shipping_code from dt_customer_relation b

					 WHERE  b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                GROUP BY  b.shipping_code
                ) AS b ON  b.shipping_code =a.customer_code";
        $param = [];
        $param[] = $customerId;
        if (null != $moreOrder) {
            $sql = " SELECT $column   FROM mst_customer a  join
                (
                SELECT b.shipping_code from dt_customer_relation b

					 WHERE  b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                GROUP BY  b.shipping_code
                ) AS b ON  b.shipping_code =a.customer_code and b.shipping_code=?";
            $param[] = $moreOrder->getShippingCode();
        }else{

        }
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }
    /**
     * @param
     */
    public function getMstProductsFromCart($myCart)
    {
        $subWhere = "";
        $c = count($myCart);
        for ($i = 0;$i<$c;$i++) {
            if($i ==$c-1){
                $subWhere .="?";
            }else{
                $subWhere .="?,";
            }
        }

        $sql = " SELECT c.*
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
                JOIN mst_product AS c ON a.product_id = c.ec_product_id
                WHERE b.cart_id in({$subWhere}) ";
        $param = [];
        $param =$myCart;
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getMainImgProduct($whereI)
    {
        $sql = 'SELECT file_name   FROM dtb_product_image where product_id=1 order by sort_no';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery();
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function runQuery($query)
    {
        $sql = $query;
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
     * seikyu_code  noi nhan hoa don
     * @param $customer_id
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCustomerBillSeikyuCode($customer_id, $moreOrder = null)
    {
        $column = "a.customer_code as seikyu_code, ec_customer_id, customer_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number";

        //seikyu_code  noi nhan hoa don
        $sql = " SELECT {$column}   FROM mst_customer a  join
                (
                SELECT b.seikyu_code from dt_customer_relation b

					 WHERE  b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                GROUP BY  b.seikyu_code
                ) AS b ON  b.seikyu_code =a.customer_code
                ";

        $myPara = [$customer_id];
        if ($moreOrder != null) {
            $seikyu_code = $moreOrder->getSeikyuCode();
            $sql = " SELECT {$column}   FROM mst_customer a  join
                (
                SELECT b.seikyu_code from dt_customer_relation b

					 WHERE  b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                GROUP BY  b.seikyu_code
                ) AS b ON  b.seikyu_code =a.customer_code and b.seikyu_code=?
                ";
            $myPara[] = $seikyu_code;
        }
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
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
    public function getCustomerOtodoke($customer_id, $shipping_code, $moreOrder = null)
    {
        //otodoke_code dia chi nhan hang
        $column = "a.customer_code as otodoke_code, ec_customer_id, customer_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number";

        $sql = "  SELECT {$column}  FROM mst_customer a  join
                (
                SELECT  b.otodoke_code from dt_customer_relation b  where b.shipping_code =?
                  AND   b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                ) AS b ON  b.otodoke_code =a.customer_code"
                ;
        $myPara = [ $shipping_code,$customer_id];
        if ($moreOrder != null) {
            $sql = "  SELECT {$column}  FROM mst_customer a  join
                (
                SELECT  b.otodoke_code from dt_customer_relation b  where b.shipping_code =?
                  AND   b.customer_code= ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id=?  LIMIT 1 )
                ) AS b ON  b.otodoke_code =a.customer_code and b.otodoke_code=?"
            ;
            $myPara[] = $moreOrder->getOtodokeCode();
        }
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

//    /***
//     * seikyu_code  noi nhan hoa don
//     * @param $customer_id
//     * @return array
//     * @throws \Doctrine\DBAL\Driver\Exception
//     * @throws \Doctrine\DBAL\Exception
//     */
//    public function getCustomerSeikyuCode($customer_id,$shipping_code)
//    {
//        //seikyu_code  noi nhan hoa don
//        $sql = 'SELECT a.*   FROM dtb_customer_address a
//                join dt_customer_relation b on b.seikyu_code =a.id and a.customer_id=b.customer_code
//                where a.customer_id=? and b.shipping_code=?
//                ';
//        $statement = $this->entityManager->getConnection()->prepare($sql);
//        $result = $statement->executeQuery([$customer_id,$shipping_code]);
//        $rows = $result->fetchAllAssociative();
//
//        return $rows;
//    }

    /***
     * @param $shipping_code
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCart($shipping_code, $pre_order_id)
    {
        // $rep = new MoreOrderRepository();
        //$objRep = $rep->findOneBy(["more_order"=>$pre_order_id]);
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();
        if ($objRep !== null) {
            $orderItem = $objRep;
        }
        $orderItem->setPreOrderId($pre_order_id);
        $orderItem->setShippingCode($shipping_code);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }

    public function saveTempCartDeliDate($date_want_delivery, $pre_order_id)
    {
        // $rep = new MoreOrderRepository();
        //$objRep = $rep->findOneBy(["more_order"=>$pre_order_id]);
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();
        if ($objRep !== null) {
            $orderItem = $objRep;
        }
        $orderItem->setPreOrderId($pre_order_id);
        $orderItem->setShippingCode($date_want_delivery);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }

    public function saveOrderShiping($arEcLData)
    {
        //dt_order_status
        //$arEcLData[] = ['ec_order_no'=>$orderNo,'ec_order_lineno'=>$itemOr->getId()];
        $keyS = date('YmdHis');
        $keyTem = (int) $keyS;
        foreach ($arEcLData as $itemSave) {
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $itemSave['ec_order_lineno'];
            $keyFind = ['ec_order_no' => $ec_order, 'ec_order_lineno' => $ec_order_lineno];
            $objRep = $this->entityManager->getRepository(MstShipping::class)->findOneBy($keyFind);
            $orderItem = new MstShipping();
            if ($objRep !== null) {
                $orderItem = $objRep;
            }
            $keyTem = $keyTem + 1 + rand(1, 10000);
            $orderItem->setShippingNo($keyTem);
            $orderItem->setEcOrderLineno($ec_order_lineno);
            $orderItem->setEcOrderNo($ec_order);
            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }

    public function saveOrderStatus($arEcLData)
    {
        //dt_order_status
        //$arEcLData[] = ['ec_order_no'=>$orderNo,'ec_order_lineno'=>$itemOr->getId()];
        foreach ($arEcLData as $itemSave) {
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $itemSave['ec_order_lineno'];
            $keyFind = ['ec_order_no' => $ec_order, 'ec_order_lineno' => $ec_order_lineno];
            $objRep = $this->entityManager->getRepository(DtOrderStatus::class)->findOneBy($keyFind);
            $orderItem = new DtOrderStatus();
            if ($objRep !== null) {
                $orderItem = $objRep;
            } else {
                $orderItem->setOrderStatus('1');
            }
            // $orderItem->setPropertiesFromArray($keyFind,['create_date']);
            $orderItem->setEcOrderLineno($ec_order_lineno);
            $orderItem->setEcOrderNo($ec_order);
            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }

    public function getMoreOrder($pre_order_id)
    {
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);

        return $objRep;
    }

    /***
     * @param $shipping_code
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCartDeliCodeOto($otodoke_code, $pre_order_id)
    {
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();
        if ($objRep !== null) {
            $orderItem = $objRep;
        }
        $orderItem->setPreOrderId($pre_order_id);
        $orderItem->setOtodokeCode($otodoke_code);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }

    /***
     * @param $date_want_delivery
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCartDateWantDeli($date_want_delivery, $pre_order_id)
    {
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();

        if ($objRep !== null) {
            $orderItem = $objRep;
        }

        $orderItem->setPreOrderId($pre_order_id);
        $orderItem->setPropertiesFromArray(["date_want_delivery"=>$date_want_delivery]);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }

    /***
     * @param $shipping_code
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCartBillSeiky($bill_code, $pre_order_id)
    {
//        $sql = 'update  more_order SET seikyu_code=? where pre_order_id = ?';
//        $statement = $this->entityManager->getConnection()->prepare($sql);
//        $result = $statement->executeStatement([$bill_code, $pre_order_id]);
        ///
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();
        if ($objRep !== null) {
            $orderItem = $objRep;
        }
        $orderItem->setPreOrderId($pre_order_id);
        $orderItem->setSeikyuCode($bill_code);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }
}
