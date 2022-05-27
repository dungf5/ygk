<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\MoreOrder', false)) {
    /**
     * MoreOrder
     *
     * @ORM\Table(name="more_order")
     * @ORM\Entity(repositoryClass="Customize\Repository\MoreOrderRepository")
     */
    class MoreOrder extends AbstractEntity
    { 
        /**
         * @var string
         *
         * @ORM\Column(name="pre_order_id", type="string", length=255,options={"comment":""}, nullable=false)
         * @ORM\Id
         */
        private $pre_order_id;
/**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $order_no;
/**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=true, type="string", length=10, options={"comment":"address to customer for bill"})
         */
        private $seikyu_code;
/**
         * @var string
         *
         * @ORM\Column(name="shipping_code",nullable=true, type="string", length=4, options={"comment":"Position store"})
         */
        private $shipping_code;
/**
         * @var string
         *
         * @ORM\Column(name="otodoke_code",nullable=true, type="string", length=4, options={"comment":"address to customer for product"})
         */
        private $otodoke_code;
    }
}
