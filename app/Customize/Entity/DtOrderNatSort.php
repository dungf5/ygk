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

if (!class_exists('\Customize\Entity\DtOrderNatSort', false)) {
    /**
     * DtOrderNatSort
     *
     * @ORM\Table(name="dt_order_nat_sort")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderNatSortRepository")
     */
    class DtOrderNatSort extends AbstractEntity
    {
        /**
         * @var integer
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;
        /**
         * @var string
         *
         * @ORM\Column(name="reqcd",nullable=true, type="string", length=13, options={"comment":"客先発注№"})
         */
        private $reqcd;
        /**
         * @var string
         *
         * @ORM\Column(name="jan",nullable=true, type="string", length=13, options={"comment":""})
         */
        private $jan;
        /**
         * @var string
         *
         * @ORM\Column(name="mkrcd",nullable=true, type="string", length=20, options={"comment":""})
         */
        private $mkrcd;
        /**
         * @var string
         *
         * @ORM\Column(name="natcd",nullable=true, type="string", length=7, options={"comment":""})
         */
        private $natcd;
        /**
         * @ORM\Column(name="qty",type="integer",nullable=true, options={"comment":""  })
         */
        private $qty;
        /**
         * @ORM\Column(name="cost",type="integer",nullable=true, options={"comment":""  })
         */
        private $cost;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_day",nullable=true, type="string", length=8, options={"comment":"yyyymmdd"})
         */
        private $delivery_day;

        /**
         * @return string
         */
        public function getReqcd()
        {
            return $this->reqcd;
        }

        /**
         * @param $reqcd
         */
        public function setReqcd($reqcd)
        {
            $this->reqcd = $reqcd;
        }

        /**
         * @return string
         */
        public function getJan()
        {
            return $this->jan;
        }

        /**
         * @param $jan
         */
        public function setJan($jan)
        {
            $this->jan = $jan;
        }

        /**
         * @return string
         */
        public function getMkrcd()
        {
            return $this->mkrcd;
        }

        /**
         * @param $mkrcd
         */
        public function setMkrcd($mkrcd)
        {
            $this->mkrcd = $mkrcd;
        }

        /**
         * @return string
         */
        public function getNatcd()
        {
            return $this->natcd;
        }

        /**
         * @param $natcd
         */
        public function setNatcd($natcd)
        {
            $this->natcd = $natcd;
        }

        /**
         * @return mixed
         */
        public function getQty()
        {
            return $this->qty;
        }

        /**
         * @param mixed $qty
         */
        public function setQty($qty)
        {
            $this->qty = $qty;
        }

        /**
         * @return mixed
         */
        public function getCost()
        {
            return $this->cost;
        }

        /**
         * @param mixed $cost
         */
        public function setCost($cost)
        {
            $this->cost = $cost;
        }

        /**
         * @return string
         */
        public function getDeliveryDay()
        {
            return $this->delivery_day;
        }

        /**
         * @param $delivery_day
         */
        public function setDeliveryDay($delivery_day)
        {
            $this->delivery_day = $delivery_day;
        }

    }
}
