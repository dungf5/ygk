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

if (!class_exists('\Customize\Entity\MstShipping', false)) {
    /**
     * MstProduct
     *
     * @ORM\Table(name="mst_shipping")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstShippingRepository")
     */
    class MstShipping extends AbstractEntity
    {


        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no", type="string", length=15, nullable=false)
         * @ORM\Id
         */
        protected $id;

        /**
         * @var string
         *
         * @ORM\Column(name="order_no", type="string", length=15)
         */
        private $order_no;

        /**
         * @var string
         *
         * @ORM\Column(name="order_lineno", type="string", length=8)
         */
        private $order_lineno;

        /**
         * @var int
         *
         * @ORM\Column(name="shipping_status", type="smallint", nullable=true)
         */
        private $shipping_status;


        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_no", type="string", length=15)
         */
        private $ec_order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="ec_order_lineno", type="string", length=15)
         */
        private $ec_order_lineno;


        /**
         * @var int
         *
         * @ORM\Column(name="shipping_num", type="integer", nullable=false, options={"default":0})
         */
        private $shipping_num;


        /**
         * @var string
         *
         * @ORM\Column(name="shipping_plan_date", type="string", length=10)
         */
        private $shipping_plan_date;

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date", type="string", length=10)
         */
        private $shipping_date;

        /**
         * @var string
         *
         * @ORM\Column(name="inquiry_no", type="string", length=10)
         */
        private $inquiry_no;

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_company_code", type="string", length=10)
         */
        private $shipping_company_code;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;


        /**
         * @var \Eccube\Entity\Order
         *
         * @ORM\OneToOne(targetEntity="Eccube\Entity\Order")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
         * })
         */
        private $Order;

        /**
         * @param mixed $Order
         */
        public function setOrder($Order): void
        {
            $this->Order = $Order;
        }




    }
}
