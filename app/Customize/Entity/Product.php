<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\ProductImage;

if (!class_exists('\Customize\Entity\Product', false)) {
    /**
     * Product
     *
     * @ORM\Table(name="dtb_product")
     * @ORM\Entity(repositoryClass="Customize\Repository\ProductRepository")
     */
    class Product extends AbstractEntity
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
         * @return int
         */
        public function getId(): int
        {
            return $this->id;
        }

        /**
         * @var \Doctrine\Common\Collections\Collection
         *
         * @ORM\OneToMany(targetEntity="Eccube\Entity\ProductImage", mappedBy="Product", cascade={"remove"})
         * @ORM\OrderBy({
         *     "sort_no"="ASC"
         * })
         */
        private $ProductImage;

        /**
         * @var string         *
         */
        private $MainListImage;

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
        public function getCreatorId()
        {
            return $this->creator_id;
        }

        /**
         * @param mixed $creator_id
         */
        public function setCreatorId($creator_id): void
        {
            $this->creator_id = $creator_id;
        }

        /**
         * @return mixed
         */
        public function getProductStatusId()
        {
            return $this->product_status_id;
        }

        /**
         * @param mixed $product_status_id
         */
        public function setProductStatusId($product_status_id): void
        {
            $this->product_status_id = $product_status_id;
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @param string $name
         */
        public function setName(string $name): void
        {
            $this->name = $name;
        }

        /**
         * @return string
         */
        public function getNote(): string
        {
            return $this->note;
        }

        /**
         * @param string $note
         */
        public function setNote(string $note): void
        {
            $this->note = $note;
        }

        /**
         * @return string
         */
        public function getDescriptionList(): string
        {
            return $this->description_list;
        }

        /**
         * @param string $description_list
         */
        public function setDescriptionList(string $description_list): void
        {
            $this->description_list = $description_list;
        }

        /**
         * @return string
         */
        public function getDescriptionDetail(): string
        {

            return $this->description_detail;
        }

        /**
         * @param string $description_detail
         */
        public function setDescriptionDetail(string $description_detail): void
        {
            $this->description_detail = $description_detail;
        }

        /**
         * @return string
         */
        public function getSearchWord(): string
        {
            return $this->search_word;
        }

        /**
         * @param string $search_word
         */
        public function setSearchWord(string $search_word): void
        {
            $this->search_word = $search_word;
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

        /**
         * @ORM\Column(name="creator_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $creator_id;
        /**
         * @ORM\Column(name="product_status_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $product_status_id;
        /**
         * @var string
         *
         * @ORM\Column(name="name",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $name;
        /**
         * @var string
         *
         * @ORM\Column(name="note",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $note;
        /**
         * @var string
         *
         * @ORM\Column(name="description_list",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $description_list;
        /**
         * @var string
         *
         * @ORM\Column(name="description_detail",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $description_detail;
        /**
         * @var string
         *
         * @ORM\Column(name="search_word",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $search_word;
        /**
         * @var string
         *
         * @ORM\Column(name="discriminator_type",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $discriminator_type;

        public function getMainListImage()
        {
            $ProductImages = $this->getProductImage();
            //$this->MainListImage = empty($ProductImages) ? null : $ProductImages[0];

            return empty($ProductImages) ? null : $ProductImages[0];
        }

        /**
         * @param string $mehien
         */
        public function getMehien()
        {
            return "xxx";
        }
        /**
         * Get productImage.
         *
         * @return \Doctrine\Common\Collections\Collection
         */
        public function getProductImage()
        {
            return $this->ProductImage;
        }
    }
}
