<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\{MstDelivery}', false)) {
    /**
     * MstDelivery
     *
     * @ORM\Table(name="mst_delivery555")
     * @ORM\Entity(repositoryClass="Customize\Repository\{MstDelivery}Repository")
     */
    class MstDelivery extends AbstractEntity
    { 
        /**
         * @var string
         *
         * @ORM\Column(name="delivery_no", type="string", length=10,options={"comment":"納品書№"}, nullable=false)
         * @ORM\Id
         */
        protected $id;
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
     * @ORM\Column(type="integer",nullable=true, options={"comment":"消費税"})
     */
    protected $tax;
/**
         * @var string
         *
         * @ORM\Column(name="lot_no",nullable=true, type="string", length=8, options={"comment":"ロット№"})
         */
        private $lot_no;
/**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=8, options={"comment":"御社注文№"})
         */
        private $order_no;
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
         * @ORM\Column(name="shiping_code",nullable=true, type="string", length=10, options={"comment":"出荷先"})
         */
        private $shiping_code;
/**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=true, type="string", length=10, options={"comment":"届け先"})
         */
        private $otodoke_code;
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
    }
}
