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

namespace Customize\Repository;

use Customize\Entity\ProductImage;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductImageRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    public function getImageMain($arProductId)
    {
        $proImgList = $this->createQueryBuilder('pm')->select(['pm'])
            ->where('pm.product_id in(:product_ids)')
            ->setParameter('product_ids', $arProductId)
            ->addOrderBy('pm.sort_no', 'ASC')
            ->getQuery()->getArrayResult();
        $hsProductImgMain = [];
        foreach ($proImgList as $myItem) {
            if (!isset($hsProductImgMain[$myItem['product_id']])) {
                $hsProductImgMain[$myItem['product_id']] = $myItem['file_name'];
            }
        }
        return $hsProductImgMain;
    }
}
