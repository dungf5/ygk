<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;

if (!class_exists('\Customize\Entity\MstProduct', false)) {
    /**
     * MstProduct
     *
     *
     * @ORM\Table(name="mst_product")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstProductRepository")
     */
    class MstProduct extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=20, nullable=false)
         * @ORM\Id
         */
        private $product_code;

        /**
         * @var string
         *
         * @ORM\Column(name="product_name", type="string", length=125)
         */
        private $product_name;

        /**
         * @var string
         *
         * @ORM\Column(name="product_name_abb", type="string", length=50)
         */
        private $product_name_abb;

        /**
         * @var string
         *
         * @ORM\Column(name="jan_code", type="string", length=13)
         */
        private $jan_code;

        /**
         * @var int
         *
         * @ORM\Column(name="unit_price", type="integer")
         */
        private $unit_price;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_code1", type="string", length=10)
         */
        private $tag_code1;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_name1", type="string", length=30)
         */
        private $tag_name1;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_code2", type="string", length=10)
         */
        private $tag_code2;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_name2", type="string", length=30)
         */
        private $tag_name2;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_code3", type="string", length=10)
         */
        private $tag_code3;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_name3", type="string", length=30)
         */
        private $tag_name3;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_code4", type="string", length=10)
         */
        private $tag_code4;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_name4", type="string", length=30)
         */
        private $tag_name4;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_code5", type="string", length=10)
         */
        private $tag_code5;

        /**
         * @var string
         *
         * @ORM\Column(name="tag_name5", type="string", length=30)
         */
        private $tag_name5;

        /**
         * @var string
         *
         * @ORM\Column(name="category_code1", type="string", length=10)
         */
        private $category_code1;

        /**
         * @var string
         *
         * @ORM\Column(name="category_name1", type="string", length=50)
         */
        private $category_name1;

        /**
         * @var string
         *
         * @ORM\Column(name="category_code2", type="string", length=10)
         */
        private $category_code2;

        /**
         * @var string
         *
         * @ORM\Column(name="category_name2", type="string", length=50)
         */
        private $category_name2;

        /**
         * @var string
         *
         * @ORM\Column(name="category_code3", type="string", length=10)
         */
        private $category_code3;

        /**
         * @var string
         *
         * @ORM\Column(name="category_name3", type="string", length=50)
         */
        private $category_name3;

        /**
         * @var string
         *
         * @ORM\Column(name="series_name", type="string", length=50)
         */
        private $series_name;

        /**
         * @var string
         *
         * @ORM\Column(name="line_no", type="string", length=10)
         */
        private $line_no;

        /**
         * @var int
         *
         * @ORM\Column(name="quantity", type="integer")
         */
        private $quantity;

        /**
         * @var string
         *
         * @ORM\Column(name="size", type="string", length=30)
         */
        private $size;

        /**
         * @var string
         *
         * @ORM\Column(name="color", type="string", length=30)
         */
        private $color;

        /**
         * @var string
         *
         * @ORM\Column(name="material", type="string", length=255)
         */
        private $material;

        /**
         * @var string
         *
         * @ORM\Column(name="model", type="string", length=255)
         */
        private $model;

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
        public function getProductNameAbb(): string
        {
            return $this->product_name_abb;
        }

        /**
         * @param string $product_name_abb
         */
        public function setProductNameAbb(string $product_name_abb): void
        {
            $this->product_name_abb = $product_name_abb;
        }

        /**
         * @return string
         */
        public function getJanCode(): string
        {
            return $this->jan_code;
        }

        /**
         * @param string $jan_code
         */
        public function setJanCode(string $jan_code): void
        {
            $this->jan_code = $jan_code;
        }

        /**
         * @return int
         */
        public function getUnitPrice(): int
        {
            return $this->unit_price;
        }

        /**
         * @param int $unit_price
         */
        public function setUnitPrice(int $unit_price): void
        {
            $this->unit_price = $unit_price;
        }

        /**
         * @return string
         */
        public function getTagCode1(): string
        {
            return $this->tag_code1;
        }

        /**
         * @param string $tag_code1
         */
        public function setTagCode1(string $tag_code1): void
        {
            $this->tag_code1 = $tag_code1;
        }

        /**
         * @return string
         */
        public function getTagName1(): string
        {
            return $this->tag_name1;
        }

        /**
         * @param string $tag_name1
         */
        public function setTagName1(string $tag_name1): void
        {
            $this->tag_name1 = $tag_name1;
        }

        /**
         * @return string
         */
        public function getTagCode2(): string
        {
            return $this->tag_code2;
        }

        /**
         * @param string $tag_code2
         */
        public function setTagCode2(string $tag_code2): void
        {
            $this->tag_code2 = $tag_code2;
        }

        /**
         * @return string
         */
        public function getTagName2(): string
        {
            return $this->tag_name2;
        }

        /**
         * @param string $tag_name2
         */
        public function setTagName2(string $tag_name2): void
        {
            $this->tag_name2 = $tag_name2;
        }

        /**
         * @return string
         */
        public function getTagCode3(): string
        {
            return $this->tag_code3;
        }

        /**
         * @param string $tag_code3
         */
        public function setTagCode3(string $tag_code3): void
        {
            $this->tag_code3 = $tag_code3;
        }

        /**
         * @return string
         */
        public function getTagName3(): string
        {
            return $this->tag_name3;
        }

        /**
         * @param string $tag_name3
         */
        public function setTagName3(string $tag_name3): void
        {
            $this->tag_name3 = $tag_name3;
        }

        /**
         * @return string
         */
        public function getTagCode4(): string
        {
            return $this->tag_code4;
        }

        /**
         * @param string $tag_code4
         */
        public function setTagCode4(string $tag_code4): void
        {
            $this->tag_code4 = $tag_code4;
        }

        /**
         * @return string
         */
        public function getTagName4(): string
        {
            return $this->tag_name4;
        }

        /**
         * @param string $tag_name4
         */
        public function setTagName4(string $tag_name4): void
        {
            $this->tag_name4 = $tag_name4;
        }

        /**
         * @return string
         */
        public function getTagCode5(): string
        {
            return $this->tag_code5;
        }

        /**
         * @param string $tag_code5
         */
        public function setTagCode5(string $tag_code5): void
        {
            $this->tag_code5 = $tag_code5;
        }

        /**
         * @return string
         */
        public function getTagName5(): string
        {
            return $this->tag_name5;
        }

        /**
         * @param string $tag_name5
         */
        public function setTagName5(string $tag_name5): void
        {
            $this->tag_name5 = $tag_name5;
        }

        /**
         * @return string
         */
        public function getCategoryCode1(): string
        {
            return $this->category_code1;
        }

        /**
         * @param string $category_code1
         */
        public function setCategoryCode1(string $category_code1): void
        {
            $this->category_code1 = $category_code1;
        }

        /**
         * @return string
         */
        public function getCategoryName1(): string
        {
            return $this->category_name1;
        }

        /**
         * @param string $category_name1
         */
        public function setCategoryName1(string $category_name1): void
        {
            $this->category_name1 = $category_name1;
        }

        /**
         * @return string
         */
        public function getCategoryCode2(): string
        {
            return $this->category_code2;
        }

        /**
         * @param string $category_code2
         */
        public function setCategoryCode2(string $category_code2): void
        {
            $this->category_code2 = $category_code2;
        }

        /**
         * @return string
         */
        public function getCategoryName2(): string
        {
            return $this->category_name2;
        }

        /**
         * @param string $category_name2
         */
        public function setCategoryName2(string $category_name2): void
        {
            $this->category_name2 = $category_name2;
        }

        /**
         * @return string
         */
        public function getCategoryCode3(): string
        {
            return $this->category_code3;
        }

        /**
         * @param string $category_code3
         */
        public function setCategoryCode3(string $category_code3): void
        {
            $this->category_code3 = $category_code3;
        }

        /**
         * @return string
         */
        public function getCategoryName3(): string
        {
            return $this->category_name3;
        }

        /**
         * @param string $category_name3
         */
        public function setCategoryName3(string $category_name3): void
        {
            $this->category_name3 = $category_name3;
        }

        /**
         * @return string
         */
        public function getSeriesName(): string
        {
            return $this->series_name;
        }

        /**
         * @param string $series_name
         */
        public function setSeriesName(string $series_name): void
        {
            $this->series_name = $series_name;
        }

        /**
         * @return string
         */
        public function getLineNo(): string
        {
            return $this->line_no;
        }

        /**
         * @param string $line_no
         */
        public function setLineNo(string $line_no): void
        {
            $this->line_no = $line_no;
        }

        /**
         * @return int
         */
        public function getQuantity(): int
        {
            return $this->quantity;
        }

        /**
         * @param int $quantity
         */
        public function setQuantity(int $quantity): void
        {
            $this->quantity = $quantity;
        }

        /**
         * @return string
         */
        public function getSize(): string
        {
            return $this->size;
        }

        /**
         * @param string $size
         */
        public function setSize(string $size): void
        {
            $this->size = $size;
        }

        /**
         * @return string
         */
        public function getColor(): string
        {
            return $this->color;
        }

        /**
         * @param string $color
         */
        public function setColor(string $color): void
        {
            $this->color = $color;
        }

        /**
         * @return string
         */
        public function getMaterial(): string
        {
            return $this->material;
        }

        /**
         * @param string $material
         */
        public function setMaterial(string $material): void
        {
            $this->material = $material;
        }

        /**
         * @return string
         */
        public function getModel(): string
        {
            return $this->model;
        }

        /**
         * @param string $model
         */
        public function setModel(string $model): void
        {
            $this->model = $model;
        }

    }
}
