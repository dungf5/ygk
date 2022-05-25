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
         * @return string
         */
        public function getPreOrderId(): string
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
        public function getSeikyuCode(): string
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
         * @return string
         */
        public function getOtodokeCode(): string
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
         * @ORM\Column(name="pre_order_id",nullable=true, type="string", length=255, options={"comment":""})
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
         * @ORM\Column(name="seikyu_code",nullable=false, type="string", length=10, options={"comment":"address to customer for bill"})
         */
        private $seikyu_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=false, type="string", length=4, options={"comment":"Position store"})
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=false, type="string", length=4, options={"comment":"address to customer for product"})
         */
        private $otodoke_code;
    }
}
