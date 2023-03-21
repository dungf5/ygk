<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\Date;

if (!class_exists('\Customize\Entity\MstProductReturnsInfo', false)) {
    /**
     * MstProductReturnsInfo.php
     *
     * @ORM\Table(name="dt_order_status")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstProductReturnsInfo")
     */
    class MstProductReturnsInfo extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=15, options={"comment":"STRA注文番号"})
         */
        private $returns_no;

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string")
         */
        private $customer_code;
        
    }
}
