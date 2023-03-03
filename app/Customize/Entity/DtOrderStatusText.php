<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtOrderStatusText', false)) {
    /**
     * DtOrderStatus
     *
     * @ORM\Table(name="dt_order_status")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderStatusRepository")
     */
    class DtOrderStatusText extends AbstractEntity
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
        public function getOrderLineNo(): string
        {
            return $this->order_line_no;
        }

        /**
         * @param string $order_line_no
         */
        public function setOrderLineNo(string $order_line_no): void
        {
            $this->order_line_no = $order_line_no;
        }

        /**
         * @return mixed
         */
        public function getOrderStatus()
        {
            return $this->order_status;
        }

        /**
         * @param mixed $order_status
         */
        public function setOrderStatus($order_status): void
        {
            $this->order_status = $order_status;
        }

        /**
         * @return string
         */
        public function getEcOrderNo(): string
        {
            return $this->ec_order_no;
        }

        /**
         * @param string $ec_order_no
         */
        public function setEcOrderNo(string $ec_order_no): void
        {
            $this->ec_order_no = $ec_order_no;
        }

        /**
         * @return string
         */
        public function getEcOrderLineno(): string
        {
            return $this->ec_order_lineno;
        }

        /**
         * @param string $ec_order_lineno
         */
        public function setEcOrderLineno(string $ec_order_lineno): void
        {
            $this->ec_order_lineno = $ec_order_lineno;
        }

        /**
         * @return mixed
         */
        public function getReserveStockNum()
        {
            return $this->reserve_stock_num;
        }

        /**
         * @param mixed $reserve_stock_num
         */
        public function setReserveStockNum($reserve_stock_num): void
        {
            $this->reserve_stock_num = $reserve_stock_num;
        }

        /**
         * @return mixed
         */
        public function getOrderRemainNum()
        {
            return $this->order_remain_num;
        }

        /**
         * @param mixed $order_remain_num
         */
        public function setOrderRemainNum($order_remain_num): void
        {
            $this->order_remain_num = $order_remain_num;
        }

        /**
         * @return string
         */
        public function getFlowType(): string
        {
            return $this->flow_type;
        }

        /**
         * @param string $flow_type
         */
        public function setFlowType(string $flow_type): void
        {
            $this->flow_type = $flow_type;
        }

        /**
         *
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         *
         */
        public function setCreateDate( $create_date): void
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
