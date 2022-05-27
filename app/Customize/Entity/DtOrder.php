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
         * @ORM\Column(name="customer_code",nullable=false, type="string", length=10, options={"comment":"顧客コード"})
         */
        private $customer_code;
/**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=false, type="string", length=10, options={"comment":"請求先コード"})
         */
        private $seikyu_code;
        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=15,options={"comment":"発注番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="order_lineno", type="string", length=15,options={"comment":"発注明細番号"}, nullable=false)
         * @ORM\Id
         */
        private $order_lineno;
/**
         * @var string
         *
         * @ORM\Column(name="syukka_code",nullable=false, type="string", length=10, options={"comment":"出荷先コード"})
         */
        private $syukka_code;
/**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=false, type="string", length=10, options={"comment":"届け先コード"})
         */
        private $otodoke_code;
     /**
         * @var \DateTime
         *
         * @ORM\Column(name="order_date", type="datetimetz",options={"comment":"受注日"})
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
         * @ORM\Column(name="item_no",nullable=true, type="string", length=12, options={"comment":"品目No"})
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
     * @ORM\Column(name="order_price",type="integer",nullable=true, options={"comment":"受注単価"  })
     */
    private $order_price;
  /**
     * @ORM\Column(name="unit_price_status",type="integer",nullable=true, options={"comment":"単価ステイタス"  })
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
     * @ORM\Column(name="request_flg",type="integer",nullable=false, options={"comment":"申請フラグ,固定（1:Y,0:No）" ,"default":1 })
     */
    private $request_flg;
    }
}
