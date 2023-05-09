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
         * @return string
         */
        public function getJan(): string
        {
            return $this->jan;
        }

        /**
         * @param string $jan
         */
        public function setJan(string $jan): void
        {
            $this->jan = $jan;
        }

        /**
         * @return string
         */
        public function getMkrcd(): string
        {
            return $this->mkrcd;
        }

        /**
         * @param string $mkrcd
         */
        public function setMkrcd(string $mkrcd): void
        {
            $this->mkrcd = $mkrcd;
        }

        /**
         * @return string
         */
        public function getGrade(): string
        {
            return $this->grade;
        }

        /**
         * @param string $grade
         */
        public function setGrade(string $grade): void
        {
            $this->grade = $grade;
        }

        /**
         * @return string
         */
        public function getNatStockNum(): string
        {
            return $this->nat_stock_num;
        }

        /**
         * @param string $nat_stock_num
         */
        public function setNatStockNum(string $nat_stock_num): void
        {
            $this->nat_stock_num = $nat_stock_num;
        }

        /**
         * @return string
         */
        public function getDeliveryDate(): string
        {
            return $this->delivery_date;
        }

        /**
         * @param string $delivery_date
         */
        public function setDeliveryDate(string $delivery_date): void
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
        public function setQuanlity($quanlity): void
        {
            $this->quanlity = $quanlity;
        }

        /**
         * @return string
         */
        public function getOrderLot(): string
        {
            return $this->order_lot;
        }

        /**
         * @param string $order_lot
         */
        public function setOrderLot(string $order_lot): void
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
        public function setUnitPrice($unit_price): void
        {
            $this->unit_price = $unit_price;
        }

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
         * @return string
         */
        public function getCatalogCode(): string
        {
            return $this->catalog_code;
        }

        /**
         * @param string $catalog_code
         */
        public function setCatalogCode(string $catalog_code): void
        {
            $this->catalog_code = $catalog_code;
        }

        /**
         * @return string
         */
        public function getColor(): string
        {
            return $this->color;
        }

        /**
         * @param string $color
         */
        public function setColor(string $color): void
        {
            $this->color = $color;
        }

        /**
         * @return string
         */
        public function getSize(): string
        {
            return $this->size;
        }

        /**
         * @param string $size
         */
        public function setSize(string $size): void
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
        public function setStockNum($stock_num): void
        {
            $this->stock_num = $stock_num;
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
         * @return \DateTime
         */
        public function getUpdateDate(): \DateTime
        {
            return $this->update_date;
        }

        /**
         * @param \DateTime $update_date
         */
        public function setUpdateDate(\DateTime $update_date): void
        {
            $this->update_date = $update_date;
        }
    }
}
