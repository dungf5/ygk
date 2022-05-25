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

if (!class_exists('\Customize\Entity\StockList', false)) {
    /**
     * StockList
     *
     * @ORM\Table(name="stock_list")
     * @ORM\Entity(repositoryClass="Customize\Repository\StockListRepository")
     */
    class StockList extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=10,options={"comment":"製品コード"}, nullable=false)
         * @ORM\Id
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=10,options={"comment":"顧客コード"}, nullable=false)
         * @ORM\Id
         */
        private $customer_code;
        /**
         * @ORM\Column(type="integer",nullable=false, options={"comment":"トータル在庫" ,"default":0 })
         */
        private $stock_num;
        /**
         * @ORM\Column(type="integer",nullable=false, options={"comment":"引当在庫" ,"default":0 })
         */
        private $reserve_num;
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
    }
}
