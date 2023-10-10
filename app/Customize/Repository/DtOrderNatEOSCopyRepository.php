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
use Customize\Entity\DtOrderNatEOSCopy;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderNatEOSCopyRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderNatEOSCopy::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtOrderNatEOSCopy();
            $object->setReqcd($data['reqcd']);
            $object->setJan($data['jan']);
            $object->setMkrcd($data['mkrcd']);
            $object->setNatcd($data['natcd']);
            $object->setQty($data['qty']);
            $object->setCost((int) $data['cost']);
            $object->setDeliveryDay(!empty($data['delivery_day']) ? date('Ymd', strtotime($data['delivery_day'])) : '');

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return;
        } catch (\Exception $e) {
            log_info('Insert dt_order_nat_eos error');
            log_info($e->getMessage());

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
            $object = $this->findOneBy([
                'reqcd' => $data['reqcd'] ?? '',
                'jan' => $data['jan'] ?? '',
            ]);

            if (!empty($object)) {
                $object->setReqcd($data['reqcd']);
                $object->setJan($data['jan']);
                $object->setMkrcd($data['mkrcd']);
                $object->setNatcd($data['natcd']);
                $object->setQty($data['qty']);
                $object->setCost((int) $data['cost']);
                $object->setDeliveryDay(!empty($data['delivery_day']) ? date('Ymd', strtotime($data['delivery_day'])) : '');

                $this->getEntityManager()->persist($object);
                $this->getEntityManager()->flush();
            }

            return;
        } catch (\Exception $e) {
            log_info('Update dt_order_nat_eos error');
            log_info($e->getMessage());

            return;
        }
    }
}
