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
        public function getCusOrderNo(): string
        {
            return $this->cus_order_no;
        }

        /**
         * @param string $cus_order_no
         */
        public function setCusOrderNo(string $cus_order_no): void
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
        public function getCusOrderLineno(): string
        {
            return $this->cus_order_lineno;
        }

        /**
         * @param string $cus_order_lineno
         */
        public function setCusOrderLineno(string $cus_order_lineno): void
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
        public function getCustomerCode(): string
        {
            return $this->customer_code;
        }

        /**
         * @param string $customer_code
         */
        public function setCustomerCode(string $customer_code): void
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return string
         */
        public function getShippingCode(): string
        {
            return $this->shipping_code;
        }

        /**
         * @param string $shipping_code
         */
        public function setShippingCode(string $shipping_code): void
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
        public function getProductCode(): string
        {
            return $this->product_code;
        }

        /**
         * @param string $product_code
         */
        public function setProductCode(string $product_code): void
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
         * @return string
         */
        public function getShippingNo(): string
        {
            return $this->shipping_no;
        }

        /**
         * @param string $shipping_no
         */
        public function setShippingNo(string $shipping_no): void
        {
            $this->shipping_no = $shipping_no;
        }

        /**
         * @return string
         */
        public function getOrderNo(): string
        {
            return $this->order_no;
        }

        /**
         * @param string $order_no
         */
        public function setOrderNo(string $order_no): void
        {
            $this->order_no = $order_no;
        }



        /**
         * @return string
         */
        public function getOrderLineno(): string
        {
            return $this->order_lineno;
        }

        /**
         * @param string $order_lineno
         */
        public function setOrderLineno(string $order_lineno): void
        {
            $this->order_lineno = $order_lineno;
        }

        /**
         * @return int
         */
        public function getShippingStatus(): int
        {
            return $this->shipping_status;
        }

        /**
         * @param int $shipping_status
         */
        public function setShippingStatus(int $shipping_status): void
        {
            $this->shipping_status = $shipping_status;
        }

        /**
         * @return string
         */
        public function getEcOrderNo(): string
        {
            return $this->ec_order_no;
        }

        /**
         * @param string $ec_order_no
         */
        public function setEcOrderNo(string $ec_order_no): void
        {
            $this->ec_order_no = $ec_order_no;
        }

        /**
         * @return string
         */
        public function getEcOrderLineno(): string
        {
            return $this->ec_order_lineno;
        }

        /**
         * @param string $ec_order_lineno
         */
        public function setEcOrderLineno(string $ec_order_lineno): void
        {
            $this->ec_order_lineno = $ec_order_lineno;
        }

        /**
         * @return int
         */
        public function getShippingNum(): int
        {
            return $this->shipping_num;
        }

        /**
         * @param int $shipping_num
         */
        public function setShippingNum(int $shipping_num): void
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
         * @param string $shipping_plan_date
         */
        public function setShippingPlanDate(string $shipping_plan_date): void
        {
            $this->shipping_plan_date = $shipping_plan_date;
        }

        /**
         * @return string
         */
        public function getShippingDate(): string
        {
            return $this->shipping_date;
        }

        /**
         * @param string $shipping_date
         */
        public function setShippingDate(string $shipping_date): void
        {
            $this->shipping_date = $shipping_date;
        }

        /**
         * @return string
         */
        public function getInquiryNo(): string
        {
            return $this->inquiry_no;
        }

        /**
         * @param string $inquiry_no
         */
        public function setInquiryNo(string $inquiry_no): void
        {
            $this->inquiry_no = $inquiry_no;
        }

        /**
         * @return string
         */
        public function getShippingCompanyCode(): string
        {
            return $this->shipping_company_code;
        }

        /**
         * @param string $shipping_company_code
         */
        public function setShippingCompanyCode(string $shipping_company_code): void
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
        public function setCreateDate(\DateTime $create_date): void
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
        public function setUpdateDate( $update_date): void
        {
            $this->update_date = $update_date;
        }


//        /**
//         * @var \Eccube\Entity\Order
//         *
//         * @ORM\OneToOne(targetEntity="Eccube\Entity\Order")
//         * @ORM\JoinColumns({
//         *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
//         * })
//         */
//        private $Order;
//
//        /**
//         * @param mixed $Order
//         */
//        public function setOrder($Order): void
//        {
//            $this->Order = $Order;
//        }




    }
}
