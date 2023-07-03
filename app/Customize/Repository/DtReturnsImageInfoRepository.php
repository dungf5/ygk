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

use Customize\Entity\DtReturnsImageInfo;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtReturnsImageInfoRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtReturnsImageInfo::class);
    }

    public function insertData($data = [])
    {
        if (empty($data)) {
            return;
        }

        try {
            $object = $this->findOneBy(['returns_no' => $data['returns_no']]);
            if (!$object) {
                $object = new DtReturnsImageInfo();
                $object->setReturnsNo($data['returns_no']);
            }

            if (!empty($data['cus_image_url_path1'])) {
                $object->setCusImageUrlPath1($data['cus_image_url_path1']);
            }
            if (!empty($data['cus_image_url_path2'])) {
                $object->setCusImageUrlPath2($data['cus_image_url_path2']);
            }
            if (!empty($data['cus_image_url_path3'])) {
                $object->setCusImageUrlPath3($data['cus_image_url_path3']);
            }
            if (!empty($data['cus_image_url_path4'])) {
                $object->setCusImageUrlPath4($data['cus_image_url_path4']);
            }
            if (!empty($data['cus_image_url_path5'])) {
                $object->setCusImageUrlPath5($data['cus_image_url_path5']);
            }
            if (!empty($data['cus_image_url_path6'])) {
                $object->setCusImageUrlPath6($data['cus_image_url_path6']);
            }
            if (!empty($data['stock_image_url_path1'])) {
                $object->setStockImageUrlPath1($data['stock_image_url_path1']);
            }
            if (!empty($data['stock_image_url_path2'])) {
                $object->setStockImageUrlPath2($data['stock_image_url_path2']);
            }
            if (!empty($data['stock_image_url_path3'])) {
                $object->setStockImageUrlPath3($data['stock_image_url_path3']);
            }
            if (!empty($data['stock_image_url_path4'])) {
                $object->setStockImageUrlPath4($data['stock_image_url_path4']);
            }
            if (!empty($data['stock_image_url_path5'])) {
                $object->setStockImageUrlPath5($data['stock_image_url_path5']);
            }
            if (!empty($data['stock_image_url_path6'])) {
                $object->setStockImageUrlPath6($data['stock_image_url_path6']);
            }

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return $object;
        } catch (\Exception $e) {
        }

        return;
    }
}
