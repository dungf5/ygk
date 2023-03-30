<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Customize\Entity\DtReturnsImageInfo;
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
        if (empty($data)) return;
        
        try {
            $object = $this->findOneBy([ 'returns_no' => $data['returns_no'] ]);
            if( !$object ) {
                $object = new DtReturnsImageInfo();
                $object->setReturnsNo($data['returns_no']);
            }
            
            $object->setCusImageUrlPath1($data['cus_image_url_path1']);
            $object->setCusImageUrlPath2($data['cus_image_url_path2']);
            $object->setCusImageUrlPath3($data['cus_image_url_path3']);
            $object->setCusImageUrlPath4($data['cus_image_url_path4']);
            $object->setCusImageUrlPath5($data['cus_image_url_path5']);
            $object->setCusImageUrlPath6($data['cus_image_url_path6']);

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return $object;
        } catch (\Exception $e) {
        }

        return;
    }
}