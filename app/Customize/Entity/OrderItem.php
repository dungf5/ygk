<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\OrderItem', false)) {
    /**
     * OrderItem
     *
     * @ORM\Table(name="dtb_order_item")
     * @ORM\Entity(repositoryClass="Customize\Repository\OrderItemRepository")
     */
    class OrderItem extends AbstractEntity
    {
        /**
         * @var integer
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;
        /**
         * @ORM\Column(name="order_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $order_id;
        /**
         * @ORM\Column(name="product_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $product_id;
        /**
         * @ORM\Column(name="product_class_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $product_class_id;
        /**
         * @ORM\Column(name="shipping_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $shipping_id;
        /**
         * @ORM\Column(name="rounding_type_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $rounding_type_id;
        /**
         * @ORM\Column(name="tax_type_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $tax_type_id;
        /**
         * @ORM\Column(name="tax_display_type_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $tax_display_type_id;
        /**
         * @ORM\Column(name="order_item_type_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $order_item_type_id;
        /**
         * @var string
         *
         * @ORM\Column(name="product_name",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $product_name;
        /**
         * @var string
         *
         * @ORM\Column(name="product_code",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="class_name1",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $class_name1;
        /**
         * @var string
         *
         * @ORM\Column(name="class_name2",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $class_name2;
        /**
         * @var string
         *
         * @ORM\Column(name="class_category_name1",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $class_category_name1;
        /**
         * @var string
         *
         * @ORM\Column(name="class_category_name2",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $class_category_name2;
        /**
         * @ORM\Column(name="tax_rule_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $tax_rule_id;
        /**
         * @var string
         *
         * @ORM\Column(name="currency_code",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $currency_code;
        /**
         * @var string
         *
         * @ORM\Column(name="processor_name",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $processor_name;
        /**
         * @var string
         *
         * @ORM\Column(name="discriminator_type",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $discriminator_type;

        /**
         * @return int
         */
        public function getId(): int
        {
            return $this->id;
        }

        /**
         * @param int $id
         */
        public function setId(int $id): void
        {
            $this->id = $id;
        }

        /**
         * @return mixed
         */
        public function getOrderId()
        {
            return $this->order_id;
        }

        /**
         * @param mixed $order_id
         */
        public function setOrderId($order_id): void
        {
            $this->order_id = $order_id;
        }

        /**
         * @return mixed
         */
        public function getProductId()
        {
            return $this->product_id;
        }

        /**
         * @param mixed $product_id
         */
        public function setProductId($product_id): void
        {
            $this->product_id = $product_id;
        }

        /**
         * @return mixed
         */
        public function getProductClassId()
        {
            return $this->product_class_id;
        }

        /**
         * @param mixed $product_class_id
         */
        public function setProductClassId($product_class_id): void
        {
            $this->product_class_id = $product_class_id;
        }

        /**
         * @return mixed
         */
        public function getShippingId()
        {
            return $this->shipping_id;
        }

        /**
         * @param mixed $shipping_id
         */
        public function setShippingId($shipping_id): void
        {
            $this->shipping_id = $shipping_id;
        }

        /**
         * @return mixed
         */
        public function getRoundingTypeId()
        {
            return $this->rounding_type_id;
        }

        /**
         * @param mixed $rounding_type_id
         */
        public function setRoundingTypeId($rounding_type_id): void
        {
            $this->rounding_type_id = $rounding_type_id;
        }

        /**
         * @return mixed
         */
        public function getTaxTypeId()
        {
            return $this->tax_type_id;
        }

        /**
         * @param mixed $tax_type_id
         */
        public function setTaxTypeId($tax_type_id): void
        {
            $this->tax_type_id = $tax_type_id;
        }

        /**
         * @return mixed
         */
        public function getTaxDisplayTypeId()
        {
            return $this->tax_display_type_id;
        }

        /**
         * @param mixed $tax_display_type_id
         */
        public function setTaxDisplayTypeId($tax_display_type_id): void
        {
            $this->tax_display_type_id = $tax_display_type_id;
        }

        /**
         * @return mixed
         */
        public function getOrderItemTypeId()
        {
            return $this->order_item_type_id;
        }

        /**
         * @param mixed $order_item_type_id
         */
        public function setOrderItemTypeId($order_item_type_id): void
        {
            $this->order_item_type_id = $order_item_type_id;
        }

        /**
         * @return string
         */
        public function getProductName(): string
        {
            return $this->product_name;
        }

        /**
         * @param string $product_name
         */
        public function setProductName(string $product_name): void
        {
            $this->product_name = $product_name;
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
        public function getClassName1(): string
        {
            return $this->class_name1;
        }

        /**
         * @param string $class_name1
         */
        public function setClassName1(string $class_name1): void
        {
            $this->class_name1 = $class_name1;
        }

        /**
         * @return string
         */
        public function getClassName2(): string
        {
            return $this->class_name2;
        }

        /**
         * @param string $class_name2
         */
        public function setClassName2(string $class_name2): void
        {
            $this->class_name2 = $class_name2;
        }

        /**
         * @return string
         */
        public function getClassCategoryName1(): string
        {
            return $this->class_category_name1;
        }

        /**
         * @param string $class_category_name1
         */
        public function setClassCategoryName1(string $class_category_name1): void
        {
            $this->class_category_name1 = $class_category_name1;
        }

        /**
         * @return string
         */
        public function getClassCategoryName2(): string
        {
            return $this->class_category_name2;
        }

        /**
         * @param string $class_category_name2
         */
        public function setClassCategoryName2(string $class_category_name2): void
        {
            $this->class_category_name2 = $class_category_name2;
        }

        /**
         * @return mixed
         */
        public function getTaxRuleId()
        {
            return $this->tax_rule_id;
        }

        /**
         * @param mixed $tax_rule_id
         */
        public function setTaxRuleId($tax_rule_id): void
        {
            $this->tax_rule_id = $tax_rule_id;
        }

        /**
         * @return string
         */
        public function getCurrencyCode(): string
        {
            return $this->currency_code;
        }

        /**
         * @param string $currency_code
         */
        public function setCurrencyCode(string $currency_code): void
        {
            $this->currency_code = $currency_code;
        }

        /**
         * @return string
         */
        public function getProcessorName(): string
        {
            return $this->processor_name;
        }

        /**
         * @param string $processor_name
         */
        public function setProcessorName(string $processor_name): void
        {
            $this->processor_name = $processor_name;
        }

        /**
         * @return string
         */
        public function getDiscriminatorType(): string
        {
            return $this->discriminator_type;
        }

        /**
         * @param string $discriminator_type
         */
        public function setDiscriminatorType(string $discriminator_type): void
        {
            $this->discriminator_type = $discriminator_type;
        }
    }
}
