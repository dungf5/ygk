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
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
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
        MstProductRepository $mstProductRepository
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->cartService = $cartService;
        $this->purchaseFlow = $cartPurchaseFlow;
        $this->baseInfo = $baseInfoRepository->get();
        $this->priceRepository = $priceRepository;
        $this->mstProductRepository = $mstProductRepository;


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
            $is_check_exist  = $request->get('is_check_exist');
            if($is_check_exist==1){

               echo $commonService->checkExistPreOrder($pre_order_id);
                die();
            }

            $commonService->saveTempCart($shipping_code, $pre_order_id);
            $arrOtoProductOrder = $commonService->getCustomerOtodoke($customer_id, $shipping_code);

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
        $result = ['is_ok' => '0', 'msg' => 'NG', 'delivery_code' => ''];
        if ('POST' === $request->getMethod()) {
            $commonService = new MyCommonService($this->entityManager);
            $delivery_code = $request->get('delivery_code');
            $date_want_delivery = $request->get('date_want_delivery');
            $pre_order_id = $request->get('pre_order_id');
            $bill_code = $request->get('bill_code');
            $date_want_delivery = $request->get('date_want_delivery');
            $result = ['is_ok' => '1', 'msg' => 'OK', 'delivery_code' => $delivery_code];

            if (!empty($bill_code)) {
                $commonService->saveTempCartBillSeiky($bill_code, $pre_order_id);
                $result = ['is_ok' => '1', 'msg' => 'OK', 'bill_code' => $bill_code, 'pre_order_id' => $pre_order_id];
            }
            if (!empty($delivery_code)) {
                $commonService->saveTempCartDeliCodeOto($delivery_code, $pre_order_id);
                $result = ['is_ok' => '1', 'msg' => 'OK', 'delivery_code' => $delivery_code, 'pre_order_id' => $pre_order_id];
            }
            if (!MyCommon::isEmptyOrNull($date_want_delivery)) {
                $commonService->saveTempCartDateWantDeli($date_want_delivery, $pre_order_id);
                $result = ['is_ok' => '1', 'msg' => 'OK', 'date_want_delivery' => $date_want_delivery, 'pre_order_id' => $pre_order_id];
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

        foreach ($Carts as $Cart) {
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
        foreach ($myCart as $cartT) {
            $cartList[] = $cartT['id'];
        }
        $mstProduct = $comSer->getMstProductsFromCart($cartList);
        $hsProductId = [];
        foreach ($mstProduct as $itemP) {
            $hsProductId[$itemP['ec_product_id']] = $itemP;
        }
        //end mapping

        $isHideNext =false;
        if ($this->getUser()) {
            $Customer = $this->getUser();
            $commonS = new MyCommonService($this->entityManager);
            $customer_code = $commonS->getMstCustomer($Customer->getId())["customer_code"];
            if($customer_code=="6000"){
                $isHideNext = true;
            }

        }

        return [
            'totalPrice' => $totalPrice,
            'isHideNext'=>$isHideNext,
            'totalQuantity' => $totalQuantity,
            // 空のカートを削除し取得し直す
            'Carts' => $myCart,
            'least' => $least,
            'quantity' => $quantity,
            'hsProductId' => $hsProductId,
            'is_delivery_free' => $isDeliveryFree,
        ];
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
            case 'remove':{
                $this->cartService->removeProductCustomize($ProductClass);
                $idRemove = $ProductClass->getProduct()->getId();
                break;
            }

        }

        if((int)$idRemove>0){
            setcookie($ProductClass->getProduct()->getId(),$mstProduct->getQuantity(),0,"/");
        }

        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            $totalPrice = 0;
            foreach ($Cart['CartItems'] as $CartItem) {
                $totalPrice += $CartItem['price'] * $CartItem['quantity'];
                setcookie($ProductClass->getProduct()->getId(),$CartItem['quantity']*$mstProduct->getQuantity(),0,"/");

            }

            $Cart->setTotalPrice($totalPrice);
            $Cart->setDeliveryFeeTotal(0);
        }
        $this->cartService->saveCustomize();

        // カートを取得して明細の正規化を実行
//        $Carts = $this->cartService->getCarts();
//        $this->execPurchaseFlow($Carts);

        log_info('カート演算処理終了', ['operation' => $operation, 'product_class_id' => $productClassId]);

        return $this->redirectToRoute('cart');
//        $myComS = new MyCommonService($this->entityManager);
//        $totalNew=0;
//        if(count($Carts)>0){
//            $cartId = $Carts[0]->getId();
//            $totalNew = $myComS->getTotalItemCart($cartId);
//        }
    }
}
