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

use Customize\Common\MyCommon;
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Repository\MstProductRepository;
use Customize\Repository\PriceRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Cart;
use Eccube\Entity\ProductClass;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Service\CartService;
use Eccube\Service\OrderHelper;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseFlowResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MyCartController extends AbstractController
{
    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var BaseInfo
     */
    protected $baseInfo;

    /**
     * @var PriceRepository
     */
    protected $priceRepository;

    /**
     * @var MstProductRepository
     */
    protected $mstProductRepository;

    /**
     * @var GlobalService
     */
    protected $globalService;

    /**
     * MyCartController constructor.
     *
     * @param ProductClassRepository $productClassRepository
     * @param CartService $cartService
     * @param PurchaseFlow $cartPurchaseFlow
     * @param BaseInfoRepository $baseInfoRepository
     * @param PriceRepository $priceRepository
     * @param MstProductRepository $mstProductRepository
     */
    public function __construct(
        ProductClassRepository $productClassRepository,
        CartService $cartService,
        PurchaseFlow $cartPurchaseFlow,
        BaseInfoRepository $baseInfoRepository,
        PriceRepository $priceRepository,
        MstProductRepository $mstProductRepository,
        EntityManagerInterface $entityManager,
        GlobalService $globalService
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->cartService = $cartService;
        $this->purchaseFlow = $cartPurchaseFlow;
        $this->baseInfo = $baseInfoRepository->get();
        $this->priceRepository = $priceRepository;
        $this->mstProductRepository = $mstProductRepository;
        $this->entityManager = $entityManager;
        $this->globalService = $globalService;
    }

    /**
     * カート画面.
     *
     * @Route("/cart", name="cart")
     * @Template("Cart/index.twig")
     */
    public function index(Request $request)
    {
        // カートを取得して明細の正規化を実行
        $Carts = $this->cartService->getCarts();
        $this->execPurchaseFlow($Carts);

        // TODO itemHolderから取得できるように
        $totalPrice = 0;
        $totalQuantity = 0;
        $onecart_id = '';
        $onecart_key = '';

        foreach ($Carts as $Cart) {
            $totalPrice += $Cart->getTotalPrice();
            $totalQuantity += $Cart->getQuantity();

            $cartItems = $Cart->getCartItems();

            foreach ($cartItems as &$cartItem) {
                $productClass = $cartItem->getProductClass();
                $product = $productClass->getProduct();

                // Get mst_product
                $mstProduct = $this->mstProductRepository->getData($product->getId());

                $product->setName($mstProduct['product_name'] ?? '');
                $product->setJan($mstProduct['jan_code'] ?? '');
                $product->setQuantity((int) $mstProduct['quantity'] ?? 1);
                $product->setUnitPrice((int) $mstProduct['unit_price'] ?? 0);
                $product->setPrice((int) $cartItem->getPrice() ?? 0);
            }

            $onecart_id = $Cart->getId();
            $onecart_key = $Cart->getCartKey();
        }

        // カートが分割された時のセッション情報を削除
        $request->getSession()->remove(OrderHelper::SESSION_CART_DIVIDE_FLAG);

        return [
            'totalPrice' => $totalPrice,
            'totalQuantity' => $totalQuantity,
            // 空のカートを削除し取得し直す
            'Carts' => $this->cartService->getCarts(true),
            'onecart_id' => $onecart_id,
            'onecart_key' => $onecart_key,
        ];
    }

    /**
     * ページタイトルの設定
     *
     * @param  array|null $searchData
     *
     * @return str
     */
    protected function getPageTitle($searchData)
    {
        if (isset($searchData['name']) && !empty($searchData['name'])) {
            return trans('front.product.search_result');
        } elseif (isset($searchData['category_id']) && $searchData['category_id']) {
            return $searchData['category_id']->getName();
        } else {
            return trans('front.product.all_products');
        }
    }

    /**
     * カートに追加.
     *
     * @Route("/cart/update_cart", name="update_cart",methods={"POST"})
     */
    public function upCart(Request $request)
    {
        try {
            $productClassId = $request->get('productClassId');
            $current_quantity = $request->get('current_quantity');
            $quantity = $request->get('quantity');
            log_info('カート明細操作開始', ['product_class_id' => $productClassId]);

            $this->isTokenValid();

            /** @var ProductClass $ProductClass */
            $ProductClass = $this->productClassRepository->find($productClassId);

            if (is_null($ProductClass)) {
                log_info('商品が存在しないため、カート画面へredirect', ['product_class_id' => $productClassId]);

                return $this->redirectToRoute('cart');
            }

            // Get mst_product
            Type::overrideType('datetimetz', UTCDateTimeTzType::class);
            $commonService = new MyCommonService($this->entityManager);
            $mstProduct = $this->mstProductRepository->getData($ProductClass->getProduct()->getId());
            $price = $mstProduct['unit_price'] ?? 0;
            $mst_quantity = $mstProduct['quantity'] ?? 1;

            // Override price
            $customer_code = $this->globalService->customerCode();
            $login_type = $this->globalService->getLoginType();
            $login_code = $this->globalService->getLoginCode();
            $relationCus = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

            if ($relationCus) {
                $customerCode = $relationCus['customer_code'];
                $shippingCode = $relationCus['shipping_code'];

                if (empty($shippingCode)) {
                    $shippingCode = $this->globalService->getShippingCode();
                }

                $dtPrice = $commonService->getPriceFromDtPrice($customerCode, $shippingCode, $mstProduct->getProductCode());
            }

            if (!empty($dtPrice)) {
                $price = $dtPrice['price_s01'];
            }
            // End - Override price

            $this->cartService->addProduct($ProductClass, ($quantity / $mst_quantity) - ($current_quantity / $mst_quantity), $price);

            setcookie($ProductClass->getProduct()->getId(), $quantity, 0, '/');

            // カートを取得して明細の正規化を実行
            $Carts = $this->cartService->getCarts();
            $this->execPurchaseFlow($Carts);

            return $this->json(['status' => 1, 'msg' => ''], 200);
        } catch (\Exception $e) {
            return $this->json(['status' => 1, 'msg' => $e->getMessage()], 400);
        }
    }

    /**
     * カート明細の加算/減算/削除を行う.
     *
     * - 加算
     *      - 明細の個数を1増やす
     * - 減算
     *      - 明細の個数を1減らす
     *      - 個数が0になる場合は、明細を削除する
     * - 削除
     *      - 明細を削除する
     *
     * @Route(
     *     path="/cart/{operation}/{productClassId}",
     *     name="cart_handle_item",
     *     methods={"PUT"},
     *     requirements={
     *          "operation": "up|down|remove",
     *          "productClassId": "\d+"
     *     }
     * )
     */
    public function handleCartItem($operation, $productClassId)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        log_info('カート明細操作開始', ['operation' => $operation, 'product_class_id' => $productClassId]);

        $this->isTokenValid();

        /** @var ProductClass $ProductClass */
        $ProductClass = $this->productClassRepository->find($productClassId);

        if (is_null($ProductClass)) {
            log_info('商品が存在しないため、カート画面へredirect', ['operation' => $operation, 'product_class_id' => $productClassId]);

            return $this->redirectToRoute('cart');
        }

        $mstProduct = $this->mstProductRepository->getData($ProductClass->getProduct()->getId());

        $idRemove = 0;
        // 明細の増減・削除
        switch ($operation) {
            case 'up':
                $this->cartService->addProductCustomize($ProductClass, $mstProduct->getQuantity());
                break;
            case 'down':
                $this->cartService->addProductCustomize($ProductClass, -1 * $mstProduct->getQuantity());
                break;
            case 'remove':
                $this->cartService->removeProductCustomize($ProductClass);
                $idRemove = $ProductClass->getProduct()->getId();
                break;
        }

        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            $totalPrice = 0;
            foreach ($Cart['CartItems'] as $CartItem) {
                $totalPrice += $CartItem['price'] * $CartItem['quantity'];
                setcookie($ProductClass->getProduct()->getId(), $CartItem['quantity'] * $mstProduct->getQuantity(), 0, '/');
            }

            $Cart->setTotalPrice($totalPrice);
            $Cart->setDeliveryFeeTotal(0);
        }
        $this->cartService->saveCustomize();
        if ((int) $idRemove > 0) {
            setcookie($idRemove, null, -1, '/');
        }
        // カートを取得して明細の正規化を実行
