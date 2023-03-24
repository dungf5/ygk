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

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\DtImportCSV;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtImportCsvRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtImportCSV::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtImportCSV();

            $object->setFileName($data['file_name']);
            $object->setDirectory($data['directory']);
            $object->setMessage($data['message']);
            $object->setIsSync($data['is_sync']);
            $object->setIsError($data['is_error']);
            $object->setIsSendMail($data['is_send_mail']);
            $object->setInDate($data['in_date']);
            $object->setUpDate($data['up_date']);

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return;
        } catch (\Exception $e) {
            return;
        }
    }

    public function updateData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = $this->findOneBy(['file_name' => $data['file_name']]);

            if (!empty($object)) {
                foreach ($data as $key => $value) {
                    if ($key == 'file_name') {
                        $object->setFileName($value);
                    }
                    if ($key == 'directory') {
                        $object->setDirectory($value);
                    }
                    if ($key == 'message') {
                        $object->setMessage($value);
                    }
                    if ($key == 'is_sync') {
                        $object->setIsSync($value);
                    }
                    if ($key == 'is_error') {
                        $object->setIsError($value);
                    }
                    if ($key == 'is_send_mail') {
                        $object->setIsSendMail($value);
                    }
                    if ($key == 'in_date') {
                        $object->setInDate($value);
                    }
                    if ($key == 'up_date') {
                        $object->setUpDate($value);
                    }
                }

                $this->getEntityManager()->persist($object);
                $this->getEntityManager()->flush();
            }

            return;
        } catch (\Exception $e) {
            return;
        }
    }
}
