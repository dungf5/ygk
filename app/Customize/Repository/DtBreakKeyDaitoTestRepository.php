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
use Customize\Entity\DtBreakKeyDaitoTest;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtBreakKeyDaitoTestRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtBreakKeyDaitoTest::class);
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
                $break_key = (int) $object->getBreakKey() + (int) $data['break_key'];
                $object->setBreakKey($break_key);
            } else {
                $object = new DtBreakKeyDaitoTest();
                $break_key = $data['break_key'];
                $object->setCustomerCode($data['customer_code']);
                $object->setBreakKey($break_key);
            }

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return $break_key;
        } catch (\Exception $e) {
            log_info('Insert or update dt_break_key error');
            log_info($e->getMessage());

            return 0;
        }
    }
}
