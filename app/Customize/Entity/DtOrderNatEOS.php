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

if (!class_exists('\Customize\Entity\DtOrderNatEOS', false)) {
    /**
     * DtOrderNatEOS
     *
     * @ORM\Table(name="dt_order_nat_eos")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderNatEOSRepository")
     */
    class DtOrderNatEOS extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="reqcd", type="string", length=13,options={"comment":"発注番号'=客先発注№"}, nullable=false)
         * @ORM\Id
         */
        private $reqcd;
        /**
         * @var string
         *
         * @ORM\Column(name="jan", type="string", length=13,options={"comment":"JANコード"}, nullable=false)
         */
        private $jan;
        /**
         * @var string
         *
         * @ORM\Column(name="mkrcd",nullable=true, type="string", length=20, options={"comment":"品番"})
         */
        private $mkrcd;
        /**
         * @var string
         *
         * @ORM\Column(name="natcd",nullable=false, type="string", length=7, options={"comment":"ナチュラム商品番号"})
         */
        private $natcd;
        /**
         * @ORM\Column(name="qty",type="integer",nullable=true, options={"comment":"発注数"  })
         */
        private $qty;
        /**
         * @ORM\Column(name="cost",type="integer",nullable=true, options={"comment":"発注単価"  })
         */
        private $cost;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_day",nullable=true, type="string", length=8, options={"comment":"納期yyyymmdd"})
         */
        private $delivery_day;
        /**
         * @ORM\Column(name="order_lineno",type="integer",nullable=true, options={"comment":"発注明細番号"  })
         * @ORM\Id
         */
        private $order_lineno;
        /**
         * @var string
         *
         * @ORM\Column(name="order_import_day",nullable=true, type="string", length=8, options={"comment":"取込日付"})
         */
        private $order_import_day;
        /**
         * @ORM\Column(name="order_registed_flg",type="integer",nullable=true, options={"comment":"注文登録済フラグ ０：未登録(初期値)　１：登録済" ,"default":0 })
         */
        private $order_registed_flg;
        /**
         * @ORM\Column(name="shipping_sent_flg",type="integer",nullable=true, options={"comment":"出荷送信済フラグ ０：未送信(初期値)　１：送信済" ,"default":0 })
         */
        private $shipping_sent_flg;
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code",nullable=true, type="string", length=25, options={"comment":"顧客コード"})
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=true, type="string", length=25, options={"comment":"出荷先コード"})
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=true, type="string", length=25, options={"comment":"届け先コード"})
         */
        private $otodoke_code;
        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=25, options={"comment":"製品コード"})
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="error_type",nullable=true, type="string", length=1, options={"comment":"エラー区分"})
         */
        private $error_type;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content1",nullable=true, type="string", length=50, options={"comment":"エラー内容１"})
         */
        private $error_content1;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content2",nullable=true, type="string", length=50, options={"comment":"エラー内容２"})
         */
        private $error_content2;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content3",nullable=true, type="string", length=50, options={"comment":"エラー内容3"})
         */
        private $error_content3;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content4",nullable=true, type="string", length=50, options={"comment":"エラー内容4"})
         */
        private $error_content4;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content5",nullable=true, type="string", length=50, options={"comment":"エラー内容5"})
         */
        private $error_content5;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content6",nullable=true, type="string", length=50, options={"comment":"エラー内容6"})
         */
        private $error_content6;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content7",nullable=true, type="string", length=50, options={"comment":"エラー内容7"})
         */
        private $error_content7;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content8",nullable=true, type="string", length=50, options={"comment":"エラー内容8"})
         */
        private $error_content8;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content9",nullable=true, type="string", length=50, options={"comment":"エラー内容9"})
         */
        private $error_content9;
        /**
         * @var string
         *
         * @ORM\Column(name="error_content10",nullable=true, type="string", length=50, options={"comment":"エラー内容10"})
         */
        private $error_content10;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP(3) DEFAULT CURRENT_TIMESTAMP COMMENT 'データ登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP(3) DEFAULT CURRENT_TIMESTAMP COMMENT 'データ更新日時'")
         */
        private $update_date;
        /**
         * @var int
         *
         * @ORM\Column(name="shipping_num", type="integer", nullable=false, options={"default":0})
         */
        private $shipping_num;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date",nullable=true, type="string", length=10, options={"comment":"出荷日"})
         */
        private $shipping_date;

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

        /**
         * @return mixed
         */
        public function getOrderLineno()
        {
            return $this->order_lineno;
        }

        /**
         * @param mixed $order_lineno
         */
        public function setOrderLineno($order_lineno)
        {
            $this->order_lineno = $order_lineno;
        }

        /**
         * @return string
         */
        public function getOrderImportDay()
        {
            return $this->order_import_day;
        }

        /**
         * @param $order_import_day
         */
        public function setOrderImportDay($order_import_day)
        {
            $this->order_import_day = $order_import_day;
        }

        /**
         * @return mixed
         */
        public function getOrderRegistedFlg()
        {
            return $this->order_registed_flg;
        }

        /**
         * @param mixed $order_registed_flg
         */
        public function setOrderRegistedFlg($order_registed_flg)
        {
            $this->order_registed_flg = $order_registed_flg;
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
         * @return string
         */
        public function getOtodokeCode()
        {
            return $this->otodoke_code;
        }

        /**
         * @param $otodoke_code
         */
        public function setOtodokeCode($otodoke_code)
        {
            $this->otodoke_code = $otodoke_code;
        }

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
         * @return string
         */
        public function getErrorType()
        {
            return $this->error_type;
        }

        /**
         * @param $error_type
         */
        public function setErrorType($error_type)
        {
            $this->error_type = $error_type;
        }

        /**
         * @return string
         */
        public function getErrorContent1()
        {
            return $this->error_content1;
        }

        /**
         * @param $error_content1
         */
        public function setErrorContent1($error_content1)
        {
            $this->error_content1 = $error_content1;
        }

        /**
         * @return string
         */
        public function getErrorContent2()
        {
            return $this->error_content2;
        }

        /**
         * @param $error_content2
         */
        public function setErrorContent2($error_content2)
        {
            $this->error_content2 = $error_content2;
        }

        /**
         * @return string
         */
        public function getErrorContent3()
        {
            return $this->error_content3;
        }

        /**
         * @param $error_content3
         */
        public function setErrorContent3($error_content3)
        {
            $this->error_content3 = $error_content3;
        }

        /**
         * @return string
         */
        public function getErrorContent4()
        {
            return $this->error_content4;
        }

        /**
         * @param $error_content4
         */
        public function setErrorContent4($error_content4)
        {
            $this->error_content4 = $error_content4;
        }

        /**
         * @return string
         */
        public function getErrorContent5()
        {
            return $this->error_content5;
        }

        /**
         * @param $error_content5
         */
        public function setErrorContent5($error_content5)
        {
            $this->error_content5 = $error_content5;
        }

        /**
         * @return string
         */
        public function getErrorContent6()
        {
            return $this->error_content6;
        }

        /**
         * @param $error_content6
         */
        public function setErrorContent6($error_content6)
        {
            $this->error_content6 = $error_content6;
        }

        /**
         * @return string
         */
        public function getErrorContent7()
        {
            return $this->error_content7;
        }

        /**
         * @param $error_content7
         */
        public function setErrorContent7($error_content7)
        {
            $this->error_content7 = $error_content7;
        }

        /**
         * @return string
         */
        public function getErrorContent8()
        {
            return $this->error_content8;
        }

        /**
         * @param $error_content8
         */
        public function setErrorContent8($error_content8)
        {
            $this->error_content8 = $error_content8;
        }

        /**
         * @return string
         */
        public function getErrorContent9()
        {
            return $this->error_content9;
        }

        /**
         * @param $error_content9
         */
        public function setErrorContent9($error_content9)
        {
            $this->error_content9 = $error_content9;
        }

        /**
         * @return string
         */
        public function getErrorContent10()
        {
            return $this->error_content10;
        }

        /**
         * @param $error_content10
         */
        public function setErrorContent10($error_content10)
        {
            $this->error_content10 = $error_content10;
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
