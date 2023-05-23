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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\Date;

if (!class_exists('\Customize\Entity\DtOrderStatusDaitoTest', false)) {
    /**
     * DtOrderStatusDaitoTest
     *
     * @ORM\Table(name="dt_order_status_daito_test")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderStatusDaitoTestRepository")
     */
    class DtOrderStatusDaitoTest extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=15, options={"comment":"STRA注文番号", "default": ''})
         */
        private $order_no = '';

        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=45, options={"comment":"product_code"})
         */
        private $product_code = '';

        /**
         * @var string
         *
         * @ORM\Column(name="order_line_no",nullable=true, type="string", length=15, options={"comment":"STRA注文明細番号"})
         */
        private $order_line_no = 0;

        /**
         * @ORM\Column(name="order_status",type="integer",nullable=false, options={"comment":"受注ステータス
ステータス種類
        1:未引当、2:一部引当、3:引当済、4:キャンセル、9:クロース(出荷済)" ,"default":1 })
         */
        private $order_status = 1;

        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_no", type="string", length=15,options={"comment":"EC発注番号"}, nullable=false)
         */
        private $ec_order_no = '';

        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_lineno", type="string", length=15,options={"comment":"EC発注明細番号"}, nullable=false)
         */
        private $ec_order_lineno = 0;

        /**
         * @ORM\Column(name="reserve_stock_num",type="integer",nullable=true, options={"comment":"引当在庫数"  })
         */
        private $reserve_stock_num = 0;

        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_no", type="string", length=40,options={"comment":"客先発注No"}, nullable=false)
         * @ORM\Id
         */
        private $cus_order_no = '';

        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_lineno", type="string", length=2,options={"comment":"客先発注No"}, nullable=false)
         */
        private $cus_order_lineno = 0;

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=25,options={"comment":"顧客"}, nullable=true)
         */
        private $customer_code = '';

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code", type="string", length=25,options={"comment":"顧客"}, nullable=true)
         */
        private $shipping_code = '';

        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code", type="string", length=25,options={"comment":"届け先コード"}, nullable=true)
         */
        private $otodoke_code = '';

        /**
         * @ORM\Column(name="order_remain_num",type="integer",nullable=true, options={"comment":"受注残"  })
         */
        private $order_remain_num = 0;
        /**
         * @var string
         *
         * @ORM\Column(name="flow_type",nullable=true, type="string", length=10, options={"comment":"商流区分(ダイナ規格セグメント03)"})
         */
        private $flow_type = '';
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ登録日時'")
         */
        private $create_date = null;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ更新日時'")
         */
        private $update_date = null;

        /**
         * @var \DateTime|null
         *
         * @ORM\Column(name="order_date", type="date", nullable=true, options={"comment":"受注日"})
         */
        private $order_date = null;

        /**
         * @var string
         *
         * @ORM\Column(name="ec_type", nullable=true, type="string", length=10, options={"comment":"EC1区分"})
         */
        private $ec_type = '';

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks1", type="text",options={"comment":"備考１"}, nullable=true)
         */
        private $remarks1 = '';

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks2", type="text",options={"comment":"備考２"}, nullable=true)
         */
        private $remarks2 = '';

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks3", type="text",options={"comment":"発注書"}, nullable=true)
         */
        private $remarks3 = '';

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks4", type="text",options={"comment":"発注書備考"}, nullable=true)
         */
        private $remarks4 = '';

        /**
         * @ORM\Column(name="shipping_num",type="integer",nullable=true, options={"comment":"出荷済数" ,"default": null })
         */
        private $shipping_num = 0;

        /**
         * @return string
         */
        public function getProductCode()
        {
            return $this->product_code;
        }

        /**
         * @param $product_code
         */
        public function setProductCode($product_code)
        {
            $this->product_code = $product_code;
        }

        /**
         * @return string
         */
        public function getShippingCode()
        {
            return $this->shipping_code;
        }

        /**
         * @param $shipping_code
         */
        public function setShippingCode($shipping_code)
        {
            $this->shipping_code = $shipping_code;
        }

        /**
         * @return string
         */
        public function getOtodokeCode()
        {
            return $this->otodoke_code;
        }

        /**
         * @param $otodoke_code
         */
        public function setOtodokeCode($otodoke_code)
        {
            $this->otodoke_code = $otodoke_code;
        }

        /**
         * @return mixed
         */
        public function getCustomerCode()
        {
            return $this->customer_code;
        }

        /**
         * @param mixed $customer_code
         */
        public function setCustomerCode($customer_code)
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return mixed
         */
        public function getCusOrderLineno()
        {
            return $this->cus_order_lineno;
        }

        /**
         * @param mixed $cus_order_lineno
         */
        public function setCusOrderLineno($cus_order_lineno)
        {
            $this->cus_order_lineno = $cus_order_lineno;
        }

        /**
         * @return string
         */
        public function getCusOrderNo()
        {
            return $this->cus_order_no;
        }

        /**
         * @param $cus_order_no
         */
        public function setCusOrderNo($cus_order_no)
        {
            $this->cus_order_no = $cus_order_no;
        }

        /**
         * @return string
         */
        public function getOrderNo()
        {
            return $this->order_no;
        }

        /**
         * @param $order_no
         */
        public function setOrderNo($order_no)
        {
            $this->order_no = $order_no;
        }

        /**
         * @return string
         */
        public function getOrderLineNo()
        {
            return $this->order_line_no;
        }

        /**
         * @param $order_line_no
         */
        public function setOrderLineNo($order_line_no)
        {
            $this->order_line_no = $order_line_no;
        }

        /**
         * @return mixed
         */
        public function getOrderStatus()
        {
            return $this->order_status;
        }

        /**
         * @param mixed $order_status
         */
        public function setOrderStatus($order_status)
        {
            $this->order_status = $order_status;
        }

        /**
         * @return string
         */
        public function getEcOrderNo()
        {
            return $this->ec_order_no;
        }

        /**
         * @param $ec_order_no
         */
        public function setEcOrderNo($ec_order_no)
        {
            $this->ec_order_no = $ec_order_no;
        }

        /**
         * @return string
         */
        public function getEcOrderLineno()
        {
            return $this->ec_order_lineno;
        }

        /**
         * @param $ec_order_lineno
         */
        public function setEcOrderLineno($ec_order_lineno)
        {
            $this->ec_order_lineno = $ec_order_lineno;
        }

        /**
         * @return mixed
         */
        public function getReserveStockNum()
        {
            return $this->reserve_stock_num;
        }

        /**
         * @param mixed $reserve_stock_num
         */
        public function setReserveStockNum($reserve_stock_num)
        {
            $this->reserve_stock_num = $reserve_stock_num;
        }

        /**
         * @return mixed
         */
        public function getOrderRemainNum()
        {
            return $this->order_remain_num;
        }

        /**
         * @param mixed $order_remain_num
         */
        public function setOrderRemainNum($order_remain_num)
        {
            $this->order_remain_num = $order_remain_num;
        }

        /**
         * @return string
         */
        public function getFlowType()
        {
            return $this->flow_type;
        }

        /**
         * @param $flow_type
         */
        public function setFlowType($flow_type)
        {
            $this->flow_type = $flow_type;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * @param $create_date
         */
        public function setCreateDate($create_date)
        {
            $this->create_date = $create_date;
        }

        /**
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }

        /**
         * @param $update_date
         */
        public function setUpdateDate($update_date)
        {
            $this->update_date = $update_date;
        }

        /**
         * Set orderDate.
         *
         * @param \DateTime|null $orderDate
         */
        public function setOrderDate($orderDate = null)
        {
            $this->order_date = $orderDate;
        }

        /**
         * Get orderDate.
         *
         * @return Date|null
         */
        public function getOrderDate()
        {
            return $this->order_date;
        }

        /**
         * @return string|null
         */
        public function getEcType()
        {
            return $this->ec_type;
        }

        /**
         * @param string|null $ec_type
         */
        public function setEcType($ec_type = null)
        {
            $this->ec_type = $ec_type;
        }

        /**
         * Set remarks1.
         *
         * @param string|null $remarks1
         */
        public function setRemarks1($remarks1 = null)
        {
            $this->remarks1 = $remarks1;
        }

        /**
         * Get remarks1.
         *
         * @return string|null
         */
        public function getRemarks1()
        {
            return $this->remarks1;
        }

        /**
         * Set remarks2.
         *
         * @param string|null $remarks2
         */
        public function setRemarks2($remarks2 = null)
        {
            $this->remarks2 = $remarks2;
        }

        /**
         * Get remarks2.
         *
         * @return string|null
         */
        public function getRemarks2()
        {
            return $this->remarks2;
        }

        /**
         * Set remarks3.
         *
         * @param string|null $remarks3
         */
        public function setRemarks3($remarks3 = null)
        {
            $this->remarks3 = $remarks3;
        }

        /**
         * Get remarks3.
         *
         * @return string|null
         */
        public function getRemarks3()
        {
            return $this->remarks3;
        }

        /**
         * Set remarks4
         *
         * @param string|null $remarks4
         */
        public function setRemarks4($remarks4 = null)
        {
            $this->remarks4 = $remarks4;
        }

        /**
         * Get remarks4.
         *
         * @return string|null
         */
        public function getRemarks4()
        {
            return $this->remarks4;
        }

        /**
         * @return int
         */
        public function getShippingNum()
        {
            return (int) $this->shipping_num;
        }

        /**
         * @param int|null
         */
        public function setShippingNum($shipping_num = null)
        {
            $this->shipping_num = $shipping_num;
        }
    }
}
