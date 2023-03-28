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

if (!class_exists('\Customize\Entity\MstShippingWSEOS', false)) {
    /**
     * MstShippingWSEOS
     *
     * @ORM\Table(name="mst_shipping_ws_eos")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstShippingWSEOSRepository")
     */
    class MstShippingWSEOS extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="system_code",nullable=true, type="string", length=1, options={"comment":"システムコード"})
         */
        private $system_code;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_company_code",nullable=true, type="string", length=4, options={"comment":"売手企業コード"})
         */
        private $sales_company_code;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_shop_code",nullable=false, type="string", length=4, options={"comment":"売手支店コード"})
         */
        private $sales_shop_code;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_no",nullable=true, type="string", length=20, options={"comment":"納品伝票番号"})
         */
        private $delivery_no;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_type",nullable=true, type="string", length=1, options={"comment":"納品伝票種別区分"})
         */
        private $delivery_type;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_flag_tmp",nullable=true, type="string", length=1, options={"comment":"仮伝票フラグ"})
         */
        private $delivery_flag_tmp;
        /**
         * @var string
         *
         * @ORM\Column(name="order_company_code",nullable=true, type="string", length=3, options={"comment":"発注企業コード"})
         */
        private $order_company_code;
        /**
         * @var string
         *
         * @ORM\Column(name="order_shop_code",nullable=true, type="string", length=3, options={"comment":"発注店舗コード"})
         */
        private $order_shop_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_company_code",nullable=true, type="string", length=3, options={"comment":"出荷先企業コード"})
         */
        private $shipping_company_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_shop_code",nullable=true, type="string", length=3, options={"comment":"出荷先支店コード"})
         */
        private $shipping_shop_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_name",nullable=true, type="string", length=50, options={"comment":"納入先名"})
         */
        private $shipping_name;
        /**
         * @ORM\Column(name="import_type",type="integer",nullable=true, options={"comment":"取込区分"  })
         */
        private $import_type;
        /**
         * @ORM\Column(name="system_code1",type="integer",nullable=true, options={"comment":"システムコード"  })
         */
        private $system_code1;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_company_code1",nullable=true, type="string", length=4, options={"comment":"売手企業コード"})
         */
        private $sales_company_code1;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_ship_code1",nullable=true, type="string", length=4, options={"comment":"売手支店コード"})
         */
        private $sales_ship_code1;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_no1",nullable=true, type="string", length=20, options={"comment":"納品伝票番号"})
         */
        private $delivery_no1;
        /**
         * @ORM\Column(name="delivery_line_no",type="integer",nullable=true, options={"comment":"納品伝票行番号"  })
         */
        private $delivery_line_no;
        /**
         * @ORM\Column(name="delivery_type1",type="integer",nullable=true, options={"comment":"納品伝票種別区分"  })
         */
        private $delivery_type1;
        /**
         * @var string
         *
         * @ORM\Column(name="order_type",nullable=true, type="string", length=1, options={"comment":"伝票区分"})
         */
        private $order_type;
        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=20, options={"unsigned":true, "comment":"注文伝票番号"})
         * @ORM\Id
         */
        private $order_no;
        /**
         * @var integer
         *
         * @ORM\Column(name="order_line_no", type="integer", options={"unsigned":true, "comment":"注文伝票行番号"})
         * @ORM\Id
         */
        private $order_line_no;
        /**
         * @var string
         *
         * @ORM\Column(name="order_flag",nullable=true, type="string", length=1, options={"comment":"発注フラグ"})
         */
        private $order_flag;
        /**
         * @var string
         *
         * @ORM\Column(name="order_staff_name",nullable=true, type="string", length=20, options={"comment":"発注担当者名"})
         */
        private $order_staff_name;
        /**
         * @var string
         *
         * @ORM\Column(name="order_shop_name",nullable=true, type="string", length=50, options={"comment":"発注店舗名"})
         */
        private $order_shop_name;
        /**
         * @var string
         *
         * @ORM\Column(name="make_code",nullable=true, type="string", length=10, options={"comment":"メーカーコード"})
         */
        private $make_code;
        /**
         * @var string
         *
         * @ORM\Column(name="maker_name",nullable=true, type="string", length=40, options={"comment":"メーカー名"})
         */
        private $maker_name;
        /**
         * @var string
         *
         * @ORM\Column(name="product_name",nullable=true, type="string", length=50, options={"comment":"商品名"})
         */
        private $product_name;
        /**
         * @ORM\Column(name="order_num",type="integer",nullable=false, options={"comment":"発注数量"})
         */
        protected $order_num;
        /**
         * @ORM\Column(name="order_price",type="integer",nullable=false, options={"comment":"発注単価" ,"default":0 })
         */
        private $order_price;
        /**
         * @ORM\Column(name="order_amount",type="integer",nullable=false, options={"comment":"発注金額" ,"default":0 })
         */
        private $order_amount;
        /**
         * @ORM\Column(name="delivery_num",type="integer", nullable=false,  options={"comment":"納入数量"})
         */
        protected $delivery_num;
        /**
         * @ORM\Column(name="delivery_price",type="integer",nullable=false, options={"comment":"納入単価" ,"default":0 })
         */
        private $delivery_price;
        /**
         * @ORM\Column(name="delivery_amount",type="integer",nullable=false, options={"comment":"納入金額" ,"default":0 })
         */
        private $delivery_amount;
        /**
         * @ORM\Column(name="tax_type",type="integer",nullable=true, options={"comment":"消費税区分"  })
         */
        private $tax_type;
        /**
         * @var string
         *
         * @ORM\Column(name="remarks_line_no",nullable=true, type="string", length=30, options={"comment":"明細備考"})
         */
        private $remarks_line_no;
        /**
         * @var string
         *
         * @ORM\Column(name="jan_code",nullable=false, type="string", length=13, options={"comment":"ＪＡＮコード"})
         */
        private $jan_code;
        /**
         * @var string
         *
         * @ORM\Column(name="unit_code",nullable=true, type="string", length=8, options={"comment":"単位コード"})
         */
        private $unit_code;
        /**
         * @ORM\Column(name="shipping_num",type="integer",nullable=true, options={"comment":"入数"  })
         */
        private $shipping_num;
        /**
         * @ORM\Column(name="order_unit_num",type="integer",nullable=true, options={"comment":"発注単位数"  })
         */
        private $order_unit_num;
        /**
         * @var string
         *
         * @ORM\Column(name="product_maker_code",nullable=true, type="string", length=33, options={"comment":"メーカー型番"})
         */
        private $product_maker_code;
        /**
         * @var string
         *
         * @ORM\Column(name="open_price_type",nullable=true, type="string", length=1, options={"comment":"オープン価格区分"})
         */
        private $open_price_type;
        /**
         * @ORM\Column(name="price_basic",type="integer",nullable=true, options={"comment":"標準卸価格"  })
         */
        private $price_basic;
        /**
         * @ORM\Column(name="price_list",type="integer",nullable=true, options={"comment":"定価"  })
         */
        private $price_list;
        /**
         * @ORM\Column(name="shipping_send_flg",type="integer",nullable=true, options={"comment":"出荷送信対象フラグ０：送信非対象(初期値)　１：送信対象" ,"default":0 })
         */
        private $shipping_send_flg;
        /**
         * @ORM\Column(name="shipping_sent_flg",type="integer",nullable=true, options={"comment":"出荷送信済フラグ０：未送信(初期値)　１：送信済" ,"default":0 })
         */
        private $shipping_sent_flg;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no",nullable=true, type="string", length=10, options={"comment":"出荷指示№"})
         */
        private $shipping_no;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ更新日時'")
         */
        private $update_date;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_day", type="string", nullable=true, options={"comment":"伝票日付"})
         */
        private $delivery_day;
        /**
         * @var string
         *
         * @ORM\Column(name="order_date",nullable=true, type="string", options={"comment":"受注日"})
         */
        private $order_date;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date",nullable=true, type="string", options={"comment":"出荷日"})
         */
        private $shipping_date;

        /**
         * @return string
         */
        public function getSystemCode()
        {
            return $this->system_code;
        }

        /**
         * @param $system_code
         */
        public function setSystemCode($system_code)
        {
            $this->system_code = $system_code;
        }

        /**
         * @return string
         */
        public function getSalesCompanyCode()
        {
            return $this->sales_company_code;
        }

        /**
         * @param $sales_company_code
         */
        public function setSalesCompanyCode($sales_company_code)
        {
            $this->sales_company_code = $sales_company_code;
        }

        /**
         * @return string
         */
        public function getSalesShopCode()
        {
            return $this->sales_shop_code;
        }

        /**
         * @param $sales_shop_code
         */
        public function setSalesShopCode($sales_shop_code)
        {
            $this->sales_shop_code = $sales_shop_code;
        }

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
        public function getDeliveryType()
        {
            return $this->delivery_type;
        }

        /**
         * @param $delivery_type
         */
        public function setDeliveryType($delivery_type)
        {
            $this->delivery_type = $delivery_type;
        }

        /**
         * @return string
         */
        public function getDeliveryFlagTmp()
        {
            return $this->delivery_flag_tmp;
        }

        /**
         * @param $delivery_flag_tmp
         */
        public function setDeliveryFlagTmp($delivery_flag_tmp)
        {
            $this->delivery_flag_tmp = $delivery_flag_tmp;
        }

        /**
         * @return string
         */
        public function getOrderCompanyCode()
        {
            return $this->order_company_code;
        }

        /**
         * @param $order_company_code
         */
        public function setOrderCompanyCode($order_company_code)
        {
            $this->order_company_code = $order_company_code;
        }

        /**
         * @return string
         */
        public function getOrderShopCode()
        {
            return $this->order_shop_code;
        }

        /**
         * @param $order_shop_code
         */
        public function setOrderShopCode($order_shop_code)
        {
            $this->order_shop_code = $order_shop_code;
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
         * @return string
         */
        public function getShippingShopCode()
        {
            return $this->shipping_shop_code;
        }

        /**
         * @param $shipping_shop_code
         */
        public function setShippingShopCode($shipping_shop_code)
        {
            $this->shipping_shop_code = $shipping_shop_code;
        }

        /**
         * @return string
         */
        public function getShippingName()
        {
            return $this->shipping_name;
        }

        /**
         * @param $shipping_name
         */
        public function setShippingName($shipping_name)
        {
            $this->shipping_name = $shipping_name;
        }

        /**
         * @return mixed
         */
        public function getImportType()
        {
            return $this->import_type;
        }

        /**
         * @param mixed $import_type
         */
        public function setImportType($import_type)
        {
            $this->import_type = $import_type;
        }

        /**
         * @return mixed
         */
        public function getSystemCode1()
        {
            return $this->system_code1;
        }

        /**
         * @param mixed $system_code1
         */
        public function setSystemCode1($system_code1)
        {
            $this->system_code1 = $system_code1;
        }

        /**
         * @return string
         */
        public function getSalesCompanyCode1()
        {
            return $this->sales_company_code1;
        }

        /**
         * @param $sales_company_code1
         */
        public function setSalesCompanyCode1($sales_company_code1)
        {
            $this->sales_company_code1 = $sales_company_code1;
        }

        /**
         * @return string
         */
        public function getSalesShipCode1()
        {
            return $this->sales_ship_code1;
        }

        /**
         * @param $sales_ship_code1
         */
        public function setSalesShipCode1($sales_ship_code1)
        {
            $this->sales_ship_code1 = $sales_ship_code1;
        }

        /**
         * @return string
         */
        public function getDeliveryNo1()
        {
            return $this->delivery_no1;
        }

        /**
         * @param $delivery_no1
         */
        public function setDeliveryNo1($delivery_no1)
        {
            $this->delivery_no1 = $delivery_no1;
        }

        /**
         * @return mixed
         */
        public function getDeliveryLineNo()
        {
            return $this->delivery_line_no;
        }

        /**
         * @param mixed $delivery_line_no
         */
        public function setDeliveryLineNo($delivery_line_no)
        {
            $this->delivery_line_no = $delivery_line_no;
        }

        /**
         * @return mixed
         */
        public function getDeliveryType1()
        {
            return $this->delivery_type1;
        }

        /**
         * @param mixed $delivery_type1
         */
        public function setDeliveryType1($delivery_type1)
        {
            $this->delivery_type1 = $delivery_type1;
        }

        /**
         * @return string
         */
        public function getOrderType()
        {
            return $this->order_type;
        }

        /**
         * @param $order_type
         */
        public function setOrderType($order_type)
        {
            $this->order_type = $order_type;
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
         * @return int
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
         * @return string
         */
        public function getOrderFlag()
        {
            return $this->order_flag;
        }

        /**
         * @param $order_flag
         */
        public function setOrderFlag($order_flag)
        {
            $this->order_flag = $order_flag;
        }

        /**
         * @return string
         */
        public function getOrderStaffName()
        {
            return $this->order_staff_name;
        }

        /**
         * @param $order_staff_name
         */
        public function setOrderStaffName($order_staff_name)
        {
            $this->order_staff_name = $order_staff_name;
        }

        /**
         * @return string
         */
        public function getOrderShopName()
        {
            return $this->order_shop_name;
        }

        /**
         * @param $order_shop_name
         */
        public function setOrderShopName($order_shop_name)
        {
            $this->order_shop_name = $order_shop_name;
        }

        /**
         * @return string
         */
        public function getMakeCode()
        {
            return $this->make_code;
        }

        /**
         * @param $make_code
         */
        public function setMakeCode($make_code)
        {
            $this->make_code = $make_code;
        }

        /**
         * @return string
         */
        public function getMakerName()
        {
            return $this->maker_name;
        }

        /**
         * @param $maker_name
         */
        public function setMakerName($maker_name)
        {
            $this->maker_name = $maker_name;
        }

        /**
         * @return string
         */
        public function getProductName()
        {
            return $this->product_name;
        }

        /**
         * @param $product_name
         */
        public function setProductName($product_name)
        {
            $this->product_name = $product_name;
        }

        /**
         * @return mixed
         */
        public function getOrderNum()
        {
            return $this->order_num;
        }

        /**
         * @param mixed $order_num
         */
        public function setOrderNum($order_num)
        {
            $this->order_num = $order_num;
        }

        /**
         * @return mixed
         */
        public function getOrderPrice()
        {
            return $this->order_price;
        }

        /**
         * @param mixed $order_price
         */
        public function setOrderPrice($order_price)
        {
            $this->order_price = $order_price;
        }

        /**
         * @return mixed
         */
        public function getOrderAmount()
        {
            return $this->order_amount;
        }

        /**
         * @param mixed $order_amount
         */
        public function setOrderAmount($order_amount)
        {
            $this->order_amount = $order_amount;
        }

        /**
         * @return mixed
         */
        public function getDeliveryNum()
        {
            return $this->delivery_num;
        }

        /**
         * @param mixed $delivery_num
         */
        public function setDeliveryNum($delivery_num)
        {
            $this->delivery_num = $delivery_num;
        }

        /**
         * @return mixed
         */
        public function getDeliveryPrice()
        {
            return $this->delivery_price;
        }

        /**
         * @param mixed $delivery_price
         */
        public function setDeliveryPrice($delivery_price)
        {
            $this->delivery_price = $delivery_price;
        }

        /**
         * @return mixed
         */
        public function getDeliveryAmount()
        {
            return $this->delivery_amount;
        }

        /**
         * @param mixed $delivery_amount
         */
        public function setDeliveryAmount($delivery_amount)
        {
            $this->delivery_amount = $delivery_amount;
        }

        /**
         * @return mixed
         */
        public function getTaxType()
        {
            return $this->tax_type;
        }

        /**
         * @param mixed $tax_type
         */
        public function setTaxType($tax_type)
        {
            $this->tax_type = $tax_type;
        }

        /**
         * @return string
         */
        public function getRemarksLineNo()
        {
            return $this->remarks_line_no;
        }

        /**
         * @param $remarks_line_no
         */
        public function setRemarksLineNo($remarks_line_no)
        {
            $this->remarks_line_no = $remarks_line_no;
        }

        /**
         * @return string
         */
        public function getJanCode()
        {
            return $this->jan_code;
        }

        /**
         * @param $jan_code
         */
        public function setJanCode($jan_code)
        {
            $this->jan_code = $jan_code;
        }

        /**
         * @return string
         */
        public function getUnitCode()
        {
            return $this->unit_code;
        }

        /**
         * @param $unit_code
         */
        public function setUnitCode($unit_code)
        {
            $this->unit_code = $unit_code;
        }

        /**
         * @return mixed
         */
        public function getShippingNum()
        {
            return $this->shipping_num;
        }

        /**
         * @param mixed $shipping_num
         */
        public function setShippingNum($shipping_num)
        {
            $this->shipping_num = $shipping_num;
        }

        /**
         * @return mixed
         */
        public function getOrderUnitNum()
        {
            return $this->order_unit_num;
        }

        /**
         * @param mixed $order_unit_num
         */
        public function setOrderUnitNum($order_unit_num)
        {
            $this->order_unit_num = $order_unit_num;
        }

        /**
         * @return string
         */
        public function getProductMakerCode()
        {
            return $this->product_maker_code;
        }

        /**
         * @param $product_maker_code
         */
        public function setProductMakerCode($product_maker_code)
        {
            $this->product_maker_code = $product_maker_code;
        }

        /**
         * @return string
         */
        public function getOpenPriceType()
        {
            return $this->open_price_type;
        }

        /**
         * @param $open_price_type
         */
        public function setOpenPriceType($open_price_type)
        {
            $this->open_price_type = $open_price_type;
        }

        /**
         * @return mixed
         */
        public function getPriceBasic()
        {
            return $this->price_basic;
        }

        /**
         * @param mixed $price_basic
         */
        public function setPriceBasic($price_basic)
        {
            $this->price_basic = $price_basic;
        }

        /**
         * @return mixed
         */
        public function getPriceList()
        {
            return $this->price_list;
        }

        /**
         * @param mixed $price_list
         */
        public function setPriceList($price_list)
        {
            $this->price_list = $price_list;
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
         * @return string
         */
        public function getOrderDate()
        {
            return $this->order_date;
        }

        /**
         * @param $order_date
         */
        public function setOrderDate($order_date)
        {
            $this->order_date = $order_date;
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
    }
}
