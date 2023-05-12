<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtOrder', false)) {
    /**
     * DtOrder
     *
     * @ORM\Table(name="dt_order")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderRepository")
     */
    class DtOrder extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code",nullable=false, type="string", length=25, options={"comment":"顧客コード"})
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=false, type="string", length=25, options={"comment":"請求先コード"})
         */
        private $seikyu_code;
        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=25,options={"comment":"発注番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="order_lineno", type="string", length=25,options={"comment":"発注明細番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_lineno;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=false, type="string", length=25, options={"comment":"出荷先コード"})
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=false, type="string", length=25, options={"comment":"届け先コード"})
         */
        private $otodoke_code;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="order_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP(3) COMMENT '受注日'")
         */
        private $order_date;
        /**
         * @var string
         *
         * @ORM\Column(name="deli_plan_date",nullable=true, type="string", length=12, options={"comment":"希望納期（納入予定日）"})
         */
        private $deli_plan_date;
        /**
         * @var string
         *
         * @ORM\Column(name="shiping_plan_date",nullable=true, type="string", length=12, options={"comment":"出荷予定日"})
         */
        private $shiping_plan_date;
        /**
         * @var string
         *
         * @ORM\Column(name="item_no",nullable=true, type="string", length=45, options={"comment":"品目No"})
         */
        private $item_no;
        /**
         * @ORM\Column(name="demand_quantity",type="integer",nullable=true, options={"comment":"需要数(需要単位ベース)"  })
         */
        private $demand_quantity;
        /**
         * @var string
         *
         * @ORM\Column(name="demand_unit",nullable=true, type="string", length=50, options={"comment":"需要単位"})
         */
        private $demand_unit;
        /**
         * @ORM\Column(name="order_price",type="float",nullable=true, options={"comment":"受注単価"  })
         */
        private $order_price;
        /**
         * @ORM\Column(name="unit_price_status",type="string",nullable=true, options={"comment":"単価ステイタス"  })
         */
        private $unit_price_status;
        /**
         * @var string
         *
         * @ORM\Column(name="deploy",nullable=true, type="string", length=50, options={"comment":"営業部門"})
         */
        private $deploy;
        /**
         * @var string
         *
         * @ORM\Column(name="company_id",nullable=true, type="string", length=50, options={"comment":"会社ID"})
         */
        private $company_id;
        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=50, options={"comment":"製品コード"})
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg1",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント01"})
         */
        private $dyna_model_seg1;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg2",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント02"})
         */
        private $dyna_model_seg2;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg3",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント03"})
         */
        private $dyna_model_seg3;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg4",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント04"})
         */
        private $dyna_model_seg4;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg5",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント05"})
         */
        private $dyna_model_seg5;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg6",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント06"})
         */
        private $dyna_model_seg6;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg7",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント07"})
         */
        private $dyna_model_seg7;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg8",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント08"})
         */
        private $dyna_model_seg8;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg9",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント09"})
         */
        private $dyna_model_seg9;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg10",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント10"})
         */
        private $dyna_model_seg10;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg11",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント11"})
         */
        private $dyna_model_seg11;
        /**
         * @var string
         *
         * @ORM\Column(name="dyna_model_seg12",nullable=true, type="string", length=50, options={"comment":"ダイナ規格セグメント12"})
         */
        private $dyna_model_seg12;
        /**
         * @ORM\Column(name="request_flg",type="string",nullable=false, options={"comment":"申請フラグ,固定（1:Y,0:No）" ,"default":1 })
         */
        private $request_flg;

        /**
         * @var string
         *
         * @ORM\Column(name="shiping_deposit_code",type="string",nullable=false, options={"comment":"出荷在庫場所", "default":"XB0101001" })
         */
        private $shiping_deposit_code;

        /**
         * @var string
         * @ORM\Column(name="fvehicleno",type="string",nullable=true, options={"comment":"便No. 0:送料なし、１：送料あり"  })
         */
        private $fvehicleno;
        /**
         * @var string
         *
         * @ORM\Column(name="ftrnsportcd", type="string", nullable=true, options={"comment":"輸送便コード"})
         */
        private $ftrnsportcd;

        /**
         * @return string
         */
        public function getCustomerCode(): string
        {
            return $this->customer_code;
        }

        /**
         * @param string $customer_code
         */
        public function setCustomerCode(string $customer_code): void
        {
            $this->customer_code = $customer_code;
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
        public function getOrderLineno(): string
        {
            return $this->order_lineno;
        }

        /**
         * @param string $order_lineno
         */
        public function setOrderLineno(string $order_lineno): void
        {
            $this->order_lineno = $order_lineno;
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
         * @return \DateTime
         */
        public function getOrderDate(): \DateTime
        {
            return $this->order_date;
        }

        /**
         * @param \DateTime $order_date
         */
        public function setOrderDate(\DateTime $order_date): void
        {
            $this->order_date = $order_date;
        }

        /**
         * @return string
         */
        public function getDeliPlanDate(): string
        {
            return $this->deli_plan_date;
        }

        /**
         * @param string $deli_plan_date
         */
        public function setDeliPlanDate(string $deli_plan_date): void
        {
            $this->deli_plan_date = $deli_plan_date;
        }

        /**
         * @return string
         */
        public function getShipingPlanDate(): string
        {
            return $this->shiping_plan_date;
        }

        /**
         * @param string $shiping_plan_date
         */
        public function setShipingPlanDate(string $shiping_plan_date): void
        {
            $this->shiping_plan_date = $shiping_plan_date;
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
         * @return mixed
         */
        public function getDemandQuantity()
        {
            return $this->demand_quantity;
        }

        /**
         * @param mixed $demand_quantity
         */
        public function setDemandQuantity($demand_quantity): void
        {
            $this->demand_quantity = $demand_quantity;
        }

        /**
         * @return string
         */
        public function getDemandUnit(): string
        {
            return $this->demand_unit;
        }

        /**
         * @param string $demand_unit
         */
        public function setDemandUnit(string $demand_unit): void
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
        public function setOrderPrice($order_price): void
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
         * @param string $unit_price_status
         */
        public function setUnitPriceStatus($unit_price_status): void
        {
            $this->unit_price_status = $unit_price_status;
        }

        /**
         * @return string
         */
        public function getDeploy(): string
        {
            return $this->deploy;
        }

        /**
         * @param string $deploy
         */
        public function setDeploy(string $deploy): void
        {
            $this->deploy = $deploy;
        }

        /**
         * @return string
         */
        public function getCompanyId(): string
        {
            return $this->company_id;
        }

        /**
         * @param string $company_id
         */
        public function setCompanyId(string $company_id): void
        {
            $this->company_id = $company_id;
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
        public function getDynaModelSeg1(): string
        {
            return $this->dyna_model_seg1;
        }

        /**
         * @param string $dyna_model_seg1
         */
        public function setDynaModelSeg1(string $dyna_model_seg1): void
        {
            $this->dyna_model_seg1 = $dyna_model_seg1;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg2(): string
        {
            return $this->dyna_model_seg2;
        }

        /**
         * @param string $dyna_model_seg2
         */
        public function setDynaModelSeg2(string $dyna_model_seg2): void
        {
            $this->dyna_model_seg2 = $dyna_model_seg2;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg3(): string
        {
            return $this->dyna_model_seg3;
        }

        /**
         * @param string $dyna_model_seg3
         */
        public function setDynaModelSeg3(string $dyna_model_seg3): void
        {
            $this->dyna_model_seg3 = $dyna_model_seg3;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg4(): string
        {
            return $this->dyna_model_seg4;
        }

        /**
         * @param string $dyna_model_seg4
         */
        public function setDynaModelSeg4(string $dyna_model_seg4): void
        {
            $this->dyna_model_seg4 = $dyna_model_seg4;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg5(): string
        {
            return $this->dyna_model_seg5;
        }

        /**
         * @param string $dyna_model_seg5
         */
        public function setDynaModelSeg5(string $dyna_model_seg5): void
        {
            $this->dyna_model_seg5 = $dyna_model_seg5;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg6(): string
        {
            return $this->dyna_model_seg6;
        }

        /**
         * @param string|null $dyna_model_seg6
         */
        public function setDynaModelSeg6($dyna_model_seg6 = null): void
        {
            $this->dyna_model_seg6 = $dyna_model_seg6;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg7(): string
        {
            return $this->dyna_model_seg7;
        }

        /**
         * @param string|null $dyna_model_seg7
         */
        public function setDynaModelSeg7($dyna_model_seg7 = null): void
        {
            $this->dyna_model_seg7 = $dyna_model_seg7;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg8(): string
        {
            return $this->dyna_model_seg8;
        }

        /**
         * @param string|null $dyna_model_seg8
         */
        public function setDynaModelSeg8($dyna_model_seg8 = null): void
        {
            $this->dyna_model_seg8 = $dyna_model_seg8;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg9(): string
        {
            return $this->dyna_model_seg9;
        }

        /**
         * @param string|null $dyna_model_seg9
         */
        public function setDynaModelSeg9($dyna_model_seg9 = null): void
        {
            $this->dyna_model_seg9 = $dyna_model_seg9;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg10(): string
        {
            return $this->dyna_model_seg10;
        }

        /**
         * @param string $dyna_model_seg10
         */
        public function setDynaModelSeg10(string $dyna_model_seg10): void
        {
            $this->dyna_model_seg10 = $dyna_model_seg10;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg11(): string
        {
            return $this->dyna_model_seg11;
        }

        /**
         * @param string $dyna_model_seg11
         */
        public function setDynaModelSeg11(string $dyna_model_seg11): void
        {
            $this->dyna_model_seg11 = $dyna_model_seg11;
        }

        /**
         * @return string
         */
        public function getDynaModelSeg12(): string
        {
            return $this->dyna_model_seg12;
        }

        /**
         * @param string $dyna_model_seg12
         */
        public function setDynaModelSeg12(string $dyna_model_seg12): void
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
         * @param string $request_flg
         */
        public function setRequestFlg($request_flg): void
        {
            $this->request_flg = $request_flg;
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
         * @return string
         */
        public function getShipingDepositCode()
        {
            return $this->shiping_deposit_code;
        }

        /**
         * @param string $shiping_deposit_code
         */
        public function setShipingDepositCode($shiping_deposit_code): void
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
         * @param string $fvehicleno
         */
        public function setFvehicleno($fvehicleno): void
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
