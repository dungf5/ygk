<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtOrderNatEOSCopy', false)) {
    /**
     * DtOrderNatEOSCopy
     *
     * @ORM\Table(name="dt_order_nat_eos_copy")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtOrderNatEOSCopyRepository")
     */
    class DtOrderNatEOSCopy extends AbstractEntity
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
         * @ORM\Id
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
    }
}
