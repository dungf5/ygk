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
use Eccube\Entity\ItemInterface;
use Eccube\Entity\OrderItem;

if (!class_exists('\Customize\Entity\MstShipping', false)) {
    /**
     * MstProduct
     *
     * @ORM\Table(name="mst_shipping")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstShippingRepository")
     */
    class MstShipping extends AbstractEntity
    {


        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no", type="string", length=15, nullable=true)
         * @ORM\Id
         */
        protected $shipping_no;

        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=15)
         */
        private $order_no;


        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_no", type="string", length=15)
         */
        private $cus_order_no;

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
         * @var string
         *
         * @ORM\Column(name="order_lineno", type="string", length=8)
         */
        private $order_lineno;

        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_lineno", type="string", length=18)
         */
        private $cus_order_lineno;

        /**
         * @return string
         */
        public function getCusOrderLineno()
        {
            return $this->cus_order_lineno;
        }

        /**
         * @param $cus_order_lineno
         */
        public function setCusOrderLineno($cus_order_lineno)
        {
            $this->cus_order_lineno = $cus_order_lineno;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=25)
         */
        private $customer_code;

        /**
         * @return string
         */
        public function getCustomerCode()
        {
            return $this->customer_code;
        }

        /**
         * @param $customer_code
         */
        public function setCustomerCode($customer_code)
        {
            $this->customer_code = $customer_code;
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
         * @var string
         *
         * @ORM\Column(name="shipping_code", type="string", length=25)
         */
        private $shipping_code;

        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=25)
         */
        private $product_code;

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
         * @var int
         *
         * @ORM\Column(name="shipping_status", type="smallint", nullable=true)
         */
        private $shipping_status;


        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_no", type="string", length=15)
         */
        private $ec_order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_lineno", type="string", length=15)
         */
        private $ec_order_lineno;


        /**
         * @var int
         *
         * @ORM\Column(name="shipping_num", type="integer", nullable=false, options={"default":0})
         */
        private $shipping_num;


        /**
         * @var string
         *
         * @ORM\Column(name="shipping_plan_date", type="string", length=10)
         */
        private $shipping_plan_date;

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date", type="string", length=10)
         */
        private $shipping_date;

        /**
         * @var string
         *
         * @ORM\Column(name="inquiry_no", type="string", length=10)
         */
        private $inquiry_no;

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_company_code", type="string", length=10)
         */
        private $shipping_company_code;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**

         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;

        /**
         * @var int
         *
         * @ORM\Column(name="delete_flg", type="integer", nullable=true, options={"default":1})
         */
        private $delete_flg;

        /**
         * @return string
         */
        public function getShippingNo()
        {
            return $this->shipping_no;
        }

        /**
         * @param $shipping_no
         */
        public function setShippingNo($shipping_no)
        {
            $this->shipping_no = $shipping_no;
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
        public function getOrderLineno()
        {
            return $this->order_lineno;
        }

        /**
         * @param $order_lineno
         */
        public function setOrderLineno($order_lineno)
        {
            $this->order_lineno = $order_lineno;
        }

        /**
         * @return int
         */
        public function getShippingStatus()
        {
            return $this->shipping_status;
        }

        /**
         * @param $shipping_status
         */
        public function setShippingStatus($shipping_status)
        {
            $this->shipping_status = $shipping_status;
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
         * @return int
         */
        public function getShippingNum()
        {
            return $this->shipping_num;
        }

        /**
         * @param $shipping_num
         */
        public function setShippingNum($shipping_num)
        {
            $this->shipping_num = $shipping_num;
        }

        /**
         * @return string
         */
        public function getShippingPlanDate()
        {
            return $this->shipping_plan_date;
        }

        /**
         * @param $shipping_plan_date
         */
        public function setShippingPlanDate($shipping_plan_date)
        {
            $this->shipping_plan_date = $shipping_plan_date;
        }

        /**
         * @return string
         */
        public function getShippingDate()
        {
            return $this->shipping_date;
        }

        /**
         * @param $shipping_date
         */
        public function setShippingDate($shipping_date)
        {
            $this->shipping_date = $shipping_date;
        }

        /**
         * @return string
         */
        public function getInquiryNo()
        {
            return $this->inquiry_no;
        }

        /**
         * @param $inquiry_no
         */
        public function setInquiryNo($inquiry_no)
        {
            $this->inquiry_no = $inquiry_no;
        }

        /**
         * @return string
         */
        public function getShippingCompanyCode()
        {
            return $this->shipping_company_code;
        }

        /**
         * @param $shipping_company_code
         */
        public function setShippingCompanyCode($shipping_company_code)
        {
            $this->shipping_company_code = $shipping_company_code;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate(): \DateTime
        {
            return $this->create_date;
        }

        /**
         * @param \DateTime $create_date
         */
        public function setCreateDate(\DateTime $create_date)
        {
            $this->create_date = $create_date;
        }

        /**
         * @return
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }

        /**
         * @param  $update_date
         */
        public function setUpdateDate( $update_date)
        {
            $this->update_date = $update_date;
        }

        /**
         * @return int
         */
        public function getDeleteFlg()
        {
            return $this->delete_flg;
        }

        /**
         * @param $delete_flg
         */
        public function setDeleteFlg($delete_flg)
        {
            $this->delete_flg = $delete_flg;
        }
    }
}
