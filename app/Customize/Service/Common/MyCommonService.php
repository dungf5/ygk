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
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;
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
                    a.price_view_flg,
                    a.fusrdec1,
                    a.pdf_export_flg,
                    a.fusrstr8         
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
        $column = 'customer_code as shipping_no,customer_code, ec_customer_id,customer_name, company_name as name01, company_name, company_name_abb, department, postal_code, addr01, addr02, addr03, email, phone_number, create_date, update_date, fusrdec1';
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
                    b.product_name,
                    b.quantity,
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
                left join mst_delivery  as f on concat(TRIM(c.cus_order_no), '-', TRIM(c.cus_order_lineno)) = TRIM(f.order_no)
                where {$condition} and c.shipping_no = ? and a.cus_order_no = ? and delete_flg <> 0
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

    public function getMstShippingCustomer($loginType, $customerId)
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

        $statement = $this->entityManager->getConnection()->prepare($sql);

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            return $rows;
        } catch (Exception $e) {
            return [];
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

    public function getCustomerBillSeikyuCode($customer_code, $login_type = '', $login_code = '')
    {
        $newComs = new MyCommonService($this->entityManager);
        $relationCus = $newComs->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

        if ($relationCus) {
            $seikyu_code = $relationCus['seikyu_code'];
        }

        if ($login_code == 'c1018' && !empty($shipping_code)) {
            $seikyu_code = $shipping_code;
        }

        if (empty($seikyu_code)) {
            return [];
        }

        $column = '
                    a.customer_code,
                    ec_customer_id,
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

            return $rows[0] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

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

    public function getDeliveryNoPrintPDF($customer_code, $login_type, $params_search)
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'dt_order_status.shipping_code = ?';
                break;
            case 'otodoke_code':
                $condition = 'dt_order_status.otodoke_code = ?';
                break;
            default:
                $condition = 'dt_order_status.customer_code = ?';
                break;
        }
        $myPara = [$customer_code];
        $date_from_condition = '';
        if (!empty($params_search['search_shipping_date_from'])) {
            $date_from_condition = "AND DATE_FORMAT(mst_delivery.delivery_date,'%Y-%m-%d') >= ?";
            $myPara[] = $params_search['search_shipping_date_from'];
        }
        $date_to_condition = '';
        if (!empty($params_search['search_shipping_date_to'])) {
            $date_to_condition = "AND DATE_FORMAT(mst_delivery.delivery_date,'%Y-%m-%d') <= ?";
            $myPara[] = $params_search['search_shipping_date_to'];
        }
        $shipping_date_condition = '';
        if (!empty($params_search['search_shipping_date'])) {
            $shipping_date_condition = 'AND mst_delivery.delivery_date like ?';
            $myPara[] = $params_search['search_shipping_date'].'-%';
        }
        $order_shipping_condition = '';
        if (!empty($params_search['search_order_shipping'])) {
            $order_shipping_condition = 'AND TRIM(mst_delivery.shiping_name) = (select company_name from mst_customer where customer_code = ?)';
            $myPara[] = $params_search['search_order_shipping'];
        }
        $order_otodoke_condition = '';
        if (!empty($params_search['search_order_otodoke'])) {
            $order_otodoke_condition = 'AND TRIM(mst_delivery.otodoke_name) = (select company_name from mst_customer where customer_code = ?)';
            $myPara[] = $params_search['search_order_otodoke'];
        }
        $sale_type_condition = '';
        if ($params_search['search_sale_type'] != '0') {
            if ($params_search['search_sale_type'] == '1') {
                $sale_type_condition = "AND TRIM(mst_delivery.sale_type) = '通常' ";
            }
            if ($params_search['search_sale_type'] == '2') {
                $sale_type_condition = "AND TRIM(mst_delivery.sale_type) = '返品' ";
            }
        }
        $sql = "
                        SELECT
                            mst_delivery.delivery_no
                        FROM
                            dt_order_status
                        JOIN
                            mst_shipping
                            ON mst_shipping.cus_order_no = dt_order_status.cus_order_no
                            AND mst_shipping.cus_order_lineno = dt_order_status.cus_order_lineno
                        JOIN
                             mst_delivery
                             ON mst_delivery.shipping_no = mst_shipping.shipping_no
                             AND TRIM(mst_delivery.order_no) = CONCAT(TRIM(mst_shipping.cus_order_no),'-',TRIM(mst_shipping.cus_order_lineno))
                        WHERE
                            {$condition}
                            {$date_from_condition}
                            {$date_to_condition}
                            {$shipping_date_condition}
                            {$order_shipping_condition}
                            {$order_otodoke_condition}
                            {$sale_type_condition}
                        GROUP BY
                            mst_delivery.delivery_no
                        ORDER BY
                            mst_delivery.delivery_date ASC, mst_delivery.delivery_no ASC
                ";
        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($myPara);
            $rows = $result->fetchAllAssociative();
            $arRe = [];
            foreach ($rows as $item) {
                $arRe[] = $item['delivery_no'];
            }

            return $arRe;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function orderByDeliveryNoPrintPDF($arr_delivery_no)
    {
        $param = [];
        $str_delivery_no = '';
        $index = count($arr_delivery_no);
        for ($i = 0; $i < $index; $i++) {
            if ($i == $index - 1) {
                $str_delivery_no .= '?';
            } else {
                $str_delivery_no .= '?,';
            }
            $param[] = $arr_delivery_no[$i];
        }
        $sql = "
                    SELECT
                        mst_delivery.delivery_no
                    FROM
                        mst_delivery
                    WHERE
                        mst_delivery.delivery_no IN ({$str_delivery_no})
                    GROUP BY
                        mst_delivery.delivery_no
                    ORDER BY
                        mst_delivery.delivery_date ASC, mst_delivery.delivery_no ASC
                ";
        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            $arRe = [];
            foreach ($rows as $item) {
                $arRe[] = $item['delivery_no'];
            }

            return $arRe;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPdfDelivery($delivery_no, $orderNo = '', $customer_code, $login_type)
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'dt_order_status.shipping_code = ?';
                break;
            case 'otodoke_code':
                $condition = 'dt_order_status.otodoke_code = ?';
                break;
            default:
                $condition = 'dt_order_status.customer_code = ?';
                break;
        }
        $subQuantity = ' CASE
                            WHEN mst_product.quantity > 1 THEN mst_product.quantity * mst_delivery.quanlity
                            ELSE mst_delivery.quanlity
                            END AS quantity
                        ';
        $subUnitPrice = '   CASE
                            WHEN mst_product.quantity > 1 THEN mst_delivery.unit_price / mst_product.quantity
                            ELSE mst_delivery.unit_price
                            END AS unit_price
                       ';
        $addCondition = '';
        if (!empty($orderNo)) {
            $addCondition = ' and mst_delivery.order_no LIKE ? ';
        }
        $sql = "
                        SELECT
                            {$subUnitPrice},
                            {$subQuantity},
                            mst_delivery.delivery_no,
                            mst_delivery.delivery_date,
                            mst_delivery.deli_post_code,
                            mst_delivery.deli_addr01,
                            mst_delivery.deli_addr02,
                            mst_delivery.deli_addr03,
                            mst_delivery.deli_company_name,
                            mst_delivery.deli_department,
                            mst_delivery.postal_code,
                            mst_delivery.addr01 ,
                            mst_delivery.addr02,
                            mst_delivery.addr03,
                            mst_delivery.customer_code,
                            mst_delivery.company_name,
                            mst_delivery.department,
                            mst_delivery.delivery_lineno,
                            mst_delivery.sale_type,
                            mst_delivery.item_no,
                            mst_delivery.jan_code,
                            mst_delivery.item_name,
                            'PC' as unit,
                            mst_delivery.amount,
                            mst_delivery.tax,
                            mst_delivery.order_no,
                            mst_delivery.item_remark,
                            mst_delivery.total_amount,
                            mst_delivery.footer_remark1,
                            mst_delivery.shipping_code,
                            mst_delivery.shiping_name,
                            mst_delivery.otodoke_code,
                            mst_delivery.otodoke_name,
                            mst_customer.department as deli_department_name,
                            mst_delivery.shipping_no,
                            mst_customer_2.fusrstr8
                        FROM
                            dt_order_status
                        JOIN
                            mst_shipping
                            ON mst_shipping.cus_order_no = dt_order_status.cus_order_no
                            AND mst_shipping.cus_order_lineno = dt_order_status.cus_order_lineno
                        JOIN
                             mst_delivery
                             ON mst_delivery.shipping_no = mst_shipping.shipping_no
                             AND TRIM(mst_delivery.order_no) = CONCAT(TRIM(mst_shipping.cus_order_no),'-',TRIM(mst_shipping.cus_order_lineno))
                        LEFT JOIN
                            mst_customer ON (mst_customer.customer_code = mst_delivery.deli_department)
                        LEFT JOIN
                            mst_customer as mst_customer_2 ON (mst_customer_2.customer_code = mst_delivery.customer_code)
                        LEFT JOIN
                            mst_product ON (mst_product.product_code = mst_delivery.item_no)
                        WHERE
                            {$condition}
                        AND
                            mst_delivery.delivery_no = ?
                        AND 
                            mst_delivery.jan_code <> ''
                            {$addCondition}
                        GROUP by 
                            mst_delivery.delivery_no, mst_delivery.delivery_lineno, mst_delivery.jan_code
                        ORDER BY
                            mst_delivery.delivery_lineno ASC";
        $myPara = [$customer_code, $delivery_no];
        if (!empty($orderNo)) {
            $myPara[] = $orderNo.'-%';
        }
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function getCsvDelivery($delivery_no, $orderNo = '', $customer_code, $login_type)
    {
        switch ($login_type) {
            case 'shipping_code':
                $condition = 'dt_order_status.shipping_code = ?';
                break;
            case 'otodoke_code':
                $condition = 'dt_order_status.otodoke_code = ?';
                break;
            default:
                $condition = 'dt_order_status.customer_code = ?';
                break;
        }
        $subQuantity = ' CASE
                            WHEN mst_product.quantity > 1 THEN mst_product.quantity * mst_delivery.quanlity
                            ELSE mst_delivery.quanlity
                            END AS quantity
                        ';
        $subUnitPrice = '   CASE
                            WHEN mst_product.quantity > 1 THEN mst_delivery.unit_price / mst_product.quantity
                            ELSE mst_delivery.unit_price
                            END AS unit_price
                       ';
        $addCondition = '';
        if (!empty($orderNo)) {
            $addCondition = ' and mst_delivery.order_no LIKE ? ';
        }
        $sql = "
                        SELECT
                            {$subUnitPrice},
                            {$subQuantity},
                            mst_delivery.delivery_no,
                            mst_delivery.delivery_lineno,
                            mst_delivery.delivery_date,
                            mst_delivery.customer_code,
                            mst_delivery.deli_company_name,
                            mst_delivery.shipping_code,
                            mst_delivery.shiping_name,
                            mst_delivery.otodoke_code,
                            mst_delivery.otodoke_name,
                            mst_delivery.sale_type,
                            mst_delivery.item_no,
                            mst_delivery.jan_code,
                            mst_delivery.item_name,
                            'PC' as unit,
                            mst_delivery.amount,
                            mst_delivery.shipping_no,
                            mst_delivery.order_no,
                            mst_delivery.footer_remark1
                        FROM
                            dt_order_status
                        JOIN
                            mst_shipping
                            ON mst_shipping.cus_order_no = dt_order_status.cus_order_no
                            AND mst_shipping.cus_order_lineno = dt_order_status.cus_order_lineno
                        JOIN
                             mst_delivery
                             ON mst_delivery.shipping_no = mst_shipping.shipping_no
                             AND TRIM(mst_delivery.order_no) = CONCAT(TRIM(mst_shipping.cus_order_no),'-',TRIM(mst_shipping.cus_order_lineno))
                        LEFT JOIN
                            mst_customer ON (mst_customer.customer_code = mst_delivery.deli_department)
                        LEFT JOIN
                            mst_customer as mst_customer_2 ON (mst_customer_2.customer_code = mst_delivery.customer_code)
                        LEFT JOIN
                            mst_product ON (mst_product.product_code = mst_delivery.item_no)
                        WHERE
                            {$condition}
                        AND
                            mst_delivery.delivery_no = ?
                        AND 
                            mst_delivery.jan_code <> ''
                            {$addCondition}
                        GROUP by 
                            mst_delivery.delivery_no, mst_delivery.delivery_lineno, mst_delivery.jan_code
                        ORDER BY
                            mst_delivery.delivery_lineno ASC";
        $myPara = [$customer_code, $delivery_no];
        if (!empty($orderNo)) {
            $myPara[] = $orderNo.'-%';
        }
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $result = $statement->executeQuery($myPara);
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function savedtOrder(\Eccube\Entity\Order $Order)
    {
        $orderItems = $Order->getProductOrderItems();
        $index = 0;
        log_info('Insert dt_order');

        foreach ($orderItems as $item) {
            $index++;
            log_info($index);
            log_info(json_encode($item));

            $obj = $this->entityManager->getRepository(DtOrder::class)->findOneBy(
                [
                    'order_no' => $Order->getOrderNo(),
                    'order_lineno' => $index,
                    'customer_code' => $Order->customer_code,
                ]
            );

            if ($obj !== null) {
                log_error("Order {$Order->getOrderNo()}-{$index} is existed");
                continue;
            }

            $orderItem = new DtOrder();
            $orderItem->setOrderLineno($index);
            $orderItem->setOrderNo($Order->getOrderNo());
            $orderItem->setShippingCode($Order->shipping_no);
            $orderItem->setSeikyuCode($Order->seikyu_code);
            $orderItem->setShipingPlanDate($Order->delivery_date);
            $orderItem->setRequestFlg('Y');
            $orderItem->setCustomerCode($Order->customer_code);
            $orderItem->setProductCode($item->getMstProduct()['product_code']);
            $orderItem->setOtodokeCode($Order->otodoke_no);
            $orderItem->setOrderPrice($item->getPrice());
            $orderItem->setDemandQuantity($item->getQuantity());
            $orderItem->setOrderDate(new \DateTime('now', new \DateTimeZone('UTC')));
            $orderItem->setDeliPlanDate($Order->delivery_date);

            $orderItem->setItemNo($item->getMstProduct()['jan_code']);
            $orderItem->setDemandUnit($item->getMstProduct()['quantity'] > 1 ? 'CS' : 'PC');
            $orderItem->setDynaModelSeg2($Order->getId());
            $orderItem->setDynaModelSeg3('2');
            $orderItem->setDynaModelSeg4($Order->getId());
            $orderItem->setDynaModelSeg5($index);
            $orderItem->setDynaModelSeg6($Order->remarks1);
            $orderItem->setDynaModelSeg7($Order->remarks2);
            $orderItem->setDynaModelSeg8($Order->remarks3);
            $orderItem->setDynaModelSeg9($Order->remarks4);
            $orderItem->setUnitPriceStatus('FOR');
            $orderItem->setDeploy('XB');
            $orderItem->setCompanyId('XB');
            $orderItem->setShipingDepositCode($Order->location);
            $orderItem->setFvehicleno($Order->fvehicleno);
            $orderItem->setFtrnsportcd('87001');

            $this->entityManager->persist($orderItem);
            $this->entityManager->flush();
        }
    }

    public function saveOrderStatus(\Eccube\Entity\Order $Order)
    {
        $orderItems = $Order->getProductOrderItems();
        $index = 0;
        log_info('Insert dt_order_status');

        foreach ($orderItems as $item) {
            $index++;
            log_info($index);
            log_info(json_encode($item));

            $obj = $this->entityManager->getRepository(DtOrderStatus::class)->findOneBy(
                [
                    'cus_order_no' => $Order->getOrderNo(),
                    'cus_order_lineno' => $index,
                    'customer_code' => $Order->customer_code,
                ]
            );

            if ($obj !== null) {
                log_error("Order {$Order->getOrderNo()}-{$index} is existed");
                continue;
            }

            $orderItem = new DtOrderStatus();
            $orderItem->setOrderStatus('1');
            $orderItem->setOrderDate(new \DateTime('now', new \DateTimeZone('UTC')));
            $orderItem->setOrderNo($Order->getOrderNo());
            $orderItem->setOrderLineNo($index);
            $orderItem->setEcOrderNo($Order->getId());
            $orderItem->setEcOrderLineno($index);
            $orderItem->setCusOrderNo($Order->getOrderNo());
            $orderItem->setCusOrderLineno($index);
            $orderItem->setCustomerCode($Order->customer_code);
            $orderItem->setShippingCode($Order->shipping_no);
            $orderItem->setOtodokeCode($Order->otodoke_no);
            $orderItem->setOrderRemainNum($item->getQuantity());
            $orderItem->setProductCode($item->getMstProduct()['product_code']);
            $orderItem->setRemarks1($Order->remarks1);
            $orderItem->setRemarks2($Order->remarks2);
            $orderItem->setRemarks3($Order->remarks3);
            $orderItem->setRemarks4($Order->remarks4);
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

    public function getJanCodeToProductCode($jan_code = '')
    {
        $sql = 'SELECT `product_code` FROM `mst_product` WHERE `jan_code` = :jan_code limit 1';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery(['jan_code' => $jan_code]);
            $row = $result->fetchAllAssociative();

            return @$row[0]['product_code'];
        } catch (Exception $e) {
        }

        return null;
    }

    public function getJanCodeToProductName($jan_code = '')
    {
        $sql = 'SELECT `product_name` FROM `mst_product` WHERE `jan_code` = :jan_code limit 1';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery(['jan_code' => $jan_code]);
            $row = $result->fetchAllAssociative();

            return @$row[0]['product_name'];
        } catch (Exception $e) {
        }

        return null;
    }

    public function getDeliveredNum($shipping_no = '', $product_code = '')
    {
        $result = 0;
        if (!$shipping_no || !$product_code) {
            return $result;
        }

        $sql = 'SELECT
                SUM( `shipping_num` ) AS sum_shipping_num
            FROM `mst_shipping`
            WHERE
                `shipping_no` = :shipping_no
                AND `product_code` = :product_code
                AND `shipping_status` = 2
            GROUP BY shipping_no, product_code';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $query = $statement->executeQuery(['shipping_no' => $shipping_no, 'product_code' => $product_code]);
            $row = $query->fetchAllAssociative();

            foreach ($row as $dt) {
                $result += (int) $dt['sum_shipping_num'];
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    public function getReturnedNum($shipping_no = '', $product_code = '', $returns_no = '')
    {
        $result = 0;
        if (!$shipping_no || !$product_code) {
            return $result;
        }

        $param = ['shipping_no' => $shipping_no, 'product_code' => $product_code];
        $where = '';
        if (!empty($returns_no)) {
            $where = 'AND returns_no <> :returns_no';
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
            $query = $statement->executeQuery($param);
            $row = $query->fetchAllAssociative();

            foreach ($row as $dt) {
                $result += (int) $dt['sum_returns_num'];
            }
        } catch (Exception $e) {
        }

        return $result;
    }

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

    public function getDataImportNatStockList($product_code, $customer_code, $shipping_code)
    {
        $sql = "
            SELECT
                mp.jan_code,
                mp.unit_price,
                mp.quantity
            FROM
                mst_product mp
            JOIN
                dt_price dp
            ON
                mp.product_code = dp.product_code
            WHERE
                mp.product_code = ?
            AND
                (mp.discontinued_date > NOW() OR mp.discontinued_date IS NULL)
            AND
                (UPPER(mp.special_order_flg) <> 'Y' OR mp.special_order_flg IS NULL)
            AND
                dp.customer_code = ?
            AND
                dp.shipping_no = ?
            AND
                dp.price_s01 > 0
            GROUP BY
                mp.product_code
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

    public function getShippingWSExportData()
    {
        $sql = '
                SELECT *,
                    (SELECT DISTINCT IFNULL(mst_product.quantity, 1) FROM mst_product WHERE product_code = dowe.product_code) as quantity
                FROM
                    mst_shipping_ws_eos mswe
                JOIN
                    dt_order_ws_eos dowe
                ON
                    dowe.order_no = mswe.order_no
                AND
                    dowe.order_line_no = mswe.order_line_no
                WHERE
                    mswe.shipping_send_flg = 1
                AND
                    IFNULL(dowe.shipping_num, 0) > 0;
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery();

            return $result->fetchAllAssociative();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getShippingNatExportData()
    {
        $sql = '
                SELECT *,
                	(SELECT DISTINCT IFNULL(mst_product.quantity, 1) FROM mst_product WHERE product_code = done.product_code) as quantity
                FROM
                    mst_shipping_nat_eos msne
                JOIN
                    dt_order_nat_eos done
                ON
                    done.reqcd = msne.reqcd
                AND
                    done.order_lineno = msne.order_lineno
                WHERE
                    msne.shipping_send_flg = 1
                AND
                    IFNULL(done.shipping_num, 0) > 0;
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery();

            return $result->fetchAllAssociative();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getNatSortExportData()
    {
        $sql = '
                SELECT *
                FROM
                    dt_order_nat_sort
                ORDER BY
                    reqcd, jan;
        ';

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery();

            return $result->fetchAllAssociative();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getMstShippingImportEOS($data)
    {
        $sql = "
                SELECT *
                FROM
                    mst_shipping
                WHERE
                    cus_order_no = ?
                AND
                    cus_order_lineno = ?
                AND
                    shipping_status = 2
                AND
                    DATE_FORMAT(shipping_date, '%Y-%m-%d') > ?;
        ";

        try {
            $params = [$data['cus_order_no'], $data['cus_order_lineno'], date('Y-m-d', strtotime($data['shipping_date'] ?? ''))];
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($params);
            $row = $result->fetchAllAssociative();

            return $row[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDeliveryShipFee($delivery_no)
    {
        $sql = "
                SELECT 
                    * 
                FROM 
                    mst_delivery 
                WHERE 
                    delivery_no = ?
                AND 
                    (   jan_code = '' 
                    OR 
                        jan_code IS NULL
                    )
                LIMIT 1;
        ";
        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery([$delivery_no]);
            $row = $result->fetchAllAssociative();

            return $row[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getSumAmountDtOrder($order_no)
    {
        $sql = '
                SELECT 
                    SUM(demand_quantity * order_price) AS amount
                FROM
                    dt_order
                WHERE
                    order_no = ?
                GROUP BY
                    order_no;
        ';
        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery([$order_no]);
            $row = $result->fetchAllAssociative();

            return $row[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function updateDtOrder($condition = [], $data = [])
    {
        $params = [];
        $set = '';
        foreach ($data as $key => $value) {
            $set .= " {$key} = ?, ";
            $params[] = $value;
        }
        $set = trim($set, ', ');
        $where = '';
        foreach ($condition as $key => $value) {
            $where .= " {$key} = ? AND ";
            $params[] = $value;
        }
        $where = trim($where, 'AND ');
        $sql = "
                UPDATE
                    dt_order
                SET
                    {$set}
                Where
                    {$where}
        ";
        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);

            return $statement->executeQuery($params);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTankaList($searchData, $shipping_code = '', $customer_code = '')
    {
        if (empty($shipping_code) || empty($customer_code)) {
            return [];
        }

        $param = [$shipping_code, $customer_code];
        $additionalCondition = '';

        // Search left menu
        if (isset($searchData['mode']) && $searchData['mode'] == 'searchLeft') {
            $commonService = new MyCommonService($this->entityManager);

            if (StringUtil::isNotBlank($searchData['s_product_name_kana'])) {
                $arrNameKana = explode(' ', $searchData['s_product_name_kana']);
                $tempCondition = '';

                foreach ($arrNameKana as $item) {
                    $item = trim($item);
                    if ($item == '') {
                        continue;
                    }

                    $tempCondition .= ' mp.product_name_kana like ? or ';
                    array_push($param, '%'.$item.'%');
                }

                $additionalCondition .= ' AND ( '.trim($tempCondition, 'or ').' ) ';
            }

            if (StringUtil::isNotBlank($searchData['s_product_name'])) {
                $arCode = $commonService->getSearchProductName($searchData['s_product_name']);
                $tempCondition = '';

                foreach ($arCode as $item) {
                    $item = trim($item);
                    if ($item == '') {
                        continue;
                    }

                    $tempCondition .= ' mp.jan_code = ? or ';
                    array_push($param, $item);
                }

                $additionalCondition .= ' AND ( '.trim($tempCondition, 'or ').' ) ';
            }

            if (StringUtil::isNotBlank($searchData['s_jan'])) {
                $arrJan = explode(' ', $searchData['s_jan']);
                $tempCondition = '';

                foreach ($arrJan as $item) {
                    $item = trim($item);
                    if ($item == '') {
                        continue;
                    }

                    $tempCondition .= ' mp.jan_code like ? or ';
                    array_push($param, '%'.$item.'%');
                }

                $additionalCondition .= ' AND ( '.trim($tempCondition, 'or ').' ) ';
            }

            if (StringUtil::isNotBlank($searchData['s_catalog_code'])) {
                $arrCatalog = explode(' ', $searchData['s_catalog_code']);
                $tempCondition = '';

                foreach ($arrCatalog as $item) {
                    $item = trim($item);
                    if ($item == '') {
                        continue;
                    }

                    $tempCondition .= ' mp.catalog_code like ? or ';
                    array_push($param, '%'.$item.'%');
                }

                $additionalCondition .= ' AND ( '.trim($tempCondition, 'or ').' ) ';
            }

            if (StringUtil::isNotBlank($searchData['name'])) {
                $tempCondition = ' mp.series_name like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.product_name_abb like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.product_name like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.product_name_kana like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.jan_code like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.product_code like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $tempCondition .= ' mp.catalog_code like ? or ';
                array_push($param, '%'.$searchData['name'].'%');

                $additionalCondition .= ' AND ( '.trim($tempCondition, 'or ').' ) ';
            }
        }

        $sql = " select
                    MAX(pri.tanka_number) AS max_tanka_number
                from
                    dt_customer_relation as cur
                join
                    dt_price as pri
                on
                    cur.customer_code = pri.customer_code
                and
                    cur.shipping_code = pri.shipping_no
                join
                    mst_product mp
                on
                    mp.product_code = pri.product_code
                WHERE
                    pri.shipping_no = ?
                AND pri.customer_code = ?
                AND pri.price_s01 > 0
                AND DATE_FORMAT(NOW(), '%Y-%m-%d') >= pri.valid_date
                AND DATE_FORMAT(NOW(), '%Y-%m-%d') < pri.expire_date
                {$additionalCondition}
                GROUP BY
                    pri.product_code
                 ORDER BY
                    pri.tanka_number desc; 
                ";

        try {
            $statement = $this->entityManager->getConnection()->prepare($sql);
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();
            $arrTanaka = array_column($rows, 'max_tanka_number');

            return $arrTanaka;
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return [];
        }
    }

    public function getPriceFromDtPrice($customer_code = '', $shipping_no = '', $productCode)
    {
        if ($customer_code == '' || $shipping_no == '') {
            return '';
        }

        $sql = " SELECT  dt_price.price_s01
                    FROM
                        dt_price
			        JOIN
                        (   select pri.product_code, MAX(pri.tanka_number) AS max_tanka_number
                            from dt_price pri
                            WHERE pri.customer_code = ?
                            AND pri.shipping_no = ?
                            AND pri.price_s01 > 0
                            AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= pri.valid_date
                            AND DATE_FORMAT(NOW(),'%Y-%m-%d') < pri.expire_date
                            AND pri.product_code = ?
                        ) AS dt_price_2
                    ON
                        dt_price_2.max_tanka_number = dt_price.tanka_number
                    AND
                        dt_price_2.product_code = dt_price.product_code
                    WHERE
                        dt_price.shipping_no = ?
                    AND 
                        dt_price.price_s01 > 0
                    AND 
                        dt_price.customer_code = ?
                    GROUP BY dt_price.product_code; 
                ";

        $param = [$customer_code, $shipping_no, $productCode, $shipping_no, $customer_code];
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $price = '';

        try {
            $result = $statement->executeQuery($param);
            $rows = $result->fetchAllAssociative();

            if (count($rows) > 0) {
                $price = $rows[0] ?? '';
            }

            return $price;
        } catch (\Exception $e) {
            log_info($e->getMessage());

            return '';
        }
    }
}
