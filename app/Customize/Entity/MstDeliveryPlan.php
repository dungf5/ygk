<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\MstDeliveryPlan', false)) {
    /**
     * MstDeliveryPlan
     *
     * @ORM\Table(name="mst_delivery_plan")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstDeliveryPlanRepository")
     */
    class MstDeliveryPlan extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=10,options={"comment":"製品コード"}, nullable=false)
         * @ORM\Id
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_location", type="string", length=10,options={"comment":"在庫場所"}, nullable=false)
         * @ORM\Id
         */
        private $stock_location;
        /**
         * @ORM\Column(name="delivery_date", type="string",nullable=false, options={"comment":"納入予定日"})
         * @ORM\Id
         */
        private $delivery_date;
        /**
         * @ORM\Column(name="quanlity", type="integer",options={"comment":"数量"})
         */
        private $quanlity;

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
        public function getStockLocation(): string
        {
            return $this->stock_location;
        }

        /**
         * @param string $stock_location
         */
        public function setStockLocation(string $stock_location): void
        {
            $this->stock_location = $stock_location;
        }

        /**
         * @return mixed
         */
        public function getDeliveryDate()
        {
            return $this->delivery_date;
        }

        /**
         * @param string $delivery_date
         */
        public function setDeliveryDate(string $delivery_date): void
        {
            $this->delivery_date = $delivery_date;
        }

        /**
         * @return mixed
         */
        public function getQuanlity()
        {
            return $this->quanlity;
        }

        /**
         * @param mixed $quanlity
         */
        public function setQuanlity($quanlity): void
        {
            $this->quanlity = $quanlity;
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
    }
}
