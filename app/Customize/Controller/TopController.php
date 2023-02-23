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

namespace Customize\Controller;


use Customize\Service\Common\MyCommonService;


use Eccube\Controller\AbstractController;
use Eccube\Service\CartService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TopController extends AbstractController
{
    /**
     * @var CartService
     */
    public function __construct(

        CartService $cartService

    )
    {

        $this->cartService = $cartService;


    }

    protected $cartService;

    /**
     * @Route("/", name="homepage", methods={"GET"})
     * @Template("index.twig")
     */
    public function index()
    {



        $this->updateCart();

        return [];
    }

    private function updateCart()
    {
        $is_update_cart = $this->session->get("is_update_cart");

        $Cart = $this->cartService->getCart();
        if ($Cart == null) {
            return;
        }
        $arCarItemId =[];
        $commonService = new  MyCommonService($this->entityManager);
        $Customer = $this->getUser() ? $this->getUser() : null;
        if ($Customer != null) {
            $arCusLogin = $commonService->getMstCustomer($Customer->getId());
            if ($is_update_cart == 1) {
                $cartId = $Cart->getId();
                $productCart = $commonService->getdtPriceFromCart([$cartId], $arCusLogin["customer_code"]);
                $arPCodeTankaNumber = $commonService->getPriceFromDtPriceOfCusV2($arCusLogin["customer_code"]);
                $arPCode = $arPCodeTankaNumber[0];
                $arTanaka = $arPCodeTankaNumber[1];
                $hsHsProductCodeIndtPrice = [];
                $hsTanaka = [];
                foreach ($arPCode as $hasKey) {
                    $hsHsProductCodeIndtPrice[$hasKey] = 1;
                }
                foreach ($arTanaka as $hasKey) {
                    $hsTanaka[$hasKey] = 1;
                }

                $hsPriceUp = [];
                foreach ($productCart as $itemCart) {
                    if ($itemCart["price_s01"] != null && ($itemCart["price_s01"] != "")) {
                        $isPro = isset($hsHsProductCodeIndtPrice[$itemCart['product_code']]);
                        $isTana = isset($hsTanaka[$itemCart['tanka_number']]);
                        if ($isPro && $isTana) {
                            $hsPriceUp[$itemCart["id"]] = $itemCart["price_s01"];
                            $arCarItemId[] = $itemCart["id"];
                        }

                    }
                }
                $commonService->updateCartItem($hsPriceUp, $arCarItemId, $Cart);
                $this->session->set("is_update_cart", 0);
            }
            //*****************
        }
    }

    /**
     * Check user login.
     *
     * @Route("/check_user", name="check_user", methods={"POST"})
     */
    public function checkUserLogin (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $user_login                     = $request->get('login_email', '');
                $my_common                      = new MyCommonService($this->entityManager);

                if (!empty($user_login) && $user_login == 'su100') {
                    try {
                        $representList          = $my_common->getListRepresent($user_login);

                        return $this->json([
                            'status'            => 1,
                            'representOpt'      => $representList,
                        ], 200);

                    } catch (\Exception $e) {
                        return $this->json([
                            'status'            => -1,
                            'error'             => $e->getMessage(),
                        ], 400);
                    }
                }

                return $this->json([
                    'status'                    => 1,
                    'representOpt'              => [],
                ], 200);
            }

            return $this->json(['status' => 0], 400);

        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }
}
