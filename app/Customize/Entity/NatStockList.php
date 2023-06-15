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

if (!class_exists('\Customize\Entity\NatStockList', false)) {
    /**
     * NatStockList
     *
     * @ORM\Table(name="nat_stock_list")
     * @ORM\Entity(repositoryClass="Customize\Repository\NatStockListRepository")
     */
    class NatStockList extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="jan", type="string", length=13,options={"comment":"JANコード"}, nullable=false)
         * @ORM\Id
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
         * @ORM\Column(name="grade",nullable=true, type="string", length=1, options={"comment":"グレード"})
         */
        private $grade;
        /**
         * @var string
         *
         * @ORM\Column(name="nat_stock_num",nullable=true, type="string", length=10, options={"comment":"在庫数,トータル在庫＝０(在庫なし)→×,>=31→〇,<=30→△"})
         */
        private $nat_stock_num;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_date",nullable=true, type="string", length=8, options={"comment":"次回入荷日yyyymmdd"})
         */
        private $delivery_date;
        /**
         * @ORM\Column(name="quanlity",type="integer",nullable=true, options={"comment":"次回入荷数"  })
         */
        private $quanlity;
        /**
         * @var string
         *
         * @ORM\Column(name="order_lot",nullable=true, type="string", length=9, options={"comment":"発注ロット"})
         */
        private $order_lot;
        /**
         * @ORM\Column(name="unit_price",type="integer",nullable=true, options={"comment":"定価"  })
         */
        private $unit_price;
        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=20, options={"comment":"仕入先品番"})
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="catalog_code",nullable=true, type="string", length=4, options={"comment":"メーカーコード"})
         */
        private $catalog_code;
        /**
         * @var string
         *
         * @ORM\Column(name="color",nullable=true, type="string", length=50, options={"comment":"カラーコード"})
         */
        private $color;
        /**
         * @var string
         *
         * @ORM\Column(name="size",nullable=true, type="string", length=20, options={"comment":"サイズコード"})
         */
        private $size;
        /**
         * @ORM\Column(name="stock_num",type="integer",nullable=true, options={"comment":"メーカー在庫数"  })
         */
        private $stock_num;
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
         * @ORM\Column(name="price_s01",type="integer",nullable=true, options={"comment":"原価"  })
         */
        private $price_s01;

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
            return (string) $this->mkrcd;
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
        public function getGrade()
        {
            return (string) $this->grade;
        }

        /**
         * @param $grade
         */
        public function setGrade($grade)
        {
            $this->grade = $grade;
        }

        /**
         * @return string
         */
        public function getNatStockNum()
        {
            return (string) $this->nat_stock_num;
        }

        /**
         * @param $nat_stock_num
         */
        public function setNatStockNum($nat_stock_num)
        {
            $this->nat_stock_num = $nat_stock_num;
        }

        /**
         * @return string
         */
        public function getDeliveryDate()
        {
            return (string) $this->delivery_date;
        }

        /**
         * @param $delivery_date
         */
        public function setDeliveryDate($delivery_date)
        {
            $this->delivery_date = $delivery_date;
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
         * @return string
         */
        public function getOrderLot()
        {
            return (string) $this->order_lot;
        }

        /**
         * @param $order_lot
         */
        public function setOrderLot($order_lot)
        {
            $this->order_lot = $order_lot;
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
        public function getProductCode()
        {
            return (string) $this->product_code;
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
        public function getCatalogCode()
        {
            return (string) $this->catalog_code;
        }

        /**
         * @param $catalog_code
         */
        public function setCatalogCode($catalog_code)
        {
            $this->catalog_code = $catalog_code;
        }

        /**
         * @return string
         */
        public function getColor()
        {
            return (string) $this->color;
        }

        /**
         * @param $color
         */
        public function setColor($color)
        {
            $this->color = $color;
        }

        /**
         * @return string
         */
        public function getSize()
        {
            return (string) $this->size;
        }

        /**
         * @param $size
         */
        public function setSize($size)
        {
            $this->size = $size;
        }

        /**
         * @return mixed
         */
        public function getStockNum()
        {
            return $this->stock_num;
        }

        /**
         * @param mixed $stock_num
         */
        public function setStockNum($stock_num)
        {
            $this->stock_num = $stock_num;
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
         * @return mixed
         */
        public function getPriceS01()
        {
            return $this->price_s01;
        }

        /**
         * @param mixed $price_s01
         */
        public function setPriceS01($price_s01)
        {
            $this->price_s01 = $price_s01;
        }

    }
}
