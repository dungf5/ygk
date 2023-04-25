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
use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Customer;
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
    public function __construct(
        EntityManagerInterface $entityManager
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

    public function getMstCustomer($customerId)
    {
        $column = '
                    a.customer_code as shipping_no,
                    a.customer_code,
                    a.ec_customer_id,
                    a.customer_name as name01,
                    a.company_name,
                    a.company_name_abb,
                    a.department,
                    a.postal_code,
                    a.addr01,
                    a.addr02,
                    a.addr03,
                    dtcus.email,
                    a.phone_number,
                    a.create_date,
                    a.update_date,
                    a.email as customer_email,
                    a.special_order_flg,
                    a.price_view_flg
         ';

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

    public function getMstCustomer2($customer_code)
    {
        $sql = '
                        SELECT
                            mstcus.*
                        FROM
                            mst_customer AS mstcus
                        WHERE
                            mstcus.customer_code = ?
                        LIMIT 1;
                    ';

        $param = [];
        $param[] = $customer_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getFullCustomer($customer, $login_type)
    {
        $where = '';
        switch ($login_type) {
            case 'shipping_code':
                $where = ' c.shipping_code  = :customer_code ';
                break;
            case 'otodoke_code':
                $where = ' c.otodoke_code  = :customer_code ';
                break;
            case 'represent_code':
            case 'customer_code':
            case 'change_type':
            default:
                $where = ' c.customer_code  = :customer_code ';
                // $where = "(
                //     CASE
                //          WHEN c.represent_code = '' OR c.represent_code IS NULL THEN c.customer_code
                //         ELSE c.represent_code
                //     END ) = :customer_code";
                break;
        }
        $sql = <<<SQL
        SELECT
            CASE
                WHEN LEFT( a.represent_code, 1 ) = 't' THEN a.otodoke_code
                WHEN LEFT ( a.represent_code, 1 ) = 's' THEN a.shipping_code
                ELSE a.customer_code
            END AS shipping_no,
            b.company_name,
            b.customer_code,
            b.ec_customer_id,
            b.customer_name as name01,
            b.company_name,
            b.company_name_abb,
            b.department,
            b.postal_code,
            b.addr01,
            b.addr02,
            b.addr03,
            c.email,
            b.phone_number,
            b.create_date,
            b.update_date,
            b.email as customer_email,
            b.special_order_flg,
            b.price_view_flg
        FROM
                dt_customer_relation AS a
                JOIN mst_customer b ON
                b.customer_code = ( CASE
                    WHEN LEFT( a.represent_code, 1 ) = 't' THEN a.otodoke_code
                    WHEN LEFT( a.represent_code, 1 ) = 's' THEN a.shipping_code
                    ELSE a.customer_code
                END )
                JOIN dtb_customer AS c ON c.id = b.ec_customer_id
        WHERE
                ( CASE
                    WHEN LEFT ( a.represent_code, 1 ) = 't' THEN a.otodoke_code
                    WHEN LEFT ( a.represent_code, 1 ) = 's' THEN a.shipping_code
                    ELSE a.customer_code
                END ) = ( SELECT
                    CASE
                        WHEN LEFT( c.represent_code, 1 ) = 't' THEN c.otodoke_code
                        WHEN LEFT( c.represent_code, 1 ) = 's' THEN c.shipping_code
                        ELSE c.customer_code
                    END
                FROM
                    dt_customer_relation AS c
                WHERE
                    {$where}
            LIMIT 1 );
        LIMIT 1;
SQL;

        $param = [];
        $param['customer_code'] = $customer->getCustomerCode();
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows[0];
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCustomerFromUserCode($login_code)
    {
        $column = '
                        a.customer_code as shipping_no,
                        a.customer_code,
                        a.ec_customer_id,
                        a.company_name as name01,
                        a.company_name,
                        a.company_name_abb,
                        a.department,
                        a.postal_code,
                        a.addr01,
                        a.addr02,
                        a.addr03,
                        dtcus.email,
                        a.phone_number,
                        a.create_date,
                        a.update_date
                    ';

        $sql = "
                        SELECT $column
                        FROM
                            mst_customer `a`
                        JOIN
                            `dtb_customer` `dtcus`
                        ON
                            (`dtcus`.`id` = `a`.`ec_customer_id`)
                        WHERE
                            `a`.`customer_code` = ?
                        OR
                            `dtcus`.`id` = ?
                    ";

        $param = [];
        $param[] = $login_code;
        $param[] = $login_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getMstCustomerCode($customer_code)
    {
        $column = 'customer_code as shipping_no,customer_code, ec_customer_id,customer_name, company_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number, create_date, update_date';
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

    public function getShipList($type, $customer_code, $shipping_no, $order_no, $jan_code, $loginType = null)
    {
        if ($loginType == 'represent_code' || $loginType == 'customer_code' || $loginType == 'change_type') {
            $condition = ' a.customer_code  = ? ';
        } elseif ($loginType == 'shipping_code') {
            $condition = ' a.shipping_code = ? ';
        } elseif ($loginType == 'otodoke_code') {
            $condition = ' a.otodoke_code = ? ';
        } else {
            $condition = ' a.customer_code  = ? ';
        }

        $sql = " select
                    c.otodoke_code,
                    d.company_name as user_created_company_name,
                    b.jan_code,
                    f.order_no as deli_order_no,
                    c.ec_order_no,
                    c.ec_order_lineno,
                    b.product_name,
                    f.delivery_no,
                    c.inquiry_no,
                    c.shipping_no,
                    cus2.customer_name as shipping_customer_name,
                    c.shipping_code,
                    d.customer_name,
                    c.product_code,
                    case
                        when c.shipping_status = 1 then '出荷指示済'
                        WHEN shipping_status = 2 then '出荷済'
                        else '未出荷'
                    end as shipping_status,
                    c.shipping_num,
                    c.shipping_plan_date,
                    c.inquiry_no,
                    c.shipping_company_code,
                    c.shipping_date,
                    CASE
                        WHEN TRIM(c.shipping_company_code) = '8001' THEN '西濃運輸'
                        WHEN TRIM(c.shipping_company_code) = '8002' THEN 'ヤマト運輸'
                        WHEN TRIM(c.shipping_company_code) = '8003' THEN '佐川急便'
                        WHEN TRIM(c.shipping_company_code) = '8004' THEN '日本郵便'
                        WHEN TRIM(c.shipping_company_code) = '8005' THEN 'ＴＯＬＬ'
                        ELSE ''
                    END as shipping_company_name
                from dt_order_status as a
                join mst_product as b
                on a.product_code = b.product_code
                join mst_shipping as c
                on a.cus_order_no = c.cus_order_no
                and a.cus_order_lineno = c.cus_order_lineno
                join mst_customer as d
                on c.customer_code = d.customer_code
                left join mst_customer AS cus2 ON  cus2.customer_code = c.shipping_code
                left join mst_delivery  as f on concat(TRIM(c.ec_order_no), '-', TRIM(c.ec_order_lineno)) = TRIM(f.order_no)
                where {$condition} and c.shipping_no = ? and a.ec_order_no = ? and delete_flg <> 0
            ";

        $param = [];
        $param[] = $customer_code;
        $param[] = $shipping_no;
        $param[] = $order_no;

        if ($type == 'one') {
            $sql .= ' and b.jan_code = ? ';
            $param[] = $jan_code;
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

    public function getShipListExtend($otodoke_code, $shipping_code)
    {
        $sql = ' SELECT
                    (SELECT company_name  FROM mst_customer ccc WHERE ccc.customer_code= ?) as shipping_company_name
                    ,(SELECT company_name  FROM mst_customer ccc WHERE ccc.customer_code= ?) as otodoke_company_name

                ';

        $param = [$shipping_code, $otodoke_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            if (count($rows) == 0) {
                $rows[] = ['shipping_company_name' => '', 'otodoke_company_name' => ''];
            }

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getShipListExtendBk($order_no)
    {
        $sql = ' SELECT m.seikyu_code,m.pre_order_id,
                    m.otodoke_code
                    ,(SELECT company_name  FROM mst_customer ccc WHERE ccc.customer_code= m.shipping_code) as shipping_company_name
                    ,(SELECT company_name  FROM mst_customer ccc WHERE ccc.customer_code= m.otodoke_code) as otodoke_company_name
                     FROM more_order m  WHERE pre_order_id IN(
                    SELECT pre_order_id FROM dtb_order WHERE id=?)
                ';
        $param = [];

        $param[] = $order_no;
        //var_dump($sql,$param);

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            if (count($rows) == 0) {
                $rows[] = ['shipping_company_name' => '', 'otodoke_company_name' => ''];
            }

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param MoreOrder $moreOrder
     */
    public function getMstShippingCustomer($loginType, $customerId, MoreOrder $moreOrder = null)
    {
        $column = '
                            mc.customer_code as shipping_no,
                            dcur.shipping_code,
                            mc.ec_customer_id,
                            mc.company_name as name01,
                            mc.company_name,
                            mc.company_name_abb,
                            mc.department,
                            mc.postal_code,
                            mc.addr01,
                            mc.addr02,
                            mc.addr03,
                            mc.email,
                            mc.phone_number,
                            mc.create_date,
                            mc.update_date
                        ';
        $shipping_code = $_SESSION['s_shipping_code'] ?? '';
        $param = [];
        $param[] = $customerId;

        if ($loginType == 'represent_code' || $loginType == 'customer_code') {
            $sql = " SELECT
                                $column
                            FROM
                                dtb_customer dc
                            JOIN
                                mst_customer mc
                            ON
                                dc.id = mc.ec_customer_id
                            JOIN
                                (SELECT
                                    dcr.shipping_code
                                from
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.customer_code = ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id = ?  LIMIT 1 )
                                GROUP BY
                                    dcr.shipping_code
                                ) AS dcur
                            ON
                                mc.customer_code = dcur.shipping_code
                        ";
        } elseif ($loginType == 'shipping_code') {
            $sql = " SELECT
                                $column
                            FROM
                                dtb_customer dc
                            JOIN
                                mst_customer mc
                            ON
                                dc.id = mc.ec_customer_id
                            JOIN
                                (SELECT
                                    dcr.shipping_code
                                from
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.shipping_code = ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id = ?  LIMIT 1 )
                                GROUP BY
                                    dcr.shipping_code
                                ) AS dcur
                            ON
                                mc.customer_code = dcur.shipping_code
                        ";
        } elseif ($loginType == 'otodoke_code') {
            $sql = " SELECT
                                $column
                            FROM
                                dtb_customer dc
                            JOIN
                                mst_customer mc
                            ON
                                dc.id = mc.ec_customer_id
                            JOIN
                                (SELECT
                                    dcr.shipping_code
                                from
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.otodoke_code = ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id = ?  LIMIT 1 )
                                GROUP BY
                                    dcr.shipping_code
                                ) AS dcur
                            ON
                                mc.customer_code = dcur.shipping_code
                        ";
        } elseif ($loginType == 'change_type'
            && $shipping_code != '') {
            $param = [];

            $sql = " SELECT
                                $column
                            FROM
                                dtb_customer dc
                            JOIN
                                mst_customer mc
                            ON
                                dc.id = mc.ec_customer_id
                            JOIN
                                (SELECT
                                    dcr.shipping_code
                                from
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.shipping_code = '{$shipping_code}'
                                GROUP BY
                                    dcr.shipping_code
                                ) AS dcur
                            ON
                                mc.customer_code = dcur.shipping_code
                        ";
        } else {
            $sql = " SELECT
                                $column
                            FROM
                                dtb_customer dc
                            JOIN
                                mst_customer mc
                            ON
                                dc.id = mc.ec_customer_id
                            JOIN
                                (SELECT
                                    dcr.shipping_code
                                from
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.customer_code = ( SELECT customer_code  FROM  mst_customer WHERE ec_customer_id = ?  LIMIT 1 )
                                GROUP BY
                                    dcr.shipping_code
                                ) AS dcur
                            ON
                                mc.customer_code = dcur.shipping_code
                        ";
        }

        if (null != $moreOrder) {
            $sql .= ' WHERE dcur.shipping_code = ? ';
            $param[] = $moreOrder->getShippingCode();
        }

        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return [];
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
        $subWhere = '';
        $c = count($myCart);
        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhere .= '?';
            } else {
                $subWhere .= '?,';
            }
        }
        if (count($myCart) == 0) {
            return [];
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
        $param = $myCart;
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
    public function getdtPriceFromCart($myCart, $customer_code)
    {
        $subWhere = '';
        $c = count($myCart);
        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhere .= '?';
            } else {
                $subWhere .= '?,';
            }
        }
        if (count($myCart) == 0) {
            return [];
        }

        $sql = " SELECT b.id,c.product_code,c.unit_price,c.ec_product_id,dtPrice.price_s01,dtPrice.tanka_number
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
                JOIN mst_product AS c ON a.product_id = c.ec_product_id
               LEFT join dt_price AS dtPrice ON dtPrice.product_code = c.product_code
                WHERE b.cart_id in({$subWhere}) and dtPrice.customer_code ='{$customer_code}' ";
        $param = [];
        $param = $myCart;
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getPriceFromDtPriceOfCus($customer_code = '')
    {
        $arR = [];
        if ($customer_code == '') {
            return [];
        }

        //pri.customer_code = pri.shipping_no cho giao hang phai giong de co gia tot
        $sql = "select pri.product_code,pri.customer_code  from dt_price pri
                WHERE pri.customer_code=?
                and DATE_FORMAT(NOW(),'%Y-%m-%d')>= pri.valid_date   AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= pri.expire_date

                GROUP BY pri.product_code,pri.customer_code
                HAVING COUNT(*)=1
                ; ";

        $param = [$customer_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            foreach ($rows as $item) {
                $arR[] = $item['product_code'];
            }

            return $arR;
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return [];
        }
    }

    public function getPriceFromDtPriceOfCusV2($customer_code = '', $arProductCode = [])
    {
        $arR = [];
        $arRTana = [];

        if ($customer_code == '') {
            return [[], []];
        }

        $param = [$customer_code];

        $subWhere = '';
        $c = count($arProductCode);

        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhere .= '?';
            } else {
                $subWhere .= '?,';
            }

            $param[] = $arProductCode[$i];
        }

        $subQuereAdd = '';
        if ($c > 0) {
            $subQuereAdd = "and pri.product_code in({$subWhere})";
        }

        //pri.customer_code = pri.shipping_no cho giao hang phai giong de co gia tot
        $sql = "select
                    DISTINCT pri.product_code,
                    MAX(pri.tanka_number) AS max_tanka_number
                FROM
                    dt_price pri
                WHERE
                    pri.customer_code=?
                AND
                    DATE_FORMAT(NOW(),'%Y-%m-%d') >= pri.valid_date
                AND
                    DATE_FORMAT(NOW(),'%Y-%m-%d') <  DATE_SUB(pri.expire_date, INTERVAL 1 DAY)
                {$subQuereAdd}
                GROUP BY
                    product_code
                ORDER BY
                    pri.tanka_number ASC
            ";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            foreach ($rows as $item) {
                $arR[] = $item['product_code'];
                $arRTana[] = $item['max_tanka_number'];
            }

            return [$arR, $arRTana];
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return [[], []];
        }
    }

    public function getPriceFromDtPriceTankaProductCode($arTanka, $arProCode, $customer_code)
    {
        $arR = [];

        if ($customer_code == '') {
            return [[], []];
        }
        $param = [$customer_code];

        $subWhereTanka = '';
        $c = count($arTanka);
        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhereTanka .= '?';
            } else {
                $subWhereTanka .= '?,';
            }
            $param[] = $arTanka[$i];
        }
        $subWhereProductCode = '';
        $c = count($arProCode);
        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhereProductCode .= '?';
            } else {
                $subWhereProductCode .= '?,';
            }
            $param[] = $arProCode[$i];
        }
        //pri.customer_code = pri.shipping_no cho giao hang phai giong de co gia tot
        $sql = "select pri.product_code,price_s01 from dt_price pri
                WHERE pri.customer_code=?
                and DATE_FORMAT(NOW(),'%Y-%m-%d')>= pri.valid_date   AND DATE_FORMAT(NOW(),'%Y-%m-%d') <  DATE_SUB(pri.expire_date, INTERVAL 1 DAY)

                and pri.tanka_number in ({$subWhereTanka}) and pri.product_code in ({$subWhereProductCode})

                GROUP BY product_code

                ORDER BY pri.tanka_number asc
                ; ";

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            foreach ($rows as $item) {
                $arR[$item['product_code']] = $item['price_s01'];
            }

            return $arR;
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return [];
        }
    }

    public function updateCartItem($hsPrice, $arCarItemId, $Cart)
    {
        $objList = $this->entityManager->getRepository(CartItem::class)->findBy(['Cart' => $Cart]);
        $totalPrice = 0;

        foreach ($objList as $carItem) {
            if (isset($hsPrice[$carItem->getId()])) {
                $carItem->setPrice($hsPrice[$carItem->getId()]);
                $this->entityManager->persist($carItem);
                $this->entityManager->flush();
            }

            $totalPrice += $carItem->getPrice() * $carItem->getQuantity();
        }

        $obC = $this->entityManager->getRepository(Cart::class)->findOneBy(['id' => $Cart->getId()]);

        if ($obC != null) {
            $obC->setTotalPrice($totalPrice);
            $this->entityManager->persist($obC);
            $this->entityManager->flush();
        }
    }

    /**
     * @param
     */
    public function getMstProductsFromCart($myCart)
    {
        $subWhere = '';
        $c = count($myCart);
        for ($i = 0; $i < $c; $i++) {
            if ($i == $c - 1) {
                $subWhere .= '?';
            } else {
                $subWhere .= '?,';
            }
        }
        if (count($myCart) == 0) {
            return [];
        }

        $sql = " SELECT c.*,b.quantity as car_quantity,a.product_id as my_product_id
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
                JOIN mst_product AS c ON a.product_id = c.ec_product_id
                WHERE b.cart_id in({$subWhere}) ";
        $param = [];
        $param = $myCart;
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
    public function getCustomerBillSeikyuCode($customer_code, $login_type = '', $login_code = '')
    {
        $newComs = new MyCommonService($this->entityManager);
        $relationCus = $newComs->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

        if ($relationCus) {
            $seikyu_code = $relationCus['seikyu_code'];
        }

        if (empty($seikyu_code)) {
            return [];
        }

        $column = '
                    a.customer_code as seikyu_code,
                    ec_customer_id,
                    company_name as name01,
                    company_name,
                    company_name_abb,
                    department,
                    postal_code,
                    addr01,
                    addr02,
                    addr03,
                    email,
                    phone_number
                ';

        //seikyu_code  noi nhan hoa don
        $sql = "SELECT
                    {$column}
                FROM
                    mst_customer a
                WHERE
                    a.customer_code = ?
                LIMIT 1
                ";

        try {
            $myPara = [$seikyu_code];
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($myPara);
            $rows = $result->fetchAllAssociative();

            return $rows ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /***
     * Otodoke  nhan hang hoa
     * @param $customer_id
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCustomerOtodoke($loginType, $customer_id, $shipping_code, $moreOrder = null)
    {
        $column = '
                            mc.customer_code as otodoke_code,
                            mc.ec_customer_id,
                            mc.company_name as name01,
                            mc.company_name,
                            mc.company_name_abb,
                            mc.department,
                            mc.postal_code,
                            mc.addr01,
                            mc.addr02,
                            mc.addr03,
                            mc.email,
                            mc.phone_number
                        ';

        $sql = " SELECT
                                {$column}
                            FROM mst_customer mc
                            join
                                (SELECT
                                    dcr.otodoke_code
                                FROM
                                    dt_customer_relation dcr
                                WHERE
                                    dcr.shipping_code = ?
                                AND
                                    dcr.otodoke_code is not NULL
                                AND
                                    dcr.otodoke_code <> ''
                                ) AS dcur
                            ON
                                dcur.otodoke_code = mc.customer_code
                    ";

        $myPara = [];
        $myPara[] = $shipping_code;

        if ($loginType == 'otodoke_code') {
            $sql .= ' AND dcur.otodoke_code = (select customer_code from mst_customer where ec_customer_id = ?) ';
            $myPara[] = $customer_id;
        }

        if ($moreOrder != null) {
            $sql .= ' AND dcur.otodoke_code = ? ';
            $myPara[] = $moreOrder->getOtodokeCode();
        }

        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function getPdfDelivery($delivery_no, $orderNo = '')
    {
        $subQuantity = ' CASE
                            WHEN m1_.quantity > 1 THEN m1_.quantity * m0_.quanlity
                            ELSE m0_.quanlity
                            END AS quanlity
                        ';

        $subUnitPrice = '   CASE
                            WHEN m1_.quantity > 1 THEN m0_.unit_price / m1_.quantity
                            ELSE m0_.unit_price
                            END AS unit_price
                       ';

        $addCondition = '';

        if (!empty($orderNo)) {
            $addCondition = ' and m0_.order_no LIKE ? ';
        }

        $sql = "
                        SELECT
                            {$subUnitPrice},
                            {$subQuantity},
                            SUBSTRING(m0_.order_no, POSITION(\"-\" IN m0_.order_no)+1) AS orderByAs,
                            m0_.delivery_no,
                            m0_.delivery_date,
                            m0_.deli_post_code,
                            m0_.deli_addr01,
                            m0_.deli_addr02,
                            m0_.deli_addr03,
                            m0_.deli_company_name,
                            m0_.deli_department,
                            m0_.postal_code,
                            m0_.addr01 ,
                            m0_.addr02,
                            m0_.addr03,
                            m0_.company_name,
                            m0_.department,
                            m0_.delivery_lineno,
                            m0_.sale_type,
                            m1_.jan_code as item_no,
                            m0_.item_name,
                            'PC' as unit,
                            m0_.amount,
                            m0_.tax,
                            m0_.order_no,
                            m0_.item_remark,
                            m0_.total_amount,
                            m0_.footer_remark1,
                            m0_.shiping_name as shiping_code,
                            m0_.otodoke_name  as otodoke_code,
                            m2_.department as deli_department_name,
                            m0_.shipping_no
                         FROM
                            mst_delivery m0_
                         LEFT JOIN
                            mst_customer m2_ ON (m2_.customer_code = m0_.deli_department)
                         LEFT JOIN
                            mst_product m1_ ON (m1_.product_code = m0_.item_no)
                    WHERE
                        m0_.delivery_no = ? {$addCondition}
                    ORDER BY
                        CONVERT(orderByAs, SIGNED INTEGER) ASC";

        $myPara = [$delivery_no];

        if (!empty($orderNo)) {
            $myPara[] = $orderNo.'-%';
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
            $ec_order_lineno = $cusOrderLineno; //$itemSave['ec_order_lineno'];
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
            $orderItem->setInquiryNo($ec_order.'-'.$ec_order_lineno);
            $orderItem->setShippingCompanyCode('');
            $orderItem->setOrderNo($ec_order);
            $orderItem->setOrderLineno($ec_order_lineno);
            $orderItem->setCusOrderNo($ec_order);
            $orderItem->setCusOrderLineno($ec_order_lineno);
            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setShippingCode($itemSave['shipping_code']);
            $orderItem->setProductCode($itemSave['product_code']);
            $orderItem->setShippingPlanDate($itemSave['shipping_plan_date'] ?? '');

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
            $ec_order_lineno = $cusOrderLineno; //$itemSave['ec_order_lineno'];
            $keyFind = ['order_no' => $ec_order, 'order_lineno' => $ec_order_lineno];
            $objRep = $this->entityManager->getRepository(DtOrder::class)->findOneBy($keyFind);
            $orderItem = new DtOrder();

            if ($objRep !== null) {
                $orderItem = $objRep;
            }

            $orderItem->setOrderLineno($ec_order_lineno);
            $orderItem->setOrderNo($ec_order);
            $orderItem->setShippingCode($itemSave['shipping_code']);
            $orderItem->setSeikyuCode($itemSave['seikyu_code'] ?? '');
            $orderItem->setShipingPlanDate($itemSave['shipping_plan_date'] ?? '');
            $orderItem->setRequestFlg('Y');
            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setProductCode($itemSave['product_code']);
            $orderItem->setOtodokeCode($itemSave['otodoke_code']);
            $orderItem->setOrderPrice($itemSave['order_price']);
            $orderItem->setDemandQuantity($itemSave['demand_quantity']);

            // No41 注文情報送信I/F start
            $time = new \DateTime();
            $orderItem->setOrderDate($time);
            // ・受注日←受注日(購入日)
            if (!is_null($itemSave['deli_plan_date'])) {
                $orderItem->setDeliPlanDate($itemSave['deli_plan_date']);                                       // ・希望納期（納入予定日）←配送日指定
            }

            $orderItem->setItemNo($itemSave['item_no'] ?? '');                                                    // ・客先品目No←JANコード
            $orderItem->setDemandUnit($itemSave['demand_unit']);                                            // ・需要単位←商品情報の入り数が‘1’の場合、‘PC’、入り数が‘1’以外の場合、‘CS’
            $orderItem->setDynaModelSeg2($itemSave['dyna_model_seg2']);                                     // ・ダイナ規格セグメント02←EC注文番号
            $orderItem->setDynaModelSeg3($itemSave['dyna_model_seg3']);
            $orderItem->setDynaModelSeg4($itemSave['dyna_model_seg4']);                                     // ・ダイナ規格セグメント04←EC注文番号
            $orderItem->setDynaModelSeg5($ec_order_lineno);                                                 // ・ダイナ規格セグメント05←EC注文明細番号
            $orderItem->setDynaModelSeg6($itemSave['remarks1']);                                     // ・ダイナ規格セグメント04←EC注文番号
            $orderItem->setDynaModelSeg7($itemSave['remarks2']);                                     // ・ダイナ規格セグメント04←EC注文番号
            $orderItem->setDynaModelSeg8($itemSave['remarks3']);
            $orderItem->setDynaModelSeg9($itemSave['remarks4']);
            $orderItem->setUnitPriceStatus('FOR');
            $orderItem->setDeploy('XB');
            $orderItem->setCompanyId('XB');
            $orderItem->setShipingDepositCode($itemSave['location']);

            // No41 注文情報送信I/F end
            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }

    public function saveOrderStatus($arEcLData)
    {
        //dt_order_status
        //$arEcLData[] = ['ec_order_no'=>$orderNo,'ec_order_lineno'=>$itemOr->getId()];
        $cusOrderLineno = 0;
        $total = count($arEcLData);
        foreach ($arEcLData as $itemSave) {
            $cusOrderLineno = $total;
            $total--;
            $ec_order = $itemSave['ec_order_no'];
            $ec_order_lineno = $cusOrderLineno; //$itemSave['ec_order_lineno'];
            $keyFind = ['ec_order_no' => $ec_order, 'ec_order_lineno' => $ec_order_lineno];
            $objRep = $this->entityManager->getRepository(DtOrderStatus::class)->findOneBy($keyFind);
            $orderItem = new DtOrderStatus();

            if ($objRep !== null) {
                $orderItem = $objRep;
            } else {
                $orderItem->setOrderStatus('1');
            }
            // $orderItem->setPropertiesFromArray($keyFind,['create_date']);
            $time = new \DateTime();
            $orderItem->setOrderDate($time);
            $orderItem->setEcOrderLineno($ec_order_lineno);
            $orderItem->setEcOrderNo($ec_order);
            $orderItem->setOrderNo($ec_order);
            $orderItem->setOrderLineNo($ec_order_lineno);
            $orderItem->setCusOrderNo($ec_order);
            $orderItem->setCusOrderLineno($cusOrderLineno);
            $orderItem->setCustomerCode($itemSave['customer_code']);
            $orderItem->setShippingCode($itemSave['shipping_code']);
            $orderItem->setOtodokeCode($itemSave['otodoke_code']);
            $orderItem->setOrderRemainNum($itemSave['order_remain_num']);
            $orderItem->setProductCode($itemSave['product_code']);
            $orderItem->setRemarks1($itemSave['remarks1']);
            $orderItem->setRemarks2($itemSave['remarks2']);
            $orderItem->setRemarks3($itemSave['remarks3']);
            $orderItem->setRemarks4($itemSave['remarks4']);
            $orderItem->setEcType('1');

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
        $orderItem->setPropertiesFromArray(['date_want_delivery' => $date_want_delivery]);
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
        $sql = '
                SELECT
                    *
                FROM
				    dtb_tax_rule
			    ';

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
    public function getMstShippingOrder($customerId, $pre_order_id)
    {
        $sql = '
        SELECT mst_customer.*,mst_shipping.*
        FROM  mst_customer
        JOIN mst_shipping
        ON mst_shipping.customer_code = mst_customer.customer_code
        WHERE ec_customer_id=?
        AND mst_shipping.order_no = ?
        LIMIT 1
        ';
        $param = [$customerId, $pre_order_id];

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
        $sql = '
         SELECT
            a.id AS 	 order_no,
            a.customer_id customer_id,
            d.customer_code customer_code,
            b.product_id AS product_id,
            c.product_code AS product_code,
            c.product_name AS product_name,
            c.jan_code AS jan_code,
            c.quantity as mst_quantity,
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
         ';
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
    public function updateOrderNo($order_id, $paymentTotal)
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

    public function updatePaymentTotalOrder($order_id, $payment_total)
    {
        $sql = '
         update
             dtb_order
            set payment_total=?

            WHERE id = ?
         ';
        $param = [$payment_total, $order_id];

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeStatement($param);

            return $result;
        } catch (Exception $e) {
            log_info('updatePaymentTotalOrder '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param
     */
    public function getMoreOrderCustomer($pre_order_id)
    {
        $sql = '
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
         ';
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

    public function getPriceFromDtPriceOfCusProductcodeV2($customer_code = '', $productCode, $login_type = null, $login_code = null)
    {
        if ($customer_code == '') {
            return null;
        }

        $newComs = new MyCommonService($this->entityManager);
        $relationCus = $newComs->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

        if ($relationCus) {
            $customerCode = $relationCus['customer_code'];
            $shippingCode = $relationCus['shipping_code'];
            $params = [$customerCode];

            if (!empty($shippingCode)) {
                $addWhere = ' AND pri.shipping_no = ? ';
                $params[] = $shippingCode;
            } elseif (!empty($_SESSION['s_shipping_code'])) {
                $addWhere = ' AND pri.shipping_no = ? ';
                $params[] = $_SESSION['s_shipping_code'];
            } else {
                $addWhere = ' AND pri.shipping_no = ? ';
                $params[] = '';
            }

            $params[] = $productCode;

            $sql = "SELECT
                        price1.price_s01
                    FROM
                        dt_price price1
                    JOIN
                        (
                            SELECT
                                MAX(pri.tanka_number) as max_tanka_number, pri.product_code, pri.customer_code, pri.shipping_no
                            FROM
                                dt_price pri
                            WHERE
                                pri.customer_code = ?
                                {$addWhere}
                            AND
                                DATE_FORMAT(NOW(),'%Y-%m-%d') >= pri.valid_date
                            AND
                                DATE_FORMAT(NOW(),'%Y-%m-%d') <  DATE_SUB(pri.expire_date, INTERVAL 1 DAY)
                            AND
                                pri.product_code = ?
                            GROUP BY pri.product_code
                        ) as price2
                    ON
                        price1.tanka_number = price2.max_tanka_number
                    AND
                        price1.product_code = price2.product_code
                    AND
                        price1.customer_code = price2.customer_code
                    AND
                        price1.shipping_no = price2.shipping_no
                ";

            try {
                $statement = $this->entityManager->getConnection()->prepare($sql);
                $result = $statement->executeQuery($params);
                $rows = $result->fetchAllAssociative();

                return $rows[0] ?? null;
            } catch (\Exception $e) {
                log_info($e->getMessage());

                return null;
            }
        }

        return null;
    }

    public function getPriceFromDtPriceOfCusProductcode($customer_code = '', $productCode)
    {
        $arR = [];
        if ($customer_code == '') {
            return [];
        }

        $sql = "select pri.product_code,pri.customer_code,pri.price_s01,pri.valid_date
                 from dt_price pri
                 WHERE pri.customer_code=?
                    and DATE_FORMAT(NOW(),'%Y-%m-%d') >= pri.valid_date    AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= pri.expire_date
                    and pri.product_code=?

                    GROUP BY pri.product_code,pri.customer_code,pri.valid_date
                    HAVING COUNT(*)=1
                ; ";

        $param = [$customer_code, $productCode];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            if (count($rows) == 1) {
                return $rows[0]['price_s01'];
            }

            return '';
        } catch (\Exception $e) {
            log_info('getPriceFromDtPriceOfCusProductcode '.$e->getMessage());

            return '';
        }
    }

    public function getDayOff()
    {
        $sql = " SELECT  DATE_FORMAT( holiday,'%Y-%m-%d')  as holiday from dtb_calendar where holiday>now() order by holiday asc
                ; ";

        $param = [];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $arRe = [];

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            foreach ($rows as $item) {
                $arRe[] = $item['holiday'];
            }

            return $arRe;
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return '';
        }
    }

    public function checkExistPreOrder($preOrderId)
    {
        $sql = 'SELECT pre_order_id FROM `dtb_cart` WHERE pre_order_id =?';
        $myPara = [$preOrderId];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();
        if (count($rows) == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getTotalItemCart($cart_id)
    {
        //$sql = " SELECT count(b.quantity*c.quantity) AS total_quantity
        $sql = ' SELECT count(b.quantity) AS total_quantity
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
                JOIN mst_product AS c ON a.product_id = c.ec_product_id
                WHERE b.cart_id =? ';
        $param = [$cart_id];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            if (count($rows) > 0) {
                return $rows[0]['total_quantity'];
            }

            return 0;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getSearchProductName($productName)
    {
        $arrSpaceName = explode(' ', $productName);

        $myPara = [];
        $whereLike = '';
        if (count($arrSpaceName) > 0) {
            $arK = array_keys($arrSpaceName);
            $last_key = end($arK);
            foreach ($arrSpaceName as $key => $itemR) {
                $myPara[] = '%'.$itemR.'%';
                if ($key == $last_key) {
                    $whereLike .= ' a.product_name like ?  ';
                } else {
                    $whereLike .= ' a.product_name like ? and ';
                }
            }
        } else {
            $whereLike = ' a.product_name like ?  ';
            $myPara = ['%'.$productName.'%'];
        }
        $sql = 'SELECT jan_code FROM  mst_product a WHERE  '.$whereLike;

        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();
        $arrProductCode = [];
        foreach ($rows as $itemR) {
            $arrProductCode[] = $itemR['jan_code'];
        }

        return $arrProductCode;

//        $myPara = [ $productName];
//        $sql = "SELECT jan_code FROM  mst_product a WHERE  match(a.product_name )
//                 AGAINST( ? IN natural LANGUAGE MODE) >1 ";
//

//        $statement = $this->entityManager->getConnection()->prepare($sql);
//        $result = $statement->executeQuery($myPara);
//        $rows = $result->fetchAllAssociative();
//        if(count($rows)==0){
//            $sql = "SELECT jan_code FROM  mst_product a WHERE  a.product_name like ?";
//            $myPara = [ "%".$productName."%"];
//            $statement = $this->entityManager->getConnection()->prepare($sql);
//            $result = $statement->executeQuery($myPara);
//            $rows = $result->fetchAllAssociative();
//        }
    }

    public function getSearchProductNameKana($productNameKana)
    {
        $arrSpaceName = explode(' ', $productNameKana);

        $myPara = [];
        $whereLike = '';
        if (count($arrSpaceName) > 0) {
            $arK = array_keys($arrSpaceName);
            $last_key = end($arK);
            foreach ($arrSpaceName as $key => $itemR) {
                $myPara[] = '%'.$itemR.'%';
                if ($key == $last_key) {
                    $whereLike .= ' a.product_name_kana like ?  ';
                } else {
                    $whereLike .= ' a.product_name_kana like ? and ';
                }
            }
        } else {
            $whereLike = ' a.product_name_kana like ?  ';
            $myPara = ['%'.$productNameKana.'%'];
        }
        $sql = 'SELECT jan_code FROM  mst_product a WHERE  '.$whereLike;

        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();
        $arrProductCode = [];
        foreach ($rows as $itemR) {
            $arrProductCode[] = $itemR['jan_code'];
        }

        return $arrProductCode;
    }

    public function getSearchCatalogCode($catalog_code)
    {
        $sql = 'SELECT jan_code FROM  mst_product a WHERE  match(a.catalog_code )
                 AGAINST( ? IN natural LANGUAGE MODE) ';
        $myPara = [$catalog_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();
        $arrProductCode = [];
        foreach ($rows as $itemR) {
            $arrProductCode[] = $itemR['jan_code'];
        }

        return $arrProductCode;
    }

    public function getDataQuery($query, $param)
    {
        $sql = $query;
        $myPara = $param;
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function updateCartItemOne($oneCartId, $productClassId, $myQuantity)
    {
        $sql = 'update  dtb_cart_item SET quantity = ? where product_class_id = ? and cart_id = ?';
        $param = [$myQuantity, $productClassId, $oneCartId];
        $result = $this->entityManager->getConnection()->prepare($sql)->executeStatement($param);
        $this->entityManager->flush();

        $sqlGetTotal = "select sum(quantity * price) as totalPrice from  dtb_cart_item where cart_id = {$oneCartId}";
        $totalPrice = $this->runQuery($sqlGetTotal, [])[0]['totalPrice'];
        $sqlTotal = "update dtb_cart set total_price = '{$totalPrice}', pre_order_id = null, update_date = now() where id = {$oneCartId}";
        $result = $this->entityManager->getConnection()->prepare($sqlTotal)->executeStatement();

        return $result;
    }

    public function isProductEcIncart($keyCart, $ecProductId)
    {
        $sql = 'SELECT a.product_id
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
        AND    b.key_eccube=? where a.product_id=?';
        $returnData = $this->getDataQuery($sql, [$keyCart, $ecProductId]);
        if (count($returnData) == 1) {
            return 1;
        }

        return 0;
    }

    public function getCartInfo($keyCart, $ecProductId)
    {
        $sql = 'SELECT a.id AS productClassId,b.cart_id,a.product_id
                FROM  dtb_product_class AS a JOIN dtb_cart_item b ON b.product_class_id =a.id
        AND    b.key_eccube=? where a.product_id=?';
        $returnData = $this->getDataQuery($sql, [$keyCart, $ecProductId]);

        return $returnData;
    }

    /***
     * @param array $arProductCode
     * @param array $hsMstProductCodeCheckShow
     * @var MyCommonService $commonS
     * @var string $customer_code
     */
    public function setCartIndtPrice($hsMstProductCodeCheckShow, $commonS, $customer_code, $login_type = '', $login_code = '')
    {
        foreach ($hsMstProductCodeCheckShow as $keyCheck => $valueCheck) {
            $dtPrice = $commonS->getPriceFromDtPriceOfCusProductcodeV2($customer_code, $keyCheck, $login_type, $login_code);

            if ($dtPrice && $dtPrice['price_s01'] && $dtPrice['price_s01'] > 0) {
                $hsMstProductCodeCheckShow[$keyCheck] = 'good_price';
            }
        }

        return $hsMstProductCodeCheckShow;
    }

    /***
     * @param $shipping_code
     * @param $pre_order_id
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveTempCartRemarks($pre_order_id, $name = '', $value = '')
    {
        $objRep = $this->entityManager->getRepository(MoreOrder::class)->findOneBy(['pre_order_id' => $pre_order_id]);
        $orderItem = new MoreOrder();

        if ($objRep !== null) {
            $orderItem = $objRep;
        }

        if ($name == 'remarks1') {
            $orderItem->setPreOrderId($pre_order_id);
            $orderItem->setRemarks1($value);
        }

        if ($name == 'remarks2') {
            $orderItem->setPreOrderId($pre_order_id);
            $orderItem->setRemarks2($value);
        }

        if ($name == 'remarks3') {
            $orderItem->setPreOrderId($pre_order_id);
            $orderItem->setRemarks3($value);
        }

        if ($name == 'remarks4') {
            $orderItem->setPreOrderId($pre_order_id);
            $orderItem->setRemarks4($value);
        }

        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();
    }

    public function checkLoginType($login_code)
    {
        if (!empty($login_code) && str_starts_with($login_code, 'su')) {
            return 'supper_user';
        } elseif (!empty($login_code) && str_starts_with($login_code, 'c')) {
            return 'represent_code';
        } elseif (!empty($login_code) && str_starts_with($login_code, 's')) {
            return 'shipping_code';
        } elseif (!empty($login_code) && str_starts_with($login_code, 't')) {
            return 'otodoke_code';
        }

        return 'customer_code';
    }

    public function getCustomerByRepresentType($login_code)
    {
        $column = '
                        dtcur.represent_code,
                        dtcur.shipping_code as shipping_no,
                        mstcus.customer_code,
                        mstcus.ec_customer_id,
                        mstcus.company_name as name01,
                        mstcus.company_name,
                        mstcus.company_name_abb,
                        mstcus.department,
                        mstcus.postal_code,
                        mstcus.addr01,
                        mstcus.addr02,
                        mstcus.addr03,
                        dtcus.email,
                        mstcus.phone_number,
                        mstcus.create_date,
                        mstcus.update_date
                    ';

        $sql = "
                        SELECT $column
                        FROM
                            dt_customer_relation `dtcur`
                        JOIN
                            `mst_customer` `mstcus`
                        ON
                            (`mstcus`.`customer_code` = `dtcur`.`customer_code`)
                        JOIN
                            `dtb_customer` `dtcus`
                        ON
                            (`dtcus`.`id` = `mstcus`.`ec_customer_id`)
                        WHERE
                            `dtcur`.`represent_code` = ?
                    ";

        $param = [];
        $param[] = $login_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCustomerByShippingType($login_code)
    {
        $column = '
                        dtcur.represent_code,
                        dtcur.shipping_code as shipping_no,
                        mstcus.customer_code,
                        mstcus.ec_customer_id,
                        mstcus.company_name as name01,
                        mstcus.company_name,
                        mstcus.company_name_abb,
                        mstcus.department,
                        mstcus.postal_code,
                        mstcus.addr01,
                        mstcus.addr02,
                        mstcus.addr03,
                        dtcus.email,
                        mstcus.phone_number,
                        mstcus.create_date,
                        mstcus.update_date
                    ';

        $sql = "
                        SELECT $column
                        FROM
                            dt_customer_relation `dtcur`
                        JOIN
                            `mst_customer` `mstcus`
                        ON
                            (`mstcus`.`customer_code` = `dtcur`.`shipping_code`)
                        JOIN
                            `dtb_customer` `dtcus`
                        ON
                            (`dtcus`.`id` = `mstcus`.`ec_customer_id`)
                        WHERE
                            `dtcur`.`represent_code` = ?
                    ";

        $param = [];
        $param[] = $login_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCustomerByOtodokeType($login_code)
    {
        $column = '
                        dtcur.represent_code,
                        dtcur.shipping_code as shipping_no,
                        mstcus.customer_code,
                        mstcus.ec_customer_id,
                        mstcus.company_name as name01,
                        mstcus.company_name,
                        mstcus.company_name_abb,
                        mstcus.department,
                        mstcus.postal_code,
                        mstcus.addr01,
                        mstcus.addr02,
                        mstcus.addr03,
                        dtcus.email,
                        mstcus.phone_number,
                        mstcus.create_date,
                        mstcus.update_date
                    ';

        $sql = "
                        SELECT $column
                        FROM
                            dt_customer_relation `dtcur`
                        JOIN
                            `mst_customer` `mstcus`
                        ON
                            (`mstcus`.`customer_code` = `dtcur`.`otodoke_code`)
                        JOIN
                            `dtb_customer` `dtcus`
                        ON
                            (`dtcus`.`id` = `mstcus`.`ec_customer_id`)
                        WHERE
                            `dtcur`.`represent_code` = ?
                    ";

        $param = [];
        $param[] = $login_code;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getShippingRouteFromUser($customer_code = '', $login_type = '')
    {
        $where = '';
        switch ($login_type) {
            case 'shipping_code':
                $where = ' cr.shipping_code  = :customerCode ';
                break;
            case 'otodoke_code':
                $where = ' cr.otodoke_code  = :customerCode ';
                break;
            case 'represent_code':
            case 'customer_code':
            case 'change_type':
            default:
                $where = ' cr.customer_code  = :customerCode ';
                break;
        }
        $sql = "SELECT
                sr.customer_code, sr.stock_location
            FROM
                `mst_shipping_route` sr
            JOIN dt_customer_relation cr on cr.customer_code = sr.customer_code
            WHERE
                {$where}
            GROUP BY cr.customer_code;";

        $statement = $this->entityManager->getConnection()->prepare($sql);
        try {
            $result = $statement->executeQuery(['customerCode' => $customer_code]);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCustomerRelationFromUser($customer_code = '', $login_type = '', $login_code = '')
    {
        switch ($login_type) {
            case 'shipping_code':
                $where = ' represent_code = :loginCode and shipping_code  = :customerCode ';
                $param = [
                    'customerCode' => $customer_code,
                    'loginCode' => $login_code,
                ];
                break;

            case 'otodoke_code':
                $where = ' represent_code = :loginCode and otodoke_code  = :customerCode ';
                $param = [
                    'customerCode' => $customer_code,
                    'loginCode' => $login_code,
                ];
                break;

            case 'change_type':
            case 'represent_code':
                $where = ' represent_code = :loginCode and customer_code  = :customerCode ';
                $param = [
                    'customerCode' => $customer_code,
                    'loginCode' => $login_code,
                ];
                break;

            case 'customer_code':
            default:
                $where = ' customer_code  = :customerCode ';
                $param = [
                    'customerCode' => $customer_code,
                ];
                break;
        }

        $sql = "SELECT
                    represent_code,
                    customer_code,
                    seikyu_code,
                    shipping_code,
                    otodoke_code
                FROM dt_customer_relation
                WHERE
                    {$where}
            ";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param
     */
    public function getCustomerLocation($customer_code)
    {
        $sql = '
                SELECT
                    *
                FROM
				    mst_shipping_route
                WHERE
                    customer_code = ?
			    ';

        $param = [$customer_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows[0]['stock_location'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getRelationCustomerCode($customerCode, $loginType = 'customer_code')
    {
        if ($loginType == 'represent_code' || $loginType == 'customer_code' || $loginType == 'change_type') {
            return $customerCode;
        } elseif ($loginType == 'shipping_code') {
            $condition = ' shipping_code = ? ';
        } elseif ($loginType == 'otodoke_code') {
            $condition = ' otodoke_code = ? ';
        } else {
            return $customerCode;
        }

        $sql = "
                                SELECT
                                    customer_code
                                FROM
                                    dt_customer_relation
                                WHERE
                                    {$condition}
                                LIMIT 1
                            ";

        $param = [];
        $param[] = $customerCode;
        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows[0]['customer_code'] ?? $customerCode;
        } catch (Exception $e) {
            return $customerCode;
        }
    }

    public function getCustomerRelation($represent_code = '')
    {
        if (empty($represent_code)) {
            return null;
        }

        $sql = "
                SELECT
                    c.ec_customer_id AS customer_id,
                    CASE
                        WHEN ( LEFT ( cr.represent_code, 1 ) = 't' ) THEN cr.otodoke_code
                        WHEN ( LEFT ( cr.represent_code, 1 ) = 's' ) THEN cr.shipping_code
                        ELSE cr.customer_code
                    END AS customer_code
                FROM
                    dt_customer_relation AS cr
                JOIN
                    mst_customer AS c
                ON
                    c.customer_code = (
                        CASE
                            WHEN ( LEFT ( cr.represent_code, 1 ) = 't' ) THEN cr.otodoke_code
                            WHEN ( LEFT ( cr.represent_code, 1 ) = 's' ) THEN cr.shipping_code
                            ELSE cr.customer_code
                        END
                    )
                WHERE
                    cr.represent_code = ?
                LIMIT 1
            ";

        try {
            $param = [$represent_code];
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($param);
            $row = $result->fetchAllAssociative();

            return $row[0] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getOrderStatus($login_code = '', $login_type = '')
    {
        if (empty($login_code)) {
            return null;
        }

        switch ($login_type) {
            case 'shipping_code':
                $condition = ' os.shipping_code = ? ';
                break;

            case 'otodoke_code':
                $condition = ' os.otodoke_code = ? ';
                break;

            default:
                $condition = ' os.customer_code = ? ';
                break;
        }

        $sql = "
                    SELECT DISTINCT
                        os.order_no,
                        os.order_line_no,
                        os.cus_order_no,
                        os.cus_order_lineno
                    FROM
                        dt_order_status os
                    WHERE
                        {$condition}
                    ORDER BY
                        os.cus_order_no ASC,
                        os.cus_order_lineno ASC;
                ";

        try {
            $params = [$login_code];
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($params);
            $row = $result->fetchAllAssociative();

            return $row ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getListRepresent()
    {
        $sql = "
                SELECT
                    a.represent_code,
                    c.id,
                    b.customer_code,
                    b.company_name,
                    b.postal_code,
                    b.addr01,
                    b.addr02,
                    b.addr03
                FROM
                    dt_customer_relation AS a
                    JOIN mst_customer b ON
                    b.customer_code = ( CASE
                        WHEN LEFT( a.represent_code, 1 ) = 't' THEN a.otodoke_code
                        WHEN LEFT( a.represent_code, 1 ) = 's' THEN a.shipping_code
                        ELSE a.customer_code
                    END )
                    JOIN dtb_customer AS c ON c.id = b.ec_customer_id
                WHERE
                    a.represent_code IS NOT NULL
                AND
                    a.represent_code <> ''
                AND
	                LEFT( a.represent_code, 2 ) <> 'su'
            ";

        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery();
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDtbCustomer($customer_id)
    {
        $objRep = $this->entityManager->getRepository(Customer::class)->findOneBy(['id' => $customer_id]);

        return $objRep;
    }

    /**
     * Get dt_price
     *
     * @param $product_code
     * @param $customer_code
     * @param $shipping_code
     *
     * @return array|mixed
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDtPrice($product_code, $customer_code, $shipping_code)
    {
        $sql = "
            SELECT dp.*
            FROM dt_price dp
            WHERE dp.product_code = ?
            AND dp.customer_code = ?
            AND dp.shipping_no = ?
            AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= dp.valid_date
            AND DATE_FORMAT(NOW(),'%Y-%m-%d') <  DATE_SUB(dp.expire_date, INTERVAL 1 DAY)
            ORDER BY dp.tanka_number DESC
            LIMIT 1
        ";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery([$product_code, $customer_code, $shipping_code]);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get dt_customer_relation
     *
     * @param $customer_code
     * @param $shipping_code
     * @param $otodoke_code
     *
     * @return array|mixed
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDtCustomerRelation($customer_code, $shipping_code, $otodoke_code)
    {
        $sql = '
            SELECT dcr.*
            FROM dt_customer_relation dcr
            WHERE dcr.customer_code = ?
            AND dcr.shipping_code = ?
            AND dcr.otodoke_code = ?
            LIMIT 1
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery([$customer_code, $shipping_code, $otodoke_code]);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getReturnsReson()
    {
        $sql = "SELECT `returns_reson_id`, `returns_reson` FROM `dt_returns_reson`";

        $statement      = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result     = $statement->executeQuery();
            $rows       = $result->fetchAllAssociative();
            return $rows;

        } catch (Exception $e) {
            return [];
        }
    }

    public function getJanCodeToProductCode( $jan_code = '' )
    {
        $sql = "SELECT `product_code` FROM `mst_product` WHERE `jan_code` = :jan_code limit 1";

        try {
            $statement      = $this->entityManager->getConnection()->prepare($sql);
            $result         = $statement->executeQuery([ 'jan_code'=>$jan_code ]);
            $row            = $result->fetchAllAssociative();

            return @$row[0]['product_code'];
        } catch (Exception $e) {
        }

        return null;
    }
    
    public function getJanCodeToProductName( $jan_code = '' ) {
        $sql = "SELECT `product_name` FROM `mst_product` WHERE `jan_code` = :jan_code limit 1";

        try {
            $statement      = $this->entityManager->getConnection()->prepare($sql);
            $result         = $statement->executeQuery([ 'jan_code'=>$jan_code ]);
            $row            = $result->fetchAllAssociative();

            return @$row[0]['product_name'];
        } catch (Exception $e) {
        }

        return null;
    }

    public function getDeliveredNum(  $shipping_no='', $product_code=''  ) {
        $result = 0;
        if( !$shipping_no || !$product_code ) return $result;

        $sql = "SELECT 
                SUM( `shipping_num` ) AS sum_shipping_num
            FROM `mst_shipping`
            WHERE
                `shipping_no` = :shipping_no
                AND `product_code` = :product_code
                AND `shipping_status` = 2
            GROUP BY shipping_no, product_code";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $query     = $statement->executeQuery([ 'shipping_no'=>$shipping_no, 'product_code'=>$product_code ]);
            $row       = $query->fetchAllAssociative();

            foreach($row as $dt) {
                $result += (int)$dt['sum_shipping_num'];
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    public function getReturnedNum(  $shipping_no='', $product_code='', $returns_no=''  ) {
        $result = 0;
        if( !$shipping_no || !$product_code ) return $result;

        $param = [ 'shipping_no'=>$shipping_no, 'product_code'=>$product_code ];
        $where = "";
        if( !empty($returns_no) ) {
            $where = "AND returns_no <> :returns_no";
            $param['returns_no'] = $returns_no;
        }

        $sql = "SELECT SUM( `returns_num` ) AS sum_returns_num
            FROM `mst_product_returns_info`
            WHERE
                `shipping_no` = :shipping_no
                AND `product_code` = :product_code
                {$where}
            GROUP BY shipping_no, product_code";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $query     = $statement->executeQuery($param);
            $row       = $query->fetchAllAssociative();

            foreach($row as $dt) {
                $result += (int)$dt['sum_returns_num'];
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    public function getReturnsNo()
    {
        $sql = "SELECT MAX(`returns_no`) AS `max_returns_no` FROM `mst_product_returns_info`";

        try {
            $statement      = $this->entityManager->getConnection()->prepare($sql);
            $result         = $statement->executeQuery();
            $row            = $result->fetchAllAssociative();

            $max_returns_no = (int)@$row[0]['max_returns_no'];
            $max_returns_no = $max_returns_no > 1000 ? $max_returns_no + 1 : 1001;
            return (string)$max_returns_no;
        } catch (Exception $e) {
        }

        return null;
    }

    /**
     * Get mst_delivery
     *
     * @param $shipping_no
     * @param $order_no
     * @param $order_line_no
     * @return array|mixed
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getMstDelivery($shipping_no, $order_no, $order_line_no)
    {
        $sql = '
            SELECT md.*
            FROM mst_delivery md
            WHERE md.shipping_no  = ?
            AND TRIM(md.order_no) = ?
            LIMIT 1
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery([$shipping_no, trim($order_no).'-'.trim($order_line_no)]);
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDtExportCsv()
    {
        $sql = '
                SELECT * FROM dt_export_csv dec2
                WHERE  dec2.file_name IS  NOT NULL
                ORDER BY dec2.id DESC
                LIMIT 1
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery();
            $rows = $result->fetchAllAssociative();

            return $rows[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
}
