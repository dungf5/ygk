<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\MoreOrder', false)) {
    /**
     * MoreOrder
     *
     * @ORM\Table(name="more_order")
     * @ORM\Entity(repositoryClass="Customize\Repository\MoreOrderRepository")
     */
    class MoreOrder extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="pre_order_id", type="string", length=255,options={"comment":""}, nullable=false)
         * @ORM\Id
         */
        private $pre_order_id;

        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $order_no;

        /**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=true, type="string", length=10, options={"comment":"address to customer for bill"})
         */
        private $seikyu_code;

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks1", type="text",options={"comment":"備考１"}, nullable=true)
         */
        private $remarks1;

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks2", type="text",options={"comment":"備考２"}, nullable=true)
         */
        private $remarks2;

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks3", type="text",options={"comment":"発注書宛先"}, nullable=true)
         */
        private $remarks3;

        /**
         * @var string|null
         *
         * @ORM\Column(name="remarks4", type="text",options={"comment":"発注書備考"}, nullable=true)
         */
        private $remarks4;

        /**
         * @return string
         */
        public function getPreOrderId()
        {
            return $this->pre_order_id;
        }

        /**
         * @param string $pre_order_id
         */
        public function setPreOrderId(string $pre_order_id): void
        {
            $this->pre_order_id = $pre_order_id;
        }

        /**
         * @return string
         */
        public function getOrderNo()
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
        public function getSeikyuCode()
        {
            return $this->seikyu_code;
        }

        /**
         * @param string $seikyu_code
         */
        public function setSeikyuCode(string $seikyu_code): void
        {
            $this->seikyu_code = $seikyu_code;
        }

        /**
         * @return string
         */
        public function getShippingCode()
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
         * @return string
         */
        public function getOtodokeCode()
        {
            return $this->otodoke_code;
        }

        /**
         * @param string $otodoke_code
         */
        public function setOtodokeCode(string $otodoke_code): void
        {
            $this->otodoke_code = $otodoke_code;
        }


        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=true, type="string", length=4, options={"comment":"Position store"})
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=true, type="string", length=4, options={"comment":"address to customer for product"})
         */
        private $otodoke_code;

        /**
         * @var string
         *
         * @ORM\Column(name="date_want_delivery",nullable=true, type="string", length=10, options={"comment":""})
         */
        private $date_want_delivery;

        /**
         *
         */
        public function getDateWantDelivery()
        {
            return $this->date_want_delivery;
        }

        /**
         * field new save
         * @param string $date_want_delivery
         */
        public function setDateWantDelivery(string $date_want_delivery): void
        {
            $this->date_want_delivery = $date_want_delivery;
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
         * Set remarks4.
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
    }
}
