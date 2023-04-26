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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtBreakKey', false)) {
    /**
     * DtBreakKey
     *
     * @ORM\Table(name="dt_break_key")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtBreakKeyRepository")
     */
    class DtBreakKey extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=25,options={"comment":"顧客コード"}, nullable=false)
         * @ORM\Id
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="break_key",nullable=false, type="string", length=3, options={"comment":"ブレイクキー"})
         */
        private $break_key;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '更新日時'")
         */
        private $update_date;

        /**
         * @return string
         */
        public function getCustomerCode()
        {
            return $this->customer_code;
        }

        /**
         * @param $customer_code
         */
        public function setCustomerCode($customer_code)
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return string
         */
        public function getBreakKey()
        {
            return $this->break_key;
        }

        /**
         * @param $break_key
         */
        public function setBreakKey($break_key)
        {
            $this->break_key = $break_key;
        }
    }
}
