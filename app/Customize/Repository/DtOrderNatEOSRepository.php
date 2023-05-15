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
use Customize\Entity\DtOrderNatEOS;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtOrderNatEOSRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtOrderNatEOS::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtOrderNatEOS();
            $object->setReqcd($data['reqcd']);
            $object->setJan($data['jan']);
            $object->setMkrcd($data['mkrcd']);
            $object->setNatcd($data['natcd']);
            $object->setQty($data['qty']);
            $object->setCost((int) $data['cost']);
            $object->setDeliveryDay(!empty($data['delivery_day']) ? date('Ymd', strtotime($data['delivery_day'])) : '');
            $object->setOrderLineno((int) $data['order_lineno']);
            $object->setOrderImportDay(date('Ymd'));
            $object->setOrderRegistedFlg(0);
            $object->setShippingSentFlg(0);
            $object->setErrorType(0);
            $object->setErrorContent1(null);
            $object->setErrorContent2(null);
            $object->setErrorContent3(null);
            $object->setErrorContent4(null);
            $object->setErrorContent5(null);
            $object->setErrorContent6(null);
            $object->setErrorContent7(null);
            $object->setErrorContent8(null);
            $object->setErrorContent9(null);
            $object->setErrorContent10(null);

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
                $object->setOrderImportDay(date('Ymd'));
                $object->setErrorType(0);
                $object->setErrorContent1(null);
                $object->setErrorContent2(null);
                $object->setErrorContent3(null);
                $object->setErrorContent4(null);
                $object->setErrorContent5(null);
                $object->setErrorContent6(null);
                $object->setErrorContent7(null);
                $object->setErrorContent8(null);
                $object->setErrorContent9(null);
                $object->setErrorContent10(null);

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

    public function updateError($data = [])
    {
        try {
            if (empty($data)) {
                return;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = $this->findOneBy([
                'reqcd' => $data['reqcd'],
                'jan' => $data['jan'],
            ]);

            if (!empty($object)) {
                foreach ($data as $key => $value) {
                    if ($key == 'error_content1') {
                        $object->setErrorContent1($value);
                    }
                    if ($key == 'error_content2') {
                        $object->setErrorContent2($value);
                    }
                    if ($key == 'error_content3') {
                        $object->setErrorContent3($value);
                    }
                    if ($key == 'error_content4') {
                        $object->setErrorContent4($value);
                    }
                    if ($key == 'error_content5') {
                        $object->setErrorContent5($value);
                    }
                    if ($key == 'error_content6') {
                        $object->setErrorContent6($value);
                    }
                    if ($key == 'error_content7') {
                        $object->setErrorContent7($value);
                    }
                    if ($key == 'error_content8') {
                        $object->setErrorContent8($value);
                    }
                    if ($key == 'error_content9') {
                        $object->setErrorContent9($value);
                    }
                    if ($key == 'error_content10') {
                        $object->setErrorContent10($value);
                    }
                }
                $object->setErrorType(1);

                $this->getEntityManager()->persist($object);
                $this->getEntityManager()->flush();
            }

            return;
        } catch (\Exception $e) {
            return;
        }
    }
}
