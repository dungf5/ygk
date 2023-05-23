<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtOrderDaitoTest', false)) {
    /**
     * DtOrderDaitoTest
     *
     * @ORM\Table(name="dt_order_daito_test")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderDaitoTestRepository")
     */
    class DtOrderDaitoTest extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code",nullable=false, type="string", length=25, options={"comment":"顧客コード"})
         */
        private $customer_code = '';
        /**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=false, type="string", length=25, options={"comment":"請求先コード"})
         */
        private $seikyu_code = '';
        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=25,options={"comment":"発注番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_no = '';
        /**
         * @var string
         *
         * @ORM\Column(name="order_lineno", type="string", length=25,options={"comment":"発注明細番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_lineno = 0;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=false, type="string", length=25, options={"comment":"出荷先コード"})
         */
        private $shipping_code = '';
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=false, type="string", length=25, options={"comment":"届け先コード"})
         */
        private $otodoke_code = '';
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="order_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT '受注日'")
         */
        private $order_date = null;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_plan_date",nullable=true, type="string", length=12, options={"comment":"希望納期（納入予定日）"})
         */
        private $deli_plan_date = '';
        /**
         * @var string
         *
         * @ORM\Column(name="shiping_plan_date",nullable=true, type="string", length=12, options={"comment":"出荷予定日"})
         */
        private $shiping_plan_date = '';
        /**
         * @var string
         *
         * @ORM\Column(name="item_no",nullable=true, type="string", length=45, options={"comment":"品目No"})
         */
        private $item_no = '';
        /**
         * @ORM\Column(name="demand_quantity",type="integer",nullable=true, options={"comment":"需要数(需要単位ベース)"  })
         */
        private $demand_quantity = 0;
        /**
         * @var string
         *
         * @ORM\Column(name="demand_unit",nullable=true, type="string", length=50, options={"comment":"需要単位"})
         */
        private $demand_unit = '';
        /**
         * @ORM\Column(name="order_price",type="float",nullable=true, options={"comment":"受注単価"  })
         */
        private $order_price = 0;
        /**
         * @ORM\Column(name="unit_price_status",type="string",nullable=true, options={"comment":"単価ステイタス"  })
         */
        private $unit_price_status = '';
        /**
         * @var string
         *
         * @ORM\Column(name="deploy",nullable=true, type="string", length=50, options={"comment":"営業部門"})
         */
        private $deploy = '';
        /**
         * @var string
         *
         * @ORM\Column(name="company_id",nullable=true, type="string", length=50, options={"comment":"会社ID"})
         */
        private $company_id = '';
        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=50, options={"comment":"製品コード"})
         */
        private $product_code = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg1",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント01"})
         */
        private $dyna_model_seg1 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg2",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント02"})
         */
        private $dyna_model_seg2 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg3",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント03"})
         */
        private $dyna_model_seg3 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg4",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント04"})
         */
        private $dyna_model_seg4 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg5",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント05"})
         */
        private $dyna_model_seg5 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg6",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント06"})
         */
        private $dyna_model_seg6 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg7",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント07"})
         */
        private $dyna_model_seg7 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg8",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント08"})
         */
        private $dyna_model_seg8 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg9",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント09"})
         */
        private $dyna_model_seg9 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg10",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント10"})
         */
        private $dyna_model_seg10 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg11",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント11"})
         */
        private $dyna_model_seg11 = '';
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg12",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント12"})
         */
        private $dyna_model_seg12 = '';
        /**
         * @ORM\Column(name="request_flg",type="string",nullable=false, options={"comment":"申請フラグ,固定（1:Y,0:No）" ,"default":1 })
         */
        private $request_flg = '';
        /**
         * @var string
         *
         * @ORM\Column(name="shiping_deposit_code",type="string",nullable=false, options={"comment":"出荷在庫場所", "default":"XB0101001" })
         */
        private $shiping_deposit_code = '';
        /**
         * @var string
         *
         * @ORM\Column(name="fvehicleno", type="string", nullable=true, options={"comment":"便No. 0:送料なし、１：送料あり"})
         */
        private $fvehicleno = '';
        /**
         * @var string
         *
         * @ORM\Column(name="ftrnsportcd", type="string", nullable=true, options={"comment":"輸送便コード"})
         */
        private $ftrnsportcd = '';
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ登録日時'")
         */
        private $create_date = null;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT 'データ更新日時'")
         */
        private $update_date = null;

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
        public function getSeikyuCode()
        {
            return $this->seikyu_code;
        }

        /**
         * @param $seikyu_code
         */
        public function setSeikyuCode($seikyu_code)
        {
            $this->seikyu_code = $seikyu_code;
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
         * @return \DateTime
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
        public function getDeliPlanDate()
        {
            return $this->deli_plan_date;
        }

        /**
         * @param $deli_plan_date
         */
        public function setDeliPlanDate($deli_plan_date)
        {
            $this->deli_plan_date = $deli_plan_date;
        }

        /**
         * @return string
         */
        public function getShipingPlanDate()
        {
            return $this->shiping_plan_date;
        }

        /**
         * @param $shiping_plan_date
         */
        public function setShipingPlanDate($shiping_plan_date)
        {
            $this->shiping_plan_date = $shiping_plan_date;
        }

        /**
         * @return string
         */
        public function getItemNo()
        {
            return $this->item_no;
        }

        /**
         * @param $item_no
         */
        public function setItemNo($item_no)
        {
            $this->item_no = $item_no;
        }

        /**
         * @return mixed
         */
        public function getDemandQuantity()
        {
            return $this->demand_quantity;
        }

        /**
         * @param mixed $demand_quantity
         */
        public function setDemandQuantity($demand_quantity)
        {
            $this->demand_quantity = $demand_quantity;
        }

        /**
         * @return string
         */
        public function getDemandUnit()
        {
            return $this->demand_unit;
        }

        /**
         * @param $demand_unit
         */
        public function setDemandUnit($demand_unit)
        {
            $this->demand_unit = $demand_unit;
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
         * @return string
         */
        public function getUnitPriceStatus()
        {
            return $this->unit_price_status;
        }

        /**
         * @param $unit_price_status
         */
        public function setUnitPriceStatus($unit_price_status)
        {
            $this->unit_price_status = $unit_price_status;
        }

        /**
         * @return string
         */
        public function getDeploy()
        {
            return $this->deploy;
        }

        /**
         * @param $deploy
         */
        public function setDeploy($deploy)
        {
            $this->deploy = $deploy;
        }

        /**
         * @return string
         */
        public function getCompanyId()
        {
            return $this->company_id;
        }

        /**
         * @param $company_id
         */
        public function setCompanyId($company_id)
        {
            $this->company_id = $company_id;
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
        public function getDynaModelSeg1()
        {
            return $this->dyna_model_seg1;
        }

        /**
         * @param $dyna_model_seg1
         */
        public function setDynaModelSeg1($dyna_model_seg1)
        {
            $this->dyna_model_seg1 = $dyna_model_seg1;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg2()
        {
            return $this->dyna_model_seg2;
        }

        /**
         * @param $dyna_model_seg2
         */
        public function setDynaModelSeg2($dyna_model_seg2)
        {
            $this->dyna_model_seg2 = $dyna_model_seg2;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg3()
        {
            return $this->dyna_model_seg3;
        }

        /**
         * @param $dyna_model_seg3
         */
        public function setDynaModelSeg3($dyna_model_seg3)
        {
            $this->dyna_model_seg3 = $dyna_model_seg3;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg4()
        {
            return $this->dyna_model_seg4;
        }

        /**
         * @param $dyna_model_seg4
         */
        public function setDynaModelSeg4($dyna_model_seg4)
        {
            $this->dyna_model_seg4 = $dyna_model_seg4;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg5()
        {
            return $this->dyna_model_seg5;
        }

        /**
         * @param $dyna_model_seg5
         */
        public function setDynaModelSeg5($dyna_model_seg5)
        {
            $this->dyna_model_seg5 = $dyna_model_seg5;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg6()
        {
            return $this->dyna_model_seg6;
        }

        /**
         * @param string|null $dyna_model_seg6
         */
        public function setDynaModelSeg6($dyna_model_seg6 = null)
        {
            $this->dyna_model_seg6 = $dyna_model_seg6;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg7()
        {
            return $this->dyna_model_seg7;
        }

        /**
         * @param string|null $dyna_model_seg7
         */
        public function setDynaModelSeg7($dyna_model_seg7 = null)
        {
            $this->dyna_model_seg7 = $dyna_model_seg7;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg8()
        {
            return $this->dyna_model_seg8;
        }

        /**
         * @param string|null $dyna_model_seg8
         */
        public function setDynaModelSeg8($dyna_model_seg8 = null)
        {
            $this->dyna_model_seg8 = $dyna_model_seg8;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg9()
        {
            return $this->dyna_model_seg9;
        }

        /**
         * @param string|null $dyna_model_seg9
         */
        public function setDynaModelSeg9($dyna_model_seg9 = null)
        {
            $this->dyna_model_seg9 = $dyna_model_seg9;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg10()
        {
            return $this->dyna_model_seg10;
        }

        /**
         * @param $dyna_model_seg10
         */
        public function setDynaModelSeg10($dyna_model_seg10)
        {
            $this->dyna_model_seg10 = $dyna_model_seg10;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg11()
        {
            return $this->dyna_model_seg11;
        }

        /**
         * @param $dyna_model_seg11
         */
        public function setDynaModelSeg11($dyna_model_seg11)
        {
            $this->dyna_model_seg11 = $dyna_model_seg11;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg12()
        {
            return $this->dyna_model_seg12;
        }

        /**
         * @param $dyna_model_seg12
         */
        public function setDynaModelSeg12($dyna_model_seg12)
        {
            $this->dyna_model_seg12 = $dyna_model_seg12;
        }

        /**
         * @return string
         */
        public function getRequestFlg()
        {
            return $this->request_flg;
        }

        /**
         * @param $request_flg
         */
        public function setRequestFlg($request_flg)
        {
            $this->request_flg = $request_flg;
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
        public function getShipingDepositCode()
        {
            return $this->shiping_deposit_code;
        }

        /**
         * @param $shiping_deposit_code
         */
        public function setShipingDepositCode($shiping_deposit_code)
        {
            $this->shiping_deposit_code = $shiping_deposit_code;
        }

        /**
         * @return string
         */
        public function getFvehicleno()
        {
            return $this->fvehicleno;
        }

        /**
         * @param $fvehicleno
         */
        public function setFvehicleno($fvehicleno)
        {
            $this->fvehicleno = $fvehicleno;
        }

        /**
         * @return string
         */
        public function getFtrnsportcd()
        {
            return $this->ftrnsportcd;
        }

        /**
         * @param $ftrnsportcd
         */
        public function setFtrnsportcd($ftrnsportcd)
        {
            $this->ftrnsportcd = $ftrnsportcd;
        }
    }
}
