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

if (!class_exists('\Customize\Entity\MstShippingRoute', false)) {
    /**
     * MstShippingRoute
     *
     * @ORM\Table(name="mst_shipping_route")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstShippingRouteRepository")
     */
    class MstShippingRoute extends AbstractEntity
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
         * @ORM\Column(name="stock_location", type="string", length=10,options={"comment":"在庫場所"}, nullable=false)
         * @ORM\Id
         */
        private $stock_location;
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
         * @return string
         */
        public function getCustomerCode(): string
        {
            return $this->customer_code;
        }

        /**
         * @param string $customer_code
         */
        public function setCustomerCode(string $customer_code): void
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return string
         */
        public function getStockLocation(): string
        {
            return $this->stock_location;
        }

        /**
         * @param string $stock_location
         */
        public function setStockLocation(string $stock_location): void
        {
            $this->stock_location = $stock_location;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate(): \DateTime
        {
            return $this->create_date;
        }

        /**
         * @param \DateTime $create_date
         */
        public function setCreateDate(\DateTime $create_date): void
        {
            $this->create_date = $create_date;
        }

        /**
         * @return \DateTime
         */
        public function getUpdateDate(): \DateTime
        {
            return $this->update_date;
        }

        /**
         * @param \DateTime $update_date
         */
        public function setUpdateDate(\DateTime $update_date): void
        {
            $this->update_date = $update_date;
        }

    }
}
