<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Customize\Entity\Price', false)) {

    /**
     * Price
     *
     *
     * @ORM\Table(name="dt_price")
     * @ORM\Entity(repositoryClass="Customize\Repository\PriceRepository")
     */
    class Price extends \Eccube\Entity\AbstractEntity{

        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=10, nullable=false, options={"comment":"製品コード"})
         * @ORM\Id
         */
        private $product_code;

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=10, nullable=false, options={"comment":"顧客コード"})
         * @ORM\Id
         */
        private $customer_code;

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no", type="string", length=15, nullable=false, options={"comment":"出荷先コード"})
         * @ORM\Id
         */
        private $shipping_no;

        /**
         * @var int
         *
         * @ORM\Column(name="min_order_num", type="integer", options={"comment":"最小オーダー数"})
         * @ORM\Id
         */
        private $min_order_num;

        /**
         * @var float
         *
         * @ORM\Column(name="price_s01", type="float", columnDefinition="FLOAT COMMENT '価格'")
         *
         */
        private $price_s01;

        /**
         * @var string
         *
         * @ORM\Column(name="valid_date", type="string", length=10, nullable=false, options={"comment":"有効日"})
         * @ORM\Id
         */
        private $valid_date;

        /**
         * @var string
         *
         * @ORM\Column(name="expire_date", type="string", length=10, nullable=false, options={"comment":"失効日"})
         * @ORM\Id
         */
        private $expire_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", nullable=false, columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ登録日時'")
         *
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", nullable=false, columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ更新日時'")
         *
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
         * @return string
         */
        public function getShippingNo(): string
        {
            return $this->shipping_no;
        }

        /**
         * @param string $shipping_no
         */
        public function setShippingNo(string $shipping_no): void
        {
            $this->shipping_no = $shipping_no;
        }

        /**
         * @return int
         */
        public function getMinOrderNum(): int
        {
            return $this->min_order_num;
        }

        /**
         * @param int $min_order_num
         */
        public function setMinOrderNum(int $min_order_num): void
        {
            $this->min_order_num = $min_order_num;
        }

        /**
         * @return float
         */
        public function getPriceS01(): float
        {
            return $this->price_s01;
        }

        /**
         * @param float $price_s01
         */
        public function setPriceS01(float $price_s01): void
        {
            $this->price_s01 = $price_s01;
        }

        /**
         * @return string
         */
        public function getValidDate(): string
        {
            return $this->valid_date;
        }

        /**
         * @param string $valid_date
         */
        public function setValidDate(string $valid_date): void
        {
            $this->valid_date = $valid_date;
        }

        /**
         * @return string
         */
        public function getExpireDate(): string
        {
            return $this->expire_date;
        }

        /**
         * @param string $expire_date
         */
        public function setExpireDate(string $expire_date): void
        {
            $this->expire_date = $expire_date;
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
