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

if (!class_exists('\Customize\Entity\ProductImage', false)) {
    /**
     * ProductImage
     *
     * @ORM\Table(name="dtb_product_image")
     * @ORM\Entity(repositoryClass="Customize\Repository\ProductImageRepository")
     */
    class ProductImage extends AbstractEntity
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
         * @ORM\Column(name="product_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $product_id;
        /**
         * @ORM\Column(name="creator_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $creator_id;
        /**
         * @var string
         *
         * @ORM\Column(name="file_name",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $file_name;
        /**
         * @ORM\Column(name="sort_no",type="integer",nullable=false, options={"comment":""  })
         */
        private $sort_no;
        /**
         * @var string
         *
         * @ORM\Column(name="discriminator_type",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $discriminator_type;
    }
}
