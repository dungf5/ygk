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

if (!class_exists('\Customize\Entity\DtCustomerRelation', false)) {
    /**
     * DtCustomerRelation
     *
     * @ORM\Table(name="dt_customer_relation")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtCustomerRelationRepository")
     */
    class DtCustomerRelation extends AbstractEntity
    {
        /**
         * @var integer
         *
         * @ORM\Column(name="customer_code", type="integer", options={"unsigned":true})
         * @ORM\Id
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="represent_code", type="string", nullable=false)
         */
        private $represent_code;
        /**
         * @var string
         *
         * @ORM\Column(name="seikyu_code", type="string", length=10,options={"comment":"請求先コード noi nhan hoa don"}, nullable=false)
         * @ORM\Id
         */
        private $seikyu_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code", type="string", length=4,options={"comment":"届け先コード diachinhanhang mstdelivery"}, nullable=false)
         * @ORM\Id
         */
        private $otodoke_code;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ更新日時'")
         */
        private $update_date;

        /**
         * @return int
         */
        public function getCustomerCode(): int
        {
            return $this->customer_code;
        }

        /**
         * @param int $customer_code
         */
        public function setCustomerCode(int $customer_code)
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return string
         */
        public function getRepresentCode()
        {
            return $this->represent_code;
        }

        /**
         * @param $represent_code
         */
        public function setRepresentCode($represent_code)
        {
            $this->represent_code = $represent_code;
        }

        /**
         * @return string
         */
        public function getSeikyuCode()
        {
            return $this->seikyu_code;
        }

        /**
         * @param $seikyu_code
         */
        public function setSeikyuCode($seikyu_code)
        {
            $this->seikyu_code = $seikyu_code;
        }

        /**
         * @return string
         */
        public function getShippingCode()
        {
            return $this->shipping_code;
        }

        /**
         * @param $shipping_code
         */
        public function setShippingCode($shipping_code)
        {
            $this->shipping_code = $shipping_code;
        }

        /**
         * @return string
         */
        public function getOtodokeCode()
        {
            return $this->otodoke_code;
        }

        /**
         * @param $otodoke_code
         */
        public function setOtodokeCode($otodoke_code)
        {
            $this->otodoke_code = $otodoke_code;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * @param $create_date
         */
        public function setCreateDate($create_date)
        {
            $this->create_date = $create_date;
        }

        /**
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }

        /**
         * @param $update_date
         */
        public function setUpdateDate($update_date)
        {
            $this->update_date = $update_date;
        }
    }
}
