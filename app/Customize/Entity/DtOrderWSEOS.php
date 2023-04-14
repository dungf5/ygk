<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use phpDocumentor\Reflection\Types\Integer;

if (!class_exists('\Customize\Entity\DtOrderWSEOS', false)) {
    /**
     * DtOrderWSEOS
     *
     * @ORM\Table(name="dt_order_ws_eos")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderWSEOSRepository")
     */
    class DtOrderWSEOS extends AbstractEntity
    {
        /**
         * @ORM\Column(name="order_type",type="integer",nullable=true, options={"comment":"伝票種別区分"  })
         */
        private $order_type;
        /**
         * @ORM\Column(name="web_order_type",type="integer",nullable=true, options={"comment":"ＷＥＢ発注区分"  })
         */
        private $web_order_type;
        /**
         * @var string
         *
         * @ORM\Column(name="order_date", type="string", nullable=true, options={"comment":"発注日付"})
         */
        private $order_date;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_date", type="string", nullable=true, options={"comment":"納入希望日"})
         */
        private $delivery_date;
        /**
         * @var string
         *
         * @ORM\Column(name="order_create_day", type="string", nullable=true, options={"comment":"作成日時"})
         */
        private $order_create_day;
        /**
         * @var string
         *
         * @ORM\Column(name="order_update_day", type="string", nullable=true, options={"comment":"最終更新日時"})
         */
        private $order_update_day;
        /**
         * @var string
         *
         * @ORM\Column(name="order_import_day", type="string", nullable=true, length=8, options={"comment":"取込日付"})
         */
        private $order_import_day;
        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=20,options={"comment":"注文伝票番号'=客先発注№(cus_order_no)"}, nullable=false)
         * @ORM\Id
         */
        private $order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="system_code",nullable=true, type="string", length=1, options={"comment":"システムコード"})
         */
        private $system_code;
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
         * @ORM\Column(name="order_staff_code",nullable=true, type="string", length=4, options={"comment":"発注担当者コード"})
         */
        private $order_staff_code;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_company_code",nullable=true, type="string", length=4, options={"comment":"売手企業コード"})
         */
        private $sales_company_code;
        /**
         * @var string
         *
         * @ORM\Column(name="sales_staff_code",nullable=true, type="string", length=4, options={"comment":"売手支店コード"})
         */
        private $sales_staff_code;
        /**
         * @var string
         *
         * @ORM\Column(name="order_company_name",nullable=true, type="string", length=50, options={"comment":"発注企業名"})
         */
        private $order_company_name;
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_flag",nullable=true, type="string", length=1, options={"comment":"直送指示フラグ"})
         */
        private $delivery_flag;
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
         * @ORM\Column(name="shipping_name",nullable=true, type="string", length=50, options={"comment":"出荷先名"})
         */
        private $shipping_name;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_address1",nullable=true, type="string", length=60, options={"comment":"出荷先住所１"})
         */
        private $shipping_address1;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_address2",nullable=true, type="string", length=60, options={"comment":"出荷先住所２"})
         */
        private $shipping_address2;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_post_code",nullable=true, type="string", length=8, options={"comment":"出荷先郵便番号"})
         */
        private $shipping_post_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_tel",nullable=true, type="string", length=13, options={"comment":"出荷先電話番号"})
         */
        private $shipping_tel;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_fax",nullable=true, type="string", length=13, options={"comment":"出荷先ＦＡＸ番号"})
         */
        private $shipping_fax;
        /**
         * @ORM\Column(name="export_type",type="integer",nullable=true, options={"comment":"出力区分" ,"default":0 })
         */
        private $export_type;
        /**
         * @ORM\Column(name="aprove_type",type="integer",nullable=true, options={"comment":"承認処理済区分" ,"default":0 })
         */
        private $aprove_type;
        /**
         * @ORM\Column(name="order_cancel",type="integer",nullable=true, options={"comment":"伝票キャンセル" ,"default":0 })
         */
        private $order_cancel;
        /**
         * @ORM\Column(name="delete_flag",type="integer",nullable=true, options={"comment":"削除フラグ" ,"default":0 })
         */
        private $delete_flag;
        /**
         * @ORM\Column(name="order_voucher_type",type="integer",nullable=true, options={"comment":"伝票種別区分" ,"default":0 })
         */
        private $order_voucher_type;
        /**
         * @var integer
         *
         * @ORM\Column(name="order_line_no", type="integer", options={"unsigned":true})
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
         * @ORM\Column(name="order_system_code",nullable=true, type="string", length=1, options={"comment":"システムコード"})
         */
        private $order_system_code;
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
         * @ORM\Column(name="product_maker_code",nullable=true, type="string", length=33, options={"comment":"JANコード又はメーカー型番"})
         */
        private $product_maker_code;
        /**
         * @var string
         *
         * @ORM\Column(name="product_name",nullable=true, type="string", length=40, options={"comment":"商品名"})
         */
        private $product_name;
        /**
         * @ORM\Column(name="order_num",type="integer",nullable=true, options={"comment":"発注数量"  })
         */
        private $order_num;
        /**
         * @ORM\Column(name="order_price",type="integer",nullable=true, options={"comment":"発注単価"  })
         */
        private $order_price;
        /**
         * @ORM\Column(name="order_amount",type="integer",nullable=true, options={"comment":"発注金額"  })
         */
        private $order_amount;
        /**
         * @var string
         *
         * @ORM\Column(name="tax_type",nullable=true, type="string", length=1, options={"comment":"消費税区分"})
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
         * @ORM\Column(name="cash_type_code",nullable=true, type="string", length=3, options={"comment":"レジ分類コード"})
         */
        private $cash_type_code;
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
         * @return mixed
         */
        public function getOrderType()
        {
            return $this->order_type;
        }

        /**
         * @param mixed $order_type
         */
        public function setOrderType($order_type)
        {
            $this->order_type = $order_type;
        }

        /**
         * @return mixed
         */
        public function getWebOrderType()
        {
            return $this->web_order_type;
        }

        /**
         * @param mixed $web_order_type
         */
        public function setWebOrderType($web_order_type)
        {
            $this->web_order_type = $web_order_type;
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
        public function getOrderStaffCode()
        {
            return $this->order_staff_code;
        }

        /**
         * @param $order_staff_code
         */
        public function setOrderStaffCode($order_staff_code)
        {
            $this->order_staff_code = $order_staff_code;
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
        public function getSalesStaffCode()
        {
            return $this->sales_staff_code;
        }

        /**
         * @param $sales_staff_code
         */
        public function setSalesStaffCode($sales_staff_code)
        {
            $this->sales_staff_code = $sales_staff_code;
        }

        /**
         * @return string
         */
        public function getOrderCompanyName()
        {
            return $this->order_company_name;
        }

        /**
         * @param $order_company_name
         */
        public function setOrderCompanyName($order_company_name)
        {
            $this->order_company_name = $order_company_name;
        }

        /**
         * @return string
         */
        public function getDeliveryFlag()
        {
            return $this->delivery_flag;
        }

        /**
         * @param $delivery_flag
         */
        public function setDeliveryFlag($delivery_flag)
        {
            $this->delivery_flag = $delivery_flag;
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
         * @return string
         */
        public function getShippingAddress1()
        {
            return $this->shipping_address1;
        }

        /**
         * @param $shipping_address1
         */
        public function setShippingAddress1($shipping_address1)
        {
            $this->shipping_address1 = $shipping_address1;
        }

        /**
         * @return string
         */
        public function getShippingAddress2()
        {
            return $this->shipping_address2;
        }

        /**
         * @param $shipping_address2
         */
        public function setShippingAddress2($shipping_address2)
        {
            $this->shipping_address2 = $shipping_address2;
        }

        /**
         * @return string
         */
        public function getShippingPostCode()
        {
            return $this->shipping_post_code;
        }

        /**
         * @param $shipping_post_code
         */
        public function setShippingPostCode($shipping_post_code)
        {
            $this->shipping_post_code = $shipping_post_code;
        }

        /**
         * @return string
         */
        public function getShippingTel()
        {
            return $this->shipping_tel;
        }

        /**
         * @param $shipping_tel
         */
        public function setShippingTel($shipping_tel)
        {
            $this->shipping_tel = $shipping_tel;
        }

        /**
         * @return string
         */
        public function getShippingFax()
        {
            return $this->shipping_fax;
        }

        /**
         * @param $shipping_fax
         */
        public function setShippingFax($shipping_fax)
        {
            $this->shipping_fax = $shipping_fax;
        }

        /**
         * @return mixed
         */
        public function getExportType()
        {
            return $this->export_type;
        }

        /**
         * @param mixed $export_type
         */
        public function setExportType($export_type)
        {
            $this->export_type = $export_type;
        }

        /**
         * @return mixed
         */
        public function getAproveType()
        {
            return $this->aprove_type;
        }

        /**
         * @param mixed $aprove_type
         */
        public function setAproveType($aprove_type)
        {
            $this->aprove_type = $aprove_type;
        }

        /**
         * @return mixed
         */
        public function getOrderCancel()
        {
            return $this->order_cancel;
        }

        /**
         * @param mixed $order_cancel
         */
        public function setOrderCancel($order_cancel)
        {
            $this->order_cancel = $order_cancel;
        }

        /**
         * @return mixed
         */
        public function getDeleteFlag()
        {
            return $this->delete_flag;
        }

        /**
         * @param mixed $delete_flag
         */
        public function setDeleteFlag($delete_flag)
        {
            $this->delete_flag = $delete_flag;
        }

        /**
         * @return mixed
         */
        public function getOrderVoucherType()
        {
            return $this->order_voucher_type;
        }

        /**
         * @param mixed $order_voucher_type
         */
        public function setOrderVoucherType($order_voucher_type)
        {
            $this->order_voucher_type = $order_voucher_type;
        }

        /**
         * @return integer
         */
        public function getOrderLineNo()
        {
            return $this->order_line_no;
        }

        /**
         * @param integer $order_line_no
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
        public function getOrderSystemCode()
        {
            return $this->order_system_code;
        }

        /**
         * @param $order_system_code
         */
        public function setOrderSystemCode($order_system_code)
        {
            $this->order_system_code = $order_system_code;
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
         * @return string
         */
        public function getTaxType()
        {
            return $this->tax_type;
        }

        /**
         * @param $tax_type
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
        public function getCashTypeCode()
        {
            return $this->cash_type_code;
        }

        /**
         * @param $cash_type_code
         */
        public function setCashTypeCode($cash_type_code)
        {
            $this->cash_type_code = $cash_type_code;
        }

        /**
         * @return integer
         */
        public function getOrderRegistedFlg()
        {
            return $this->order_registed_flg;
        }

        /**
         * @param $order_registed_flg
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
         * @return string
         */
        public function getCreateDate()
        {
            return (string) $this->create_date;
        }

        /**
         * @param  $create_date
         */
        public function setCreateDate($create_date)
        {
            $this->create_date = $create_date;
        }

        /**
         * @return string
         */
        public function getUpdateDate()
        {
            return (string) $this->update_date;
        }

        /**
         * @param  $update_date
         */
        public function setUpdateDate($update_date)
        {
            $this->update_date = $update_date;
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
         * @return \DateTime
         */
        public function getDeliveryDate()
        {
            return $this->delivery_date;
        }

        /**
         * @param $delivery_date
         */
        public function setDeliveryDate($delivery_date)
        {
            $this->delivery_date = $delivery_date;
        }

        /**
         * @return \DateTime
         */
        public function getOrderCreateDay()
        {
            return $this->order_create_day;
        }

        /**
         * @param $order_create_day
         */
        public function setOrderCreateDay($order_create_day)
        {
            $this->order_create_day = $order_create_day;
        }

        /**
         * @return \DateTime
         */
        public function getOrderUpdateDay()
        {
            return $this->order_update_day;
        }

        /**
         * @param $order_update_day
         */
        public function setOrderUpdateDay($order_update_day)
        {
            $this->order_update_day = $order_update_day;
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
    }
}
