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

use Customize\Entity\DtOrder;
use Customize\Entity\DtOrderStatus;
use Customize\Entity\MoreOrder;
use Customize\Entity\MstShipping;
use Customize\Entity\Order;
use Customize\Repository\MoreOrderRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\CartItem;
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
     *
     */
    public function getMstCustomer($customerId)
    {
        $column = "a.customer_code as shipping_no,a.customer_code, a.ec_customer_id, a.customer_name as name01, a.company_name, a.company_name_abb,
         a.department, a.postal_code, a.addr01, a.addr02, a.addr03, a.email, a.phone_number, a.create_date, a.update_date";
        $sql = " SELECT $column   FROM mst_customer a join `dtb_customer` `dtcus` on((`dtcus`.`id` = `a`.`ec_customer_id`))  WHERE ec_customer_id=?";
        $param = [];
        $param[] = $customerId;
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }
    public function getMstCustomerCode($customer_code)
    {
        $column = "customer_code as shipping_no,customer_code, ec_customer_id,customer_name, customer_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number, create_date, update_date";
        $sql = " SELECT $column   FROM mst_customer a WHERE customer_code=?";
        $param = [];
        $param[] = $customer_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }
    public function getShipList($customer_code,$shipping_no,$order_no)
    {

        $sql = " select c.inquiry_no ,c.shipping_no,d.customer_name ,c.product_code,
                    case when c.shipping_status = 1 then '出荷指示済'  else '出荷済' end as shipping_status,c.shipping_num
                    ,c.shipping_plan_date ,c.inquiry_no,c.shipping_company_code
                    from dt_order_status as a
                    join mst_product as b
                    on a.product_code = b.product_code
                    join mst_shipping as c
                    on a.ec_order_no = c.ec_order_no
                    and a.ec_order_lineno = c.ec_order_lineno
                    join mst_customer as d
                    on c.customer_code = d.customer_code
                    -- join dt_customer_relation as e   on c.shipping_code = e.shipping_code
                    where a.customer_code = ? and c.shipping_no=? and a.ec_order_no=?";
        $param = [];
        $param[] = $customer_code;
        $param[] = $shipping_no;
        $param[] = $order_no;


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
     * @param MoreOrder $moreOrder
     */
    public function getMstShippingCustomer($customerId, MoreOrder $moreOrder = null)
    {
        $column = "customer_code as shipping_no,b.shipping_code, ec_customer_id, customer_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number, create_date, update_date";
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
    public function getMstProductsOrderNo($order_no)
    {



        $sql = " 	select b.id AS ec_order_lineno,a.order_no,b.product_id,c.product_code,c.jan_code,c.quantity as product_quantity ,b.quantity	 from
				dtb_order as a  join dtb_order_item b on a.id = b.order_id
				join mst_product as c   on c.ec_product_id = b.product_id
			WHERE order_no='$order_no'
				ORDER BY b.id asc ";
        $param = [];

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getImageFromEcProductId($myCart)
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
        if(count($myCart)==0){
            return  [];
        }

        $sql = " SELECT a.file_name,a.product_id,b.product_code
                FROM  dtb_product_image a JOIN mst_product b
                ON b.ec_product_id = a.product_id
                WHERE  a.id IN(

                SELECT MIN(a.id)
                                 FROM dtb_product_image  a
                                 WHERE a.product_id in({$subWhere})
                                GROUP BY a.product_id  )
                                 ORDER BY a.id ASC
                ";
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

    /**
     * @param
     */
    public function getdtPriceFromCart($myCart,$customer_code)
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
        if(count($myCart)==0){
            return  [];
        }


        $sql = " SELECT b.id,c.product_code,c.unit_price,c.ec_product_id,dtPrice.price_s01
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
                JOIN mst_product AS c ON a.product_id = c.ec_product_id
               LEFT join dt_price AS dtPrice ON dtPrice.product_code = c.product_code
                WHERE b.cart_id in({$subWhere}) and dtPrice.customer_code ='{$customer_code}' ";
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

    public function getPriceFromDtPriceOfCus($customer_code="")
    {
        $arR = [];
        if($customer_code=="") {
            return [];
        }


        $sql = "select pri.product_code,pri.customer_code  from dt_price pri WHERE customer_code=?
                and DATE_FORMAT(NOW(),'%Y-%m-%d')>= pri.valid_date   AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= pri.expire_date
                GROUP BY pri.product_code,pri.customer_code
                HAVING COUNT(*)=1
                ; ";
        $param = [$customer_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            foreach ($rows as $item){
                $arR[] = $item["product_code"];
            }

            return $arR;
        } catch (\Exception $e) {
            log_info($e->getMessage());
            return [];
        }
    }

    public function updateCartItem($hsPrice,$arCarItemId,$Cart)
    {

        $objList = $this->entityManager->getRepository(CartItem::class)->findBy(['Cart' => $Cart]);
        $myDb = $this->entityManager->createQueryBuilder();
        //$resSult = $myDb->select([])->from('CartItem','cartItem')->where('cartItem.id in(:ids)')->setParameter('ids',$arCarItemId)->getQuery()->getArrayResult();
        //var_dump($resSult);die();
        foreach ($objList as $carItem ){

            if(isset($hsPrice[$carItem->getId()])){
                $carItem->setPrice($hsPrice[$carItem->getId()]);
                $this->entityManager->persist($carItem);
                $this->entityManager->flush();
            }

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
        if(count($myCart)==0){
            return  [];
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
        $keyS = date('mdHis');
        $keyTem = (int) $keyS;
        $total = count($arEcLData);
        foreach ($arEcLData as $itemSave) {
            $cusOrderLineno = $total;
            $total--;
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $cusOrderLineno;//$itemSave['ec_order_lineno'];
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
            $orderItem->setShippingStatus(0);
            $orderItem->setShippingNum(0);

            $orderItem->setShippingDate('');
            $orderItem->setInquiryNo('');
            $orderItem->setShippingCompanyCode('');
            $orderItem->setOrderNo($ec_order);
            $orderItem->setOrderLineno($ec_order_lineno);

            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setShippingCode($itemSave['shipping_code']);
            $orderItem->setProductCode($itemSave['product_code']);
            $orderItem->setShippingPlanDate($itemSave['shipping_plan_date']??'');


            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }
    public function savedtOrder($arEcLData)
    {

        $total = count($arEcLData);
        foreach ($arEcLData as $itemSave) {
            $cusOrderLineno = $total;
            $total--;
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $cusOrderLineno;//$itemSave['ec_order_lineno'];
            $keyFind = ['order_no' => $ec_order, 'order_lineno' => $ec_order_lineno];
            $objRep = $this->entityManager->getRepository(DtOrder::class)->findOneBy($keyFind);
            $orderItem = new DtOrder();
            if ($objRep !== null) {
                $orderItem = $objRep;
            }

            $orderItem->setOrderLineno($ec_order_lineno);
            $orderItem->setOrderNo($ec_order);
            $orderItem->setShippingCode($itemSave["shipping_code"]);
            $orderItem->setSeikyuCode($itemSave["seikyu_code"]);
            $orderItem->setShipingPlanDate($itemSave['shipping_plan_date']??'');
            $orderItem->setRequestFlg('Y');
            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setProductCode($itemSave['product_code']);
            $orderItem->setOtodokeCode($itemSave['otodoke_code']);
            $orderItem->setOrderPrice($itemSave['order_price']);
            $orderItem->setDemandQuantity($itemSave['demand_quantity']);
            // No41 注文情報送信I/F start
            $time = new \DateTime();
            $orderItem->setOrderDate($time);                                                                // ・受注日←受注日(購入日)
            if(!is_null($itemSave['deli_plan_date']))  {
                $orderItem->setDeliPlanDate($itemSave['deli_plan_date']);                                       // ・希望納期（納入予定日）←配送日指定
            }
            $orderItem->setItemNo($itemSave['item_no']);                                                    // ・客先品目No←JANコード
            $orderItem->setDemandUnit($itemSave['demand_unit']);                                            // ・需要単位←商品情報の入り数が‘1’の場合、‘PC’、入り数が‘1’以外の場合、‘CS’
            $orderItem->setDynaModelSeg2($itemSave['dyna_model_seg2']);                                     // ・ダイナ規格セグメント02←EC注文番号
            $orderItem->setDynaModelSeg4($itemSave['dyna_model_seg4']);                                     // ・ダイナ規格セグメント04←EC注文番号
            $orderItem->setDynaModelSeg5($ec_order_lineno);                                                 // ・ダイナ規格セグメント05←EC注文明細番号
            $orderItem->setUnitPriceStatus('FOR');
            $orderItem->setDeploy('G');
            $orderItem->setCompanyId('6000');
            $orderItem->setDynaModelSeg3($itemSave['customer_code'] == '6000' ? '1' : '2');
            // No41 注文情報送信I/F end
            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }
    public function saveOrderStatus($arEcLData)
    {
        //dt_order_status
        //$arEcLData[] = ['ec_order_no'=>$orderNo,'ec_order_lineno'=>$itemOr->getId()];
        $cusOrderLineno=0;
        $total = count($arEcLData);
        foreach ($arEcLData as $itemSave) {
            $cusOrderLineno = $total;
            $total--;
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $cusOrderLineno;//$itemSave['ec_order_lineno'];
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
            //"cus_order_no"=>$ec_order,"cus_order_lineno"=>$ec_order_lineno
            $orderItem->setCusOrderNo($ec_order);
            $orderItem->setCusOrderLineno($cusOrderLineno);
            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setShippingCode($itemSave['shipping_code']);
            $orderItem->setOrderRemainNum($itemSave['order_remain_num']);
            $orderItem->setProductCode($itemSave['product_code']);


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

    /**
     * @param
     */
    public function getTaxInfo()
    {
        $sql = "
                SELECT
                    *
                FROM
				    dtb_tax_rule
			    ";

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery();
            $rows = $result->fetchAllAssociative();
            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param
     */
    public function getMstShippingOrder($customerId,$pre_order_id)
    {
        $sql = "
        SELECT mst_customer.*,mst_shipping.*
        FROM  mst_customer
        JOIN mst_shipping
        ON mst_shipping.customer_code = mst_customer.customer_code
        WHERE ec_customer_id=?
        AND mst_shipping.order_no = ?
        LIMIT 1
        ";
        $param = [$customerId,$pre_order_id];

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param
     */
    public function getMstProductsOrderCustomer($order_no)
    {

//        $sql = "
//         SELECT
//            a.id AS 	 order_no,
//            a.customer_id customer_id,
//            d.customer_code customer_code,
//            b.product_id AS product_id,
//            c.product_code AS product_code,
//            c.product_name AS product_name,
//            c.unit_price AS unit_price,
//            b.quantity AS quantity,
//             IF(e.count_price = 1, (SELECT price_s01 FROM dt_price WHERE product_code = c.product_code AND customer_code = d.customer_code), c.unit_price) AS price
//        FROM dtb_order a
//        JOIN dtb_order_item b ON a.id = b.order_id
//        JOIN mst_product c ON c.ec_product_id = b.product_id
//        JOIN mst_customer d ON d.ec_customer_id = a.customer_id
//        LEFT JOIN
//		   ( SELECT
//		   	product_code,
//		   	customer_code,
//				COUNT(price_s01) AS count_price
//			  FROM  dt_price
//			  GROUP BY
//			  	product_code,
//		   	customer_code
//			  ) e ON e.product_code = c.product_code AND e.customer_code = d.customer_code
//        WHERE order_no=?
//        ORDER BY b.id ASC
//         ";
       $sql = "
         SELECT
            a.id AS 	 order_no,
            a.customer_id customer_id,
            d.customer_code customer_code,
            b.product_id AS product_id,
            c.product_code AS product_code,
            c.product_name AS product_name,
            c.unit_price AS unit_price,
            b.quantity AS quantity,
            b.price
        FROM dtb_order a
        JOIN dtb_order_item b ON a.id = b.order_id
        JOIN mst_product c ON c.ec_product_id = b.product_id
        JOIN mst_customer d ON d.ec_customer_id = a.customer_id
        LEFT JOIN
		   ( SELECT
		   	product_code,
		   	customer_code,
				COUNT(price_s01) AS count_price
			  FROM  dt_price
			  GROUP BY
			  	product_code,
		   	customer_code
			  ) e ON e.product_code = c.product_code AND e.customer_code = d.customer_code
        WHERE order_no=?
        ORDER BY b.id ASC
         ";
        $param = [$order_no];

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
     * @param $order_id
     */
    public function updateOrderNo($order_id,$paymentTotal)
    {
        $obj = $this->entityManager->getRepository(\Eccube\Entity\Order::class)->findOneBy(['id' => $order_id]);
        $order = new \Eccube\Entity\Order();
        if ($obj !== null) {
            $order = $obj;
        }
        $order->setOrderNo($order_id);
        $order->setPaymentTotal($paymentTotal);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

    }
    public function updatePaymentTotalOrder($order_id,$payment_total){
        $sql = "
         update
             dtb_order
            set payment_total=?

            WHERE id = ?
         ";
        $param = [$payment_total,$order_id];

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeStatement($param);
            return $result;
        } catch (Exception $e) {
            log_info("updatePaymentTotalOrder ".$e->getMessage());
            return null;
        }
    }

    /**
     * @param
     */
    public function getMoreOrderCustomer($pre_order_id)
    {
        $sql = "
         SELECT
            pre_order_id,
            seikyu_code,
            otodoke_code,
            date_want_delivery AS shipping_plan_date,
            mst_customer.*
            FROM more_order
            JOIN mst_customer
            ON otodoke_code = customer_code
            WHERE pre_order_id = ?
         ";
        $param = [$pre_order_id];

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            //getMoreOrderCustomer

            $rows = $result->fetchAllAssociative();
            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }
    public function getPriceFromDtPriceOfCusProductcode($customer_code="",$productCode)
    {
        $arR = [];
        if($customer_code=="") {
            return [];
        }


        $sql = "select pri.product_code,pri.customer_code,pri.price_s01,pri.valid_date
                 from dt_price pri
                 WHERE customer_code=?
                    and DATE_FORMAT(NOW(),'%Y-%m-%d') >= pri.valid_date    AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= pri.expire_date
                    and pri.product_code=?
                    GROUP BY pri.product_code,pri.customer_code,pri.valid_date
                    HAVING COUNT(*)=1
                ; ";
        $param = [$customer_code,$productCode];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            if(count($rows)==1){
                return $rows[0]["price_s01"];
            }
           return "";


        } catch (\Exception $e) {
            log_info($e->getMessage());
            var_dump("xxxxxxxx",$sql,$e->getMessage());
            return "";
        }
    }

}