//        $Carts = $this->cartService->getCarts();
//        $this->execPurchaseFlow($Carts);

        // Delete session cart product type
        if (!count($Carts)) {
            unset($_SESSION['cart_product_type']);
        }

        log_info('カート演算処理終了', ['operation' => $operation, 'product_class_id' => $productClassId]);

        return $this->redirectToRoute('cart');
//        $myComS = new MyCommonService($this->entityManager);
//        $totalNew=0;
//        if(count($Carts)>0){
//            $cartId = $Carts[0]->getId();
//            $totalNew = $myComS->getTotalItemCart($cartId);
//        }
    }

    /**
     * @Route("/block/cart", name="block_cart", methods={"GET"})
     * @Route("/block/cart_sp", name="block_cart_sp", methods={"GET"})
     */
    public function addCart(Request $request)
    {
        $Carts = $this->cartService->getCarts();

        // 二重に実行され, 注文画面でのエラーハンドリングができないので
        // ここではpurchaseFlowは実行しない

        $totalQuantity = array_reduce($Carts, function ($total, $Cart) {
            /* @var Cart $Cart */
            $total += $Cart->getTotalQuantity();

            return $total;
        }, 0);
        $totalPrice = array_reduce($Carts, function ($total, $Cart) {
            /* @var Cart $Cart */
            $total += $Cart->getTotalPrice();

            return $total;
        }, 0);

        $route = $request->attributes->get('_route');

        if ($route == 'block_cart_sp') {
            return $this->render('Block/nav_sp.twig', [
                'totalQuantity' => $totalQuantity,
                'totalPrice' => $totalPrice,
                'Carts' => $Carts,
            ]);
        } else {
            return $this->render('Block/cart.twig', [
                'totalQuantity' => $totalQuantity,
                'totalPrice' => $totalPrice,
                'Carts' => $Carts,
            ]);
        }
    }

    /**
     * @param $Carts
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function execPurchaseFlow($Carts)
    {
        /** @var PurchaseFlowResult[] $flowResults */
        $flowResults = array_map(function ($Cart) {
            $purchaseContext = new PurchaseContext($Cart, $this->getUser());

            return $this->purchaseFlow->validate($Cart, $purchaseContext);
        }, $Carts);

        // 復旧不可のエラーが発生した場合はカートをクリアして再描画
        $hasError = false;
        foreach ($flowResults as $result) {
            if ($result->hasError()) {
                $hasError = true;
                foreach ($result->getErrors() as $error) {
                    $this->addRequestError($error->getMessage());
                }
            }
        }
        if ($hasError) {
            $this->cartService->clear();

            return $this->redirectToRoute('cart');
        }

        $this->cartService->save();

        foreach ($flowResults as $index => $result) {
            foreach ($result->getWarning() as $warning) {
                if ($Carts[$index]->getItems()->count() > 0) {
                    $cart_key = $Carts[$index]->getCartKey();
                    $this->addRequestError($warning->getMessage(), "front.cart.${cart_key}");
                } else {
                    // キーが存在しない場合はグローバルにエラーを表示する
                    $this->addRequestError($warning->getMessage());
                }
            }
        }
    }
}
