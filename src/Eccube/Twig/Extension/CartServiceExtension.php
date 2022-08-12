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

namespace Eccube\Twig\Extension;

use Customize\Service\Common\MyCommonService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Cart;
use Eccube\Service\CartService;
use Twig\Extension\AbstractExtension;

class CartServiceExtension extends AbstractExtension
{
    /**
     * @var CartService
     */
    protected $cartService;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(CartService $cartService,  EntityManagerInterface $entityManager)
    {
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('get_cart', [$this, 'get_cart'], ['is_safe' => ['all']]),
            new \Twig_Function('get_all_carts', [$this, 'get_all_carts'], ['is_safe' => ['all']]),
            new \Twig_Function('get_carts_total_price', [$this, 'get_carts_total_price'], ['is_safe' => ['all']]),
            new \Twig_Function('get_carts_total_quantity', [$this, 'get_carts_total_quantity'], ['is_safe' => ['all']]),
        ];
    }

    public function get_cart()
    {
        return $this->cartService->getCart();
    }

    public function get_all_carts()
    {
        return $this->cartService->getCarts();
    }

    public function get_carts_total_price()
    {
        $Carts = $this->cartService->getCarts();
        $totalPrice = array_reduce($Carts, function ($total, Cart $Cart) {
            $total += $Cart->getTotalPrice();

            return $total;
        }, 0);

        return $totalPrice;
    }

    public function get_carts_total_quantity()
    {
        $Carts = $this->cartService->getCarts();
//        $totalQuantity = array_reduce($Carts, function ($total, Cart $Cart) {
//            $total += $Cart->getTotalQuantity();
//
//            return $total;
//        }, 0);
//
//        return $totalQuantity;
        $myComS = new MyCommonService($this->entityManager);
        $cartId = $Carts[0]->getId();
        $totalQuantity = $myComS->getTotalItemCart($cartId);
        return $totalQuantity;
    }
}
