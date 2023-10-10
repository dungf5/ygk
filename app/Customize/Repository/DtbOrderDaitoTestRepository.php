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
use Customize\Entity\DtbOrderDaitoTest;
use Doctrine\DBAL\Types\Type;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DtbOrderDaitoTestRepository extends AbstractRepository
{
    /**
     * MstProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DtbOrderDaitoTest::class);
    }

    public function insertData($data = [])
    {
        try {
            if (empty($data)) {
                return 0;
            }

            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $object = new DtbOrderDaitoTest();

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            sleep(1);

            return $object->getId();
        } catch (\Exception $e) {
            log_info('Insert dtb_order error');
            log_info($e->getMessage());

            $message = 'Insert dtb_order error';
            $message .= "\n".$e->getMessage();
            $this->pushGoogleChat($message);

            return 0;
        }
    }
}
