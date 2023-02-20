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

if (!class_exists('\Customize\Entity\MstDelivery', false)) {
    /**
     * MstDelivery
     *
     * @ORM\Table(name="mst_delivery")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstDeliveryRepository")
     */
    class MstDelivery extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_no", type="string", length=10,options={"comment":"納品書№"}, nullable=false)
         * @ORM\Id
         */
        protected $delivery_no;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_date",nullable=true, type="string", length=15, options={"comment":"納品日"})
         */
        private $delivery_date;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_post_code",nullable=true, type="string", length=8, options={"comment":"宛先郵便番号"})
         */
        private $deli_post_code;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_addr01",nullable=true, type="string", length=50, options={"comment":"宛先住所１"})
         */
        private $deli_addr01;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_addr02",nullable=true, type="string", length=50, options={"comment":"宛先住所２"})
         */
        private $deli_addr02;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_addr03",nullable=true, type="string", length=50, options={"comment":"宛先住所3"})
         */
        private $deli_addr03;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_company_name",nullable=true, type="string", length=255, options={"comment":"宛先会社名"})
         */
        private $deli_company_name;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_department",nullable=true, type="string", length=255, options={"comment":"宛先部署"})
         */
        private $deli_department;
        /**
         * @var string
         *
         * @ORM\Column(name="postal_code",nullable=true, type="string", length=8, options={"comment":"郵便番号"})
         */
        private $postal_code;
        /**
         * @var string
         *
         * @ORM\Column(name="addr01",nullable=true, type="string", length=50, options={"comment":"住所1"})
         */
        private $addr01;
        /**
         * @var string
         *
         * @ORM\Column(name="addr02",nullable=true, type="string", length=50, options={"comment":"住所2"})
         */
        private $addr02;
        /**
         * @var string
         *
         * @ORM\Column(name="addr03",nullable=true, type="string", length=50, options={"comment":"住所3"})
         */
        private $addr03;
        /**
         * @var string
         *
         * @ORM\Column(name="company_name",nullable=true, type="string", length=50, options={"comment":"会社名"})
         */
        private $company_name;
        /**
         * @var string
         *
         * @ORM\Column(name="department",nullable=true, type="string", length=50, options={"comment":"部署"})
         */
        private $department;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_lineno",nullable=false, type="string", length=10, options={"comment":"明細番号"})
         */
        private $delivery_lineno;
        /**
         * @var string
         *
         * @ORM\Column(name="sale_type",nullable=true, type="string", length=15, options={"comment":"売上区分"})
         */
        private $sale_type;
        /**
         * @var string
         *
         * @ORM\Column(name="item_no",nullable=true, type="string", length=10, options={"comment":"品目№"})
         */
        private $item_no;
        /**
         * @var string
         *
         * @ORM\Column(name="item_name",nullable=true, type="string", length=50, options={"comment":"品目名称"})
         */
        private $item_name;
        /**
         * @ORM\Column(type="integer",nullable=true, options={"comment":"数量"})
         */
        protected $quanlity;
        /**
         * @var string
         *
         * @ORM\Column(name="unit",nullable=true, type="string", length=15, options={"comment":"単位"})
         */
        private $unit;
        /**
         * @ORM\Column(type="float",nullable=true, columnDefinition="FLOAT",  options={"comment":"金額"})
         */
        protected $amount;

        /**
         * @ORM\Column(name="unit_price",type="float",nullable=true, columnDefinition="FLOAT",  options={"comment":"金額"})
         */
        protected $unit_price;

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
         * @ORM\Column(type="integer",nullable=true, options={"comment":"消費税"})
         */
        protected $tax;

        /**
         * @ORM\Column(type="string")
         */
        protected $shipping_no;

        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=8, options={"comment":"御社注文№"})
         */
        private $order_no;

        /**
         * @return string
         */
        public function getId(): string
        {
            return $this->id;
        }

        /**
         * @param string $id
         */
        public function setId(string $id): void
        {
            $this->id = $id;
        }
        /**
         * @var string
         *
         * @ORM\Column(name="item_remark",nullable=true, type="string", length=255, options={"comment":"明細備考"})
         */
        private $item_remark;
        /**
         * @var string
         *
         * @ORM\Column(name="footer_remark1",nullable=true, type="string", length=500, options={"comment":"フッター備考１"})
         */
        private $footer_remark1;
        /**
         * @var string
         *
         * @ORM\Column(name="footer_remark2",nullable=true, type="string", length=500, options={"comment":"フッター備考２"})
         */
        private $footer_remark2;
        /**
         * @ORM\Column(type="float",nullable=true, columnDefinition="FLOAT",  options={"comment":"納品書合計額"})
         */
        protected $total_amount;
        /**
         * @var string
         *
         * @ORM\Column(name="shiping_name",nullable=true, type="string", length=100, options={"comment":"出荷先"})
         */
        private $shiping_name;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_name",nullable=true, type="string", length=100, options={"comment":"届け先"})
         */
        private $otodoke_name;
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
        public function getDeliveryNo(): string
        {
            return $this->delivery_no;
        }

        /**
         * @param string $delivery_no
         */
        public function setDeliveryNo(string $delivery_no): void
        {
            $this->delivery_no = $delivery_no;
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
         * @return string
         */
        public function getDeliPostCode(): string
        {
            return $this->deli_post_code;
        }

        /**
         * @param string $deli_post_code
         */
        public function setDeliPostCode(string $deli_post_code): void
        {
            $this->deli_post_code = $deli_post_code;
        }

        /**
         * @return string
         */
        public function getDeliAddr01(): string
        {
            return $this->deli_addr01;
        }

        /**
         * @param string $deli_addr01
         */
        public function setDeliAddr01(string $deli_addr01): void
        {
            $this->deli_addr01 = $deli_addr01;
        }

        /**
         * @return string
         */
        public function getDeliAddr02(): string
        {
            return $this->deli_addr02;
        }

        /**
         * @param string $deli_addr02
         */
        public function setDeliAddr02(string $deli_addr02): void
        {
            $this->deli_addr02 = $deli_addr02;
        }

        /**
         * @return string
         */
        public function getDeliAddr03(): string
        {
            return $this->deli_addr03;
        }

        /**
         * @param string $deli_addr03
         */
        public function setDeliAddr03(string $deli_addr03): void
        {
            $this->deli_addr03 = $deli_addr03;
        }

        /**
         * @return string
         */
        public function getDeliCompanyName(): string
        {
            return $this->deli_company_name;
        }

        /**
         * @param string $deli_company_name
         */
        public function setDeliCompanyName(string $deli_company_name): void
        {
            $this->deli_company_name = $deli_company_name;
        }

        /**
         * @return string
         */
        public function getDeliDepartment(): string
        {
            return $this->deli_department;
        }

        /**
         * @param string $deli_department
         */
        public function setDeliDepartment(string $deli_department): void
        {
            $this->deli_department = $deli_department;
        }

        /**
         * @return string
         */
        public function getPostalCode(): string
        {
            return $this->postal_code;
        }

        /**
         * @param string $postal_code
         */
        public function setPostalCode(string $postal_code): void
        {
            $this->postal_code = $postal_code;
        }

        /**
         * @return string
         */
        public function getAddr01(): string
        {
            return $this->addr01;
        }

        /**
         * @param string $addr01
         */
        public function setAddr01(string $addr01): void
        {
            $this->addr01 = $addr01;
        }

        /**
         * @return string
         */
        public function getAddr02(): string
        {
            return $this->addr02;
        }

        /**
         * @param string $addr02
         */
        public function setAddr02(string $addr02): void
        {
            $this->addr02 = $addr02;
        }

        /**
         * @return string
         */
        public function getAddr03(): string
        {
            return $this->addr03;
        }

        /**
         * @param string $addr03
         */
        public function setAddr03(string $addr03): void
        {
            $this->addr03 = $addr03;
        }

        /**
         * @return string
         */
        public function getCompanyName(): string
        {
            return $this->company_name;
        }

        /**
         * @param string $company_name
         */
        public function setCompanyName(string $company_name): void
        {
            $this->company_name = $company_name;
        }

        /**
         * @return string
         */
        public function getDepartment(): string
        {
            return $this->department;
        }

        /**
         * @param string $department
         */
        public function setDepartment(string $department): void
        {
            $this->department = $department;
        }

        /**
         * @return string
         */
        public function getDeliveryLineno(): string
        {
            return $this->delivery_lineno;
        }

        /**
         * @param string $delivery_lineno
         */
        public function setDeliveryLineno(string $delivery_lineno): void
        {
            $this->delivery_lineno = $delivery_lineno;
        }

        /**
         * @return string
         */
        public function getSaleType(): string
        {
            return $this->sale_type;
        }

        /**
         * @param string $sale_type
         */
        public function setSaleType(string $sale_type): void
        {
            $this->sale_type = $sale_type;
        }

        /**
         * @return string
         */
        public function getItemNo(): string
        {
            return $this->item_no;
        }

        /**
         * @param string $item_no
         */
        public function setItemNo(string $item_no): void
        {
            $this->item_no = $item_no;
        }

        /**
         * @return string
         */
        public function getItemName(): string
        {
            return $this->item_name;
        }

        /**
         * @param string $item_name
         */
        public function setItemName(string $item_name): void
        {
            $this->item_name = $item_name;
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
        public function getUnit(): string
        {
            return $this->unit;
        }

        /**
         * @param string $unit
         */
        public function setUnit(string $unit): void
        {
            $this->unit = $unit;
        }

        /**
         * @return mixed
         */
        public function getAmount()
        {
            return $this->amount;
        }

        /**
         * @param mixed $amount
         */
        public function setAmount($amount): void
        {
            $this->amount = $amount;
        }

        /**
         * @return mixed
         */
        public function getTax()
        {
            return $this->tax;
        }

        /**
         * @param mixed $tax
         */
        public function setTax($tax): void
        {
            $this->tax = $tax;
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
        public function getItemRemark(): string
        {
            return $this->item_remark;
        }

        /**
         * @param string $item_remark
         */
        public function setItemRemark(string $item_remark): void
        {
            $this->item_remark = $item_remark;
        }

        /**
         * @return string
         */
        public function getFooterRemark1(): string
        {
            return $this->footer_remark1;
        }

        /**
         * @param string $footer_remark1
         */
        public function setFooterRemark1(string $footer_remark1): void
        {
            $this->footer_remark1 = $footer_remark1;
        }

        /**
         * @return string
         */
        public function getFooterRemark2(): string
        {
            return $this->footer_remark2;
        }

        /**
         * @param string $footer_remark2
         */
        public function setFooterRemark2(string $footer_remark2): void
        {
            $this->footer_remark2 = $footer_remark2;
        }

        /**
         * @return mixed
         */
        public function getTotalAmount()
        {
            return $this->total_amount;
        }

        /**
         * @param mixed $total_amount
         */
        public function setTotalAmount($total_amount): void
        {
            $this->total_amount = $total_amount;
        }

        /**
         * @return string
         */
        public function getShipingName(): string
        {
            return $this->shiping_name;
        }

        /**
         * @param string $shiping_name
         */
        public function setShipingName(string $shiping_name): void
        {
            $this->shiping_name = $shiping_name;
        }

        /**
         * @return string
         */
        public function getOtodokeName(): string
        {
            return $this->otodoke_name;
        }

        /**
         * @param string $otodoke_name
         */
        public function setOtodokeName(string $otodoke_name): void
        {
            $this->otodoke_name = $otodoke_name;
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
