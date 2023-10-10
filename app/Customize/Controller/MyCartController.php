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
use Eccube\Service\PurchaseFlow\PurchaseFlow;
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
     * MyCartController
     *
     * @param Request   $request
     * @Route("/cart_save_temp", name="cart_save_temp", methods={"POST"})
     * @Template("Ajax/add_list.twig")
     *
     * @return array
     */
    public function index(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $commonService = new MyCommonService($this->entityManager);
            $shipping_code = $request->get('shipping_code');
            $pre_order_id = $request->get('pre_order_id');
            $customer_id = $request->get('customer_id');
            $date_want_delivery = $request->get('date_want_delivery');
            $is_check_exist = $request->get('is_check_exist');

            if ($is_check_exist == 1) {
                echo $commonService->checkExistPreOrder($pre_order_id);
                exit();
            }

            $commonService->saveTempCart($shipping_code, $pre_order_id);
            $arrOtoProductOrder = $commonService->getCustomerOtodoke($this->globalService->getLoginType(), $this->globalService->customerId(), $shipping_code, null);
            $moreOrder = $commonService->getMoreOrder($pre_order_id);
            $data = (object) [];
            $otodoke_code_checked = '';

            if (!MyCommon::isEmptyOrNull($moreOrder)) {
                $data->moreOrder = $moreOrder;
                $data->hasMoreOrder = 1;

                foreach ($arrOtoProductOrder as $mS) {
                    if ($mS['otodoke_code'] == $moreOrder['otodoke_code']) {
                        $otodoke_code_checked = $mS['otodoke_code'];
                    }
                }
            } else {
                $data->hasMoreOrder = 0;
            }

            $data->otodoke_code_checked = $otodoke_code_checked;
            $data->shipping = $arrOtoProductOrder;

            $result = ['is_ok' => '1', 'msg' => 'OK', 'data' => $data];

            return $result;
        }

        return [];
    }

    /**
     * MyCartController
     *
     * @param Request   $request
     * @Route("/cart_save_temp_order", name="cart_save_temp_order", methods={"POST"})
     *
     * @return array
     */
    public function cartSaveTempOrder(Request $request)
    {
        $result = [
            'is_ok' => '0',
            'msg' => 'NG',
            'delivery_code' => '',
        ];

        if ('POST' === $request->getMethod()) {
            $commonService = new MyCommonService($this->entityManager);
            $delivery_code = $request->get('delivery_code');
            $date_want_delivery = $request->get('date_want_delivery');
            $pre_order_id = $request->get('pre_order_id');
            $bill_code = $request->get('bill_code');
            $date_want_delivery = $request->get('date_want_delivery');
            $remarks1 = $request->get('remarks1');
            $remarks2 = $request->get('remarks2');
            $remarks3 = $request->get('remarks3');
            $remarks4 = $request->get('remarks4');
            $customer_order_no = $request->get('customer_order_no');

            $result = [
                'is_ok' => '1',
                'msg' => 'OK',
                'delivery_code' => $delivery_code,
            ];

            if (!empty($bill_code)) {
                $commonService->saveTempCartBillSeiky($bill_code, $pre_order_id);

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'bill_code' => $bill_code,
                    'pre_order_id' => $pre_order_id,
                ];
            }

            if (!empty($delivery_code)) {
                $commonService->saveTempCartDeliCodeOto($delivery_code, $pre_order_id);

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'delivery_code' => $delivery_code,
                    'pre_order_id' => $pre_order_id,
                ];

                //Nạp lại session otodoke_code
                $_SESSION['s_otodoke_code'] = $delivery_code;
            }

            if (!MyCommon::isEmptyOrNull($date_want_delivery)) {
                $result = [
                    'is_ok' => '0',
                    'msg' => 'OK',
                    'date_want_delivery' => $date_want_delivery,
                    'pre_order_id' => $pre_order_id,
                ];

                $dayTest = $date_want_delivery;
                $comS = new MyCommonService($this->entityManager);
                $arrDayOff = $comS->getDayOff();
                $dayAfter = MyCommon::getValidDate($dayTest, MyCommon::getDayWeekend(), $arrDayOff);
                $dayAfterDay = new \DateTime($dayAfter);
                $curDay = new \DateTime();
                $curDay->modify('+1 month');

                if ($dayAfterDay > $curDay) {
                    $result = [
                        'is_ok' => '0',
                        'msg' => 'Over one months',
                        'date_want_delivery' => $dayAfter,
                        'pre_order_id' => $pre_order_id,
                    ];
                } else {
                    $commonService->saveTempCartDateWantDeli($dayAfter, $pre_order_id);
                    $result = [
                        'is_ok' => '1',
                        'msg' => 'OK saved',
                        'date_want_delivery' => $dayAfter,
                        'pre_order_id' => $pre_order_id,
                    ];
                }
            }

            if (isset($remarks1)) {
                $name = 'remarks1';
                $commonService->saveTempCartRemarks($pre_order_id, $name, trim($remarks1));

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'remarks1' => $remarks1,
                    'pre_order_id' => $pre_order_id,
                ];
            }

            if (isset($remarks2)) {
                $name = 'remarks2';
                $commonService->saveTempCartRemarks($pre_order_id, $name, trim($remarks2));

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'remarks2' => $remarks2,
                    'pre_order_id' => $pre_order_id,
                ];
            }

            if (isset($remarks3)) {
                $name = 'remarks3';
                $commonService->saveTempCartRemarks($pre_order_id, $name, trim($remarks3));

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'remarks3' => $remarks3,
                    'pre_order_id' => $pre_order_id,
                ];
            }

            if (isset($remarks4)) {
                $name = 'remarks4';
                $commonService->saveTempCartRemarks($pre_order_id, $name, trim($remarks4));

                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK',
                    'remarks4' => $remarks4,
                    'pre_order_id' => $pre_order_id,
                ];
            }

            return $this->json($result, 200);
        }

        return $this->json($result, 400);
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
     * カート画面.
     *
     * @Route("/cart", name="cart", methods={"GET"})
     * @Template("Cart/index.twig")
     */
    public function showCart(Request $request)
    {
        // カートを取得して明細の正規化を実行
        $Carts = $this->cartService->getCarts();

        //$this->execPurchaseFlow($Carts);
        // TODO itemHolderから取得できるように
        $least = [];
        $quantity = [];
        $isDeliveryFree = [];
        $totalPrice = 0;
        $totalQuantity = 0;
        $carSession = MyCommon::getCarSession();

        foreach ($Carts as $Cart) {
            if ($Cart['key_eccube'] !== $carSession) {
                continue;
            }

            $quantity[$Cart->getCartKey()] = 0;
            $isDeliveryFree[$Cart->getCartKey()] = false;

            if ($this->baseInfo->getDeliveryFreeQuantity()) {
                if ($this->baseInfo->getDeliveryFreeQuantity() > $Cart->getQuantity()) {
                    $quantity[$Cart->getCartKey()] = $this->baseInfo->getDeliveryFreeQuantity() - $Cart->getQuantity();
                } else {
                    $isDeliveryFree[$Cart->getCartKey()] = true;
                }
            }

            if ($this->baseInfo->getDeliveryFreeAmount()) {
                if (!$isDeliveryFree[$Cart->getCartKey()] && $this->baseInfo->getDeliveryFreeAmount() <= $Cart->getTotalPrice()) {
                    $isDeliveryFree[$Cart->getCartKey()] = true;
                } else {
                    $least[$Cart->getCartKey()] = $this->baseInfo->getDeliveryFreeAmount() - $Cart->getTotalPrice();
                }
            }

            $totalPrice += $Cart->getTotalPrice();
            $totalQuantity += $Cart->getQuantity();
        }

        // カートが分割された時のセッション情報を削除
        $request->getSession()->remove(OrderHelper::SESSION_CART_DIVIDE_FLAG);
        $myCart = $this->cartService->getCarts(true);

        //Mapping cart product with mst product
        $comSer = new MyCommonService($this->entityManager);
        $cartList = [];
        $oneCartId = 0;
        $onecart_key = '';

        foreach ($myCart as $cartT) {
            $cartList[] = $cartT['id'];
            $oneCartId = $cartT['id'];
            $onecart_key = $cartT['cart_key'];
        }

        $mstProduct = $comSer->getMstProductsFromCart($cartList);
        $hsMstProductPrice = [];
        $hsProductId = [];
        $arProductCode = [];
        $hsMstProductCodeCheckShow = [];

        foreach ($mstProduct as $itemP) {
            //car_quantity,a.product_id as my_product_id
            $hsProductId[$itemP['ec_product_id']] = $itemP;
            $arProductCode[] = $itemP['product_code'];
            $hsMstProductCodeCheckShow[$itemP['product_code']] = 'standar_price';
        }
        //end mapping

        $isHideNext = false;
        if ($this->getUser()) {
            $commonS = new MyCommonService($this->entityManager);
            $login_type = $this->globalService->getLoginType();
            $login_code = $this->globalService->getLoginCode();
            $customer_id = $this->globalService->customerId();
            $customer_code = $commonS->getMstCustomer($customer_id)['customer_code'] ?? '';

            if ($customer_code == '6000') {
                $isHideNext = true;
            }

            if (count($arProductCode) > 0) {
                $hsMstProductCodeCheckShow = $commonS->setCartIndtPrice($hsMstProductCodeCheckShow, $commonS, $customer_code, $login_type, $login_code);
            }
        }

        return [
            'totalPrice' => $totalPrice,
            'isHideNext' => $isHideNext,
            'totalQuantity' => $totalQuantity,
            // 空のカートを削除し取得し直す
            'Carts' => $myCart,
            'least' => $least,
            'onecart_key' => $onecart_key,
            'oneCartId' => $oneCartId,
            'quantity' => $quantity,
            'hsProductId' => $hsProductId,
            'hsMstProductCodeCheckShow' => $hsMstProductCodeCheckShow,
            'is_delivery_free' => $isDeliveryFree,
        ];
    }

    /**
     * カートに追加.
     *
     * @Route("/cart/update_cart", name="update_cart",methods={"POST"})
     */
    public function upCart(Request $request)
    {
        $productClassId = $request->get('productClassId');
        $ProductClass = $this->productClassRepository->find($productClassId);
        $myQuantity = $request->get('quantity');
        $oneCartId = $request->get('oneCartId');
        $product_id = $ProductClass->getProduct()->getId();
        setcookie($ProductClass->getProduct()->getId(), $myQuantity, 0, '/');

        $msg = $this->cartService->updateProductCustomize($ProductClass, $myQuantity, $oneCartId, $productClassId);

        return $this->json([
            'is_ok' => 1,
            'msg' => $msg,
        ], 200);
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
}
