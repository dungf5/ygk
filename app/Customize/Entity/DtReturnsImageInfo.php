<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\Date;

if (!class_exists('\Customize\Entity\DtReturnsImageInfo', false)) {
    /**
     * DtReturnsImageInfo.php
     *
     * @ORM\Table(name="dt_returns_image_info")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtReturnsImageInfo")
     */
    class DtReturnsImageInfo extends AbstractEntity
    {
        /**
         * @var integer
         * @ORM\Column(name="returns_no", type="string", nullable=false)
         * @ORM\Id
         */
        private $returns_no;
        /**
         * @return string
         */
        public function getReturnsNo(): string
        {
            return $this->returns_no;
        }
        /**
         * @param string $returns_no
         */
        public function setReturnsNo(string $returns_no): void
        {
            $this->returns_no = $returns_no;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path1", type="string")
         */
        private $cus_image_url_path1;
        /**
         * @return string
         */
        public function getCusImageUrlPath1(): string
        {
            return $this->cus_image_url_path1;
        }
        /**
         * @param string $cus_image_url_path1
         */
        public function setCusImageUrlPath1(?string $cus_image_url_path1): void
        {
            $this->cus_image_url_path1 = $cus_image_url_path1;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path2", type="string")
         */
        private $cus_image_url_path2;
        /**
         * @return string
         */
        public function getCusImageUrlPath2(): string
        {
            return $this->cus_image_url_path2;
        }
        /**
         * @param string $cus_image_url_path2
         */
        public function setCusImageUrlPath2(?string $cus_image_url_path2): void
        {
            $this->cus_image_url_path2 = $cus_image_url_path2;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path3", type="string")
         */
        private $cus_image_url_path3;
        /**
         * @return string
         */
        public function getCusImageUrlPath3(): string
        {
            return $this->cus_image_url_path3;
        }
        /**
         * @param string $cus_image_url_path2
         */
        public function setCusImageUrlPath3(?string $cus_image_url_path3): void
        {
            $this->cus_image_url_path3 = $cus_image_url_path3;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path4", type="string")
         */
        private $cus_image_url_path4;
        /**
         * @return string
         */
        public function getCusImageUrlPath4(): string
        {
            return $this->cus_image_url_path4;
        }
        /**
         * @param string $cus_image_url_path4
         */
        public function setCusImageUrlPath4(?string $cus_image_url_path4): void
        {
            $this->cus_image_url_path4 = $cus_image_url_path4;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path5", type="string")
         */
        private $cus_image_url_path5;
        /**
         * @return string
         */
        public function getCusImageUrlPath5(): string
        {
            return $this->cus_image_url_path5;
        }
        /**
         * @param string $cus_image_url_path5
         */
        public function setCusImageUrlPath5(?string $cus_image_url_path5): void
        {
            $this->cus_image_url_path5 = $cus_image_url_path5;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path6", type="string")
         */
        private $cus_image_url_path6;
        /**
         * @return string
         */
        public function getCusImageUrlPath6(): string
        {
            return $this->cus_image_url_path6;
        }
        /**
         * @param string $cus_image_url_path6
         */
        public function setCusImageUrlPath6(?string $cus_image_url_path6): void
        {
            $this->cus_image_url_path6 = $cus_image_url_path6;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path1", type="string")
         */
        private $stock_image_url_path1;
        /**
         * @return string
         */
        public function getStockImageUrlPath1(): string
        {
            return $this->stock_image_url_path1;
        }
        /**
         * @param string $stock_image_url_path1
         */
        public function setStockImageUrlPath1(string $stock_image_url_path1): void
        {
            $this->stock_image_url_path1 = $stock_image_url_path1;
        }
        
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path2", type="string")
         */
        private $stock_image_url_path2;
        /**
         * @return string
         */
        public function getStockImageUrlPath2(): string
        {
            return $this->stock_image_url_path2;
        }
        /**
         * @param string $stock_image_url_path2
         */
        public function setStockImageUrlPath2(string $stock_image_url_path2): void
        {
            $this->stock_image_url_path2 = $stock_image_url_path2;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path3", type="string")
         */
        private $stock_image_url_path3;
        /**
         * @return string
         */
        public function getStockImageUrlPath3(): string
        {
            return $this->stock_image_url_path3;
        }
        /**
         * @param string $stock_image_url_path3
         */
        public function setStockImageUrlPath3(string $stock_image_url_path3): void
        {
            $this->stock_image_url_path3 = $stock_image_url_path3;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path4", type="string")
         */
        private $stock_image_url_path4;
        /**
         * @return string
         */
        public function getStockImageUrlPath4(): string
        {
            return $this->stock_image_url_path4;
        }
        /**
         * @param string $stock_image_url_path4
         */
        public function setStockImageUrlPath4(string $stock_image_url_path4): void
        {
            $this->stock_image_url_path4 = $stock_image_url_path4;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path5", type="string")
         */
        private $stock_image_url_path5;
        /**
         * @return string
         */
        public function getStockImageUrlPath5(): string
        {
            return $this->stock_image_url_path5;
        }
        /**
         * @param string $stock_image_url_path5
         */
        public function setStockImageUrlPath5(string $stock_image_url_path5): void
        {
            $this->stock_image_url_path5 = $stock_image_url_path5;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path6", type="string")
         */
        private $stock_image_url_path6;
        /**
         * @return string
         */
        public function getStockImageUrlPath6(): string
        {
            return $this->stock_image_url_path6;
        }
        /**
         * @param string $stock_image_url_path6
         */
        public function setStockImageUrlPath6(string $stock_image_url_path6): void
        {
            $this->stock_image_url_path6 = $stock_image_url_path6;
        }
    }
}