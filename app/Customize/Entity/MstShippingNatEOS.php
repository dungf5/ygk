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

if (!class_exists('\Customize\Entity\MstShippingNatEOS', false)) {
    /**
     * MstShippingNatEOS
     *
     * @ORM\Table(name="mst_shipping_nat_eos")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstShippingNatEOSRepository")
     */
    class MstShippingNatEOS extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_no",nullable=true, type="string", length=13, options={"comment":"納品書番号"})
         */
        private $delivery_no;
        /**
         * @var string
         *
         * @ORM\Column(name="jan",nullable=true, type="string", length=13, options={"comment":"JANコード"})
         */
        private $jan;
        /**
         * @var string
         *
         * @ORM\Column(name="mkrcd",nullable=true, type="string", length=20, options={"comment":"品番(メーカー品番)"})
         */
        private $mkrcd;
        /**
         * @var string
         *
         * @ORM\Column(name="natcd",nullable=true, type="string", length=7, options={"comment":"ナチュラム商品番号"})
         */
        private $natcd;
        /**
         * @ORM\Column(name="quanlity",type="integer",nullable=true, options={"comment":"納品数"  })
         */
        private $quanlity;
        /**
         * @ORM\Column(name="unit_price",type="integer",nullable=true, options={"comment":"納品単価"  })
         */
        private $unit_price;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_day",nullable=true, type="string", length=8, options={"comment":"到着予定日yyyymmdd"})
         */
        private $delivery_day;
        /**
         * @ORM\Column(name="shipping_send_flg",type="integer",nullable=true, options={"comment":"出荷送信対象フラグ ０：送信非対象(初期値)　１：送信対象"  })
         */
        private $shipping_send_flg;
        /**
         * @ORM\Column(name="shipping_sent_flg",type="integer",nullable=true, options={"comment":"出荷送信済フラグ ０：未送信(初期値)　１：送信済"  })
         */
        private $shipping_sent_flg;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no",nullable=true, type="string", length=10, options={"comment":"出荷指示№"})
         */
        private $shipping_no;
        /**
         * @var string
         *
         * @ORM\Column(name="reqcd", type="string", length=13,options={"comment":"発注番号"}, nullable=false)
         * @ORM\Id
         */
        private $reqcd;
        /**
         * @var integer
         *
         * @ORM\Column(name="order_lineno", type="integer", options={"unsigned":true})
         * @ORM\Id
         */
        private $order_lineno;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ更新日時'")
         */
        private $update_date;

        /**
         * @return string
         */
        public function getDeliveryNo()
        {
            return $this->delivery_no;
        }

        /**
         * @param $delivery_no
         */
        public function setDeliveryNo($delivery_no)
        {
            $this->delivery_no = $delivery_no;
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
        public function getQuanlity()
        {
            return $this->quanlity;
        }

        /**
         * @param mixed $quanlity
         */
        public function setQuanlity($quanlity)
        {
            $this->quanlity = $quanlity;
        }

        /**
         * @return mixed
         */
        public function getUnitPrice()
        {
            return $this->unit_price;
        }

        /**
         * @param mixed $unit_price
         */
        public function setUnitPrice($unit_price)
        {
            $this->unit_price = $unit_price;
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

        /**
         * @return mixed
         */
        public function getShippingSendFlg()
        {
            return $this->shipping_send_flg;
        }

        /**
         * @param mixed $shipping_send_flg
         */
        public function setShippingSendFlg($shipping_send_flg)
        {
            $this->shipping_send_flg = $shipping_send_flg;
        }

        /**
         * @return mixed
         */
        public function getShippingSentFlg()
        {
            return $this->shipping_sent_flg;
        }

        /**
         * @param mixed $shipping_sent_flg
         */
        public function setShippingSentFlg($shipping_sent_flg)
        {
            $this->shipping_sent_flg = $shipping_sent_flg;
        }

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
         * @return int
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
    }
}
