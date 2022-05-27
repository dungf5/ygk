<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtOrderStatus', false)) {
    /**
     * DtOrderStatus
     *
     * @ORM\Table(name="dt_order_status")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderStatusRepository")
     */
    class DtOrderStatus extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=15, options={"comment":"STRA注文番号"})
         */
        private $order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="order_line_no",nullable=true, type="string", length=15, options={"comment":"STRA注文明細番号"})
         */
        private $order_line_no;
        /**
         * @ORM\Column(name="order_status",type="integer",nullable=false, options={"comment":"受注ステータス
ステータス種類
        1:未引当、2:一部引当、3:引当済、4:キャンセル、9:クロース(出荷済)" ,"default":1 })
         */
        private $order_status;
        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_no", type="string", length=15,options={"comment":"EC発注番号"}, nullable=false)
         * @ORM\Id
         */
        private $ec_order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_lineno", type="string", length=15,options={"comment":"EC発注明細番号"}, nullable=false)
         * @ORM\Id
         */
        private $ec_order_lineno;
        /**
         * @ORM\Column(name="reserve_stock_num",type="integer",nullable=true, options={"comment":"引当在庫数"  })
         */
        private $reserve_stock_num;
        /**
         * @ORM\Column(name="order_remain_num",type="integer",nullable=true, options={"comment":"受注残"  })
         */
        private $order_remain_num;
        /**
         * @var string
         *
         * @ORM\Column(name="flow_type",nullable=true, type="string", length=10, options={"comment":"商流区分(ダイナ規格セグメント03)"})
         */
        private $flow_type;
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
