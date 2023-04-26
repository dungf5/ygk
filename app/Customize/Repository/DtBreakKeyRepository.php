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
use Customize\Entity\DtBreakKey;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtBreakKeyRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtBreakKey::class);
    }

    public function insertOrUpdate($data = [])
    {
        try {
            if (empty($data)) {
                return 0;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = $this->findOneBy(['customer_code' => $data['customer_code']]);

            if (!empty($object)) {
                $object->setBreakKey($data['break_key']);
            } else {
                $object = new DtBreakKey();
                $object->setCustomerCode($data['customer_code']);
                $object->setBreakKey($data['break_key']);
            }

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return 1;
        } catch (\Exception $e) {
            log_info('Insert or update dt_break_key error');
            log_info($e->getMessage());

            return 0;
        }
    }
}
