<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\StockList', false)) {
    /**
     * StockList
     *
     * @ORM\Table(name="stock_list")
     * @ORM\Entity(repositoryClass="Customize\Repository\StockListRepository")
     */
    class StockList extends AbstractEntity
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
         * @ORM\Column(name="customer_code", type="string", length=10,options={"comment":"顧客コード"}, nullable=false)
         * @ORM\Id
         */
        private $customer_code;
        /**
         * @ORM\Column(name="stock_location", type="string", length=10,options={"comment":"保管場所"}, nullable=false)
         * @ORM\Id
         */
        private $stock_location;
        /**
         * @ORM\Column(type="integer",nullable=false, options={"comment":"トータル在庫"})
         */
        private $stock_num;
        /**
         * @ORM\Column(type="integer",nullable=false, options={"comment":"引当在庫"})
         */
        private $reserve_num;
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
         * @return mixed
         */
        public function getStockNum()
        {
            return $this->stock_num;
        }

        /**
         * @param mixed $stock_num
         */
        public function setStockNum($stock_num): void
        {
            $this->stock_num = $stock_num;
        }

        /**
         * @return mixed
         */
        public function getStockLocation()
        {
            return $this->stock_location;
        }

        /**
         * @param mixed $stock_location
         */
        public function setStockLocation($stock_location): void
        {
            $this->stock_location = $stock_location;
        }

        /**
         * @return mixed
         */
        public function getReserveNum()
        {
            return $this->reserve_num;
        }

        /**
         * @param mixed $reserve_num
         */
        public function setReserveNum($reserve_num): void
        {
            $this->reserve_num = $reserve_num;
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
