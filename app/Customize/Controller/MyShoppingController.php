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
use Customize\Entity\MoreOrder;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Customize\Service\MailService;
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractShoppingController;
use Eccube\Entity\Order;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\ShoppingException;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Form\Type\Shopping\OrderType;
use Eccube\Repository\OrderRepository;
use Eccube\Service\CartService;
use Eccube\Service\OrderHelper;
use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MyShoppingController extends AbstractShoppingController
{
    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var GlobalService
     */
    protected $globalService;

    public function __construct(
        CartService $cartService,
        MailService $mailService,
        OrderRepository $orderRepository,
        OrderHelper $orderHelper,
        GlobalService $globalService
    ) {
        $this->cartService = $cartService;
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->globalService = $globalService;
    }

    /**
     * ログイン画面.
     *
     * @Route("/shopping/login", name="shopping_login", methods={"GET"})
     * @Template("Shopping/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('shopping');
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory->createNamedBuilder('', CustomerLoginType::class);

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer) {
                $builder->get('login_email')->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();
        $this->session->set('is_update_cart', 1);

        return [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }

    /**
     * 注文手続き画面を表示する
     *
     * 未ログインまたはRememberMeログインの場合はログイン画面に遷移させる.
     * ただし、非会員でお客様情報を入力済の場合は遷移させない.
     *
     * カート情報から受注データを生成し, `pre_order_id`でカートと受注の紐付けを行う.
     * 既に受注が生成されている場合(pre_order_idで取得できる場合)は, 受注の生成を行わずに画面を表示する.
     *
     * purchaseFlowの集計処理実行後, warningがある場合はカートど同期をとるため, カートのPurchaseFlowを実行する.
     *
     * @Route("/shopping", name="shopping", methods={"GET"})
     * @Template("Shopping/index.twig")
     */
    public function index(PurchaseFlow $cartPurchaseFlow)
    {
        // ログイン状態のチェック.
        $commonService  = new MyCommonService($this->entityManager);

        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文手続] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');
            $this->session->set('is_update_cart', 1);

            return $this->redirectToRoute('shopping_login');
        }

        // カートチェック.
        $Cart           = $this->cartService->getCart();
        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            log_info('[注文手続] カートが購入フローへ遷移できない状態のため, カート画面に遷移します.');

            return $this->redirectToRoute('cart');
        }

        // 受注の初期化.
        log_info('[注文手続] 受注の初期化処理を開始します.');
        $Customer           = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
        $customer_id        = $this->globalService->customerId();
        $arCusLogin         = $commonService->getMstCustomer($customer_id);
        $is_update_cart     = $this->session->get('is_update_cart', '');

        //************** update cart when login
        $arCarItemId    = [];
        if ($is_update_cart == 1) {
            $cartId                     = $Cart->getId();
            $productCart                = $commonService->getdtPriceFromCart([$cartId], $arCusLogin['customer_code']);
            $arPCodeTankaNumber         = $commonService->getPriceFromDtPriceOfCusV2($arCusLogin['customer_code']);
            $arPCode                    = $arPCodeTankaNumber[0];
            $arTanaka                   = $arPCodeTankaNumber[1];
            $hsHsProductCodeIndtPrice   = [];
            $hsTanaka                   = [];

            foreach ($arPCode as $hasKey) {
                $hsHsProductCodeIndtPrice[$hasKey]  = 1;
            }

            foreach ($arTanaka as $hasKey) {
                $hsTanaka[$hasKey]                  = 1;
            }

            $hsPriceUp                              = [];
            foreach ($productCart as $itemCart) {
                if ($itemCart['price_s01'] != null && ($itemCart['price_s01'] != '')) {
                    $isPro                          = isset($hsHsProductCodeIndtPrice[$itemCart['product_code']]);
                    $isTana                         = isset($hsTanaka[$itemCart['tanka_number']]);

                    if ($isPro && $isTana) {
                        $hsPriceUp[$itemCart['id']] = $itemCart['price_s01'];
                        $arCarItemId[]              = $itemCart['id'];
                    }
                }
            }

            $commonService->updateCartItem($hsPriceUp, $arCarItemId, $Cart);
            $this->session->set('is_update_cart', 0);
        }
        //*****************

        $Order                      = $this->orderHelper->initializeOrder($Cart, $Customer);
        $mstShip                    = $commonService->getMstShippingCustomer($this->globalService->getLoginType(), $this->globalService->customerId());
        $Order->arCusLogin          = $arCusLogin;
        $login_type                 = $this->globalService->getLoginType();
        $login_code                 = $this->globalService->getLoginCode();
        $dtBillSeikyuCode           = $commonService->getCustomerBillSeikyuCode($Customer->getCustomerCode(), $login_type, $login_code);
        $Order->mstShips            = $mstShip;
        $Order->dtBillSeikyuCode    = $dtBillSeikyuCode;

        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult                 = $this->executePurchaseFlow($Order, false);

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        if (!empty($_SESSION['previous_pre_order_id'] ?? '')) {
            //Get more order previous and update new pre_order_id
            $moreOrderPrevious                      = $commonService->getMoreOrder($_SESSION['previous_pre_order_id']);
            $moreOrderPrevious->setPreOrderId($Order->getPreOrderId());
            $moreOrderPrevious->setOtodokeCode('');
            $this->entityManager->persist($moreOrderPrevious);
            $this->entityManager->flush();

            $_SESSION['previous_pre_order_id']      = null;
        }

        $moreOrder                                  = $commonService->getMoreOrder($Order->getPreOrderId());
        $shipping_no_checked                        = '';
        $seikyu_code_checked                        = '';

            if (!MyCommon::isEmptyOrNull($moreOrder)) {
            //Nếu $moreOrder not empty => pre_order_id có tồn tại. Nạp Sesssion
            $_SESSION['s_pre_order_id']             = $Order->getPreOrderId() ?? '';

            if (MyCommon::isEmptyOrNull($moreOrder['date_want_delivery'])) {
                $moreOrder['date_want_delivery']    = '';
            }

            if (MyCommon::isEmptyOrNull($moreOrder['date_want_delivery'])) {
                $moreOrder['date_want_delivery']    = '';
            }

            if (MyCommon::isEmptyOrNull($moreOrder['seikyu_code'])) {
                $moreOrder['seikyu_code']           = '';
            }

            if (MyCommon::isEmptyOrNull($moreOrder['shipping_code'])) {
                $moreOrder['shipping_code']         = '';
            }

            if (MyCommon::isEmptyOrNull($moreOrder['otodoke_code'])) {
                $moreOrder['otodoke_code']          = '';
            }

            if (MyCommon::isEmptyOrNull($moreOrder['remarks1'])) {
                $moreOrder['remarks1']              = null;
            }

            if (MyCommon::isEmptyOrNull($moreOrder['remarks2'])) {
                $moreOrder['remarks2']              = null;
            }

            if (MyCommon::isEmptyOrNull($moreOrder['remarks3'])) {
                $moreOrder['remarks3']              = null;
            }

            if (MyCommon::isEmptyOrNull($moreOrder['remarks4'])) {
                $moreOrder['remarks4']              = null;
            }

            $moreOrder->setShippingCode($this->globalService->getShippingCode() ?? '');
            $moreOrder->setOtodokeCode($this->globalService->getOtodokeCode() ?? '');
            $this->entityManager->persist($moreOrder);
            $this->entityManager->flush();
        }

        if (!MyCommon::isEmptyOrNull($moreOrder)) {
            $Order->moreOrder                       = $moreOrder;
            $Order->hasMoreOrder                    = 1;

            foreach ($mstShip as $mS) {
                if ($mS['shipping_no'] == $moreOrder['shipping_code']) {
                    $shipping_no_checked            = $mS['shipping_no'];
                }
            }

        }

        else {
            $Order->hasMoreOrder                    = 0;

            //Nếu pre_order_id có tồn tại.
            if (!empty($Order->getPreOrderId())) {
                //Nạp Sesssion
                $_SESSION['s_pre_order_id']         = $Order->getPreOrderId() ?? '';

                $orderItem                          = new MoreOrder();
                $orderItem['shipping_code']         = $this->globalService->getShippingCode();
                $orderItem['otodoke_code']          = $this->globalService->getOtodokeCode();
                $orderItem->setPreOrderId($Order->getPreOrderId());
                $orderItem->setShippingCode($this->globalService->getShippingCode());
                $orderItem->setOtodokeCode($this->globalService->getOtodokeCode());
                $this->entityManager->persist($orderItem);
                $this->entityManager->flush();

                $Order->moreOrder                   = $orderItem;

                foreach ($mstShip as $mS) {
                    if ($mS['shipping_no'] == $orderItem['shipping_code']) {
                        $shipping_no_checked        = $mS['shipping_no'];
                    }
                }
            }
        }

        $Order->shipping_no_checked                 = $shipping_no_checked;
        $Order->seikyu_code_checked                 = isset($moreOrder['seikyu_code']) ?? '';
        $Order->rate                                = $commonService->getTaxInfo()['tax_rate'];
        $Order->setTax((float) $Order->getTotal() / (float) $Order->rate);
        $Order->setPaymentTotal((int) $Order->getTotal() + (int) ((float) $Order->getTotal() / (float) $Order->rate));

        $this->entityManager->persist($Order);
        $this->entityManager->flush();

        $form                                       = $this->createForm(OrderType::class, $Order);
        //show quantity
        $myCart                                     = $this->cartService->getCarts(true);
        //Mapping cart product with mst product
        $comSer                                     = new MyCommonService($this->entityManager);
        $cartList                                   = [];

        foreach ($myCart as $cartT) {
            $cartList[]                             = $cartT['id'];
        }

        $customer_id                                = $this->globalService->customerId();
        $customer_code                              = $comSer->getMstCustomer($customer_id)["customer_code"] ?? "";
        $mstProduct                                 = $comSer->getMstProductsFromCart($cartList);
        $hsProductId                                = [];
        $hsMstProductCodeCheckShow                  = [];
        $arProductCode                              = [];

        foreach ($mstProduct as $itemP) {
            $hsProductId[$itemP['ec_product_id']]   = $itemP;
            $arProductCode[]                        = $itemP['product_code'];
            $hsMstProductCodeCheckShow[$itemP['product_code']] = "standar_price";
        }

        $hsMstProductCodeCheckShow                  = $comSer->setCartIndtPrice($hsMstProductCodeCheckShow, $comSer, $customer_code, $login_type, $login_code);

        return [
            'form'                                  => $form->createView(),
            'Order'                                 => $Order,
            'hsProductId'                           => $hsProductId,
            'hsMstProductCodeCheckShow'             => $hsMstProductCodeCheckShow
        ];
    }

    /**
     * 注文確認画面を表示する.
     *
     * ここではPaymentMethod::verifyがコールされます.
     * PaymentMethod::verifyではクレジットカードの有効性チェック等, 注文手続きを進められるかどうかのチェック処理を行う事を想定しています.
     * PaymentMethod::verifyでエラーが発生した場合は, 注文手続き画面へリダイレクトします.
     *
     * @Route("/shopping/confirm", name="shopping_confirm", methods={"POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function confirm(Request $request)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文確認] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId         = $this->cartService->getPreOrderId();
        $Order              = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);

        if (!$Order) {
            log_info('[注文確認] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        $form = $this->createForm(OrderType::class, $Order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[注文確認] 集計処理を開始します.', [$Order->getId()]);
            $response           = $this->executePurchaseFlow($Order);
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            log_info('[注文確認] PaymentMethod::verifyを実行します.', [$Order->getPayment()->getMethodClass()]);
            $paymentMethod      = $this->createPaymentMethod($Order, $form);
            $PaymentResult      = $paymentMethod->verify();

            if ($PaymentResult) {
                if (!$PaymentResult->isSuccess()) {
                    $this->entityManager->rollback();
                    foreach ($PaymentResult->getErrors() as $error) {
                        $this->addError($error);
                    }

                    log_info('[注文確認] PaymentMethod::verifyのエラーのため, 注文手続き画面へ遷移します.', [$PaymentResult->getErrors()]);

                    return $this->redirectToRoute('shopping');
                }

                $response       = $PaymentResult->getResponse();
                if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                    $this->entityManager->flush();

                    log_info('[注文確認] PaymentMethod::verifyが指定したレスポンスを表示します.');

                    return $response;
                }
            }

            $this->entityManager->flush();

            log_info('[注文確認] 注文確認画面を表示します.');
            //nvtrong start
            $commonService      = new MyCommonService($this->entityManager);
            $rate               = $commonService->getTaxInfo()['tax_rate'];
            $tax                = (float) $Order->getTotal() / (float) $rate;
            $paymentTotal       = (int) $Order->getTotal() + (int) ((float) $Order->getTotal() / (float) $rate);
            $moreOrder          = $commonService->getMoreOrder($Order->getPreOrderId());

            //add default day delivery
            if ($moreOrder->getDateWantDelivery() == null || $moreOrder->getDateWantDelivery() == "") {
                $comS                   = new MyCommonService($this->entityManager);
                $arrDayOff              = $comS->getDayOff();
                $dayOffSatSun           = MyCommon::getDayWeekend();
                $arrDayOff              = array_merge($arrDayOff,$dayOffSatSun);
                $newDate                =  MyCommon::get3DayAfterDayOff($arrDayOff);

                $moreOrder->setDateWantDelivery($newDate);
                $this->entityManager->persist($moreOrder);
                $this->entityManager->flush();
            }
            $login_type                 = $this->globalService->getLoginType();
            $login_code                 = $this->globalService->getLoginCode();
            $customer_id                = $this->globalService->customerId();
            $mstShip                    = $commonService->getMstShippingCustomer($login_type, $customer_id, $moreOrder);
            $Customer                   = $commonService->getMstCustomer($customer_id);
            $dtBillSeikyuCode           = $commonService->getCustomerBillSeikyuCode($Customer['customer_code'] ?? "", $login_type, $login_code);

            // Update more_order.seikyu_code
            if (!empty($dtBillSeikyuCode)) {
                $moreOrder->setSeikyuCode($dtBillSeikyuCode[0]['seikyu_code'] ?? "");
                $this->entityManager->persist($moreOrder);
                $this->entityManager->flush();
            }

            $arCusLogin                 = $commonService->getMstCustomer($customer_id);
            $Order->arCusLogin          = $arCusLogin;
            $arrOtoProductOrder         = $commonService->getCustomerOtodoke($login_type, $customer_id, $moreOrder->getShippingCode(), $moreOrder);
            $Order->MoreOrder           = $moreOrder;
            $Order->mstShips            = $mstShip;
            $Order->dtBillSeikyuCode    = $dtBillSeikyuCode;
            $Order->dtCustomerOtodoke   = $arrOtoProductOrder;
            $Order->rate                = $rate;

            $Order->setTax($tax);
            $Order->setPaymentTotal($paymentTotal);

            // Update order_no
            $commonService->updateOrderNo($Order->getId(), $paymentTotal);

            log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);
            //nvtrong end
            //show quantity
            $myCart                 = $this->cartService->getCarts(true);
            //Mapping cart product with mst product
            $comSer                 = new MyCommonService($this->entityManager);
            $cartList               = [];

            foreach ($myCart as $cartT) {
                $cartList[]         = $cartT['id'];
            }

            $mstProduct                 = $comSer->getMstProductsFromCart($cartList);
            $hsProductId                = [];
            $hsMstProductCodeCheckShow  = [];
            $arProductCode              = [];

            foreach ($mstProduct as $itemP) {
                $hsProductId[$itemP['ec_product_id']] = $itemP;
                $arProductCode[]                                    = $itemP['product_code'];
                $hsMstProductCodeCheckShow[$itemP['product_code']]  = "standar_price";
            }

            $customer_id                    = $this->globalService->customerId();
            $customer_code                  = $comSer->getMstCustomer($customer_id)["customer_code"] ?? "";
            $hsMstProductCodeCheckShow      = $comSer->setCartIndtPrice($hsMstProductCodeCheckShow, $comSer, $customer_code, $login_type, $login_code);

            return [
                'form'          => $form->createView(),
                'Order'         => $Order,'hsProductId'=>$hsProductId,'hsMstProductCodeCheckShow'=>$hsMstProductCodeCheckShow
            ];
        }

        log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);

        // FIXME @Templateの差し替え.
        $request->attributes->set('_template', new Template(['template' => 'Shopping/index.twig']));
        //show quantity
        $myCart             = $this->cartService->getCarts(true);
        //Mapping cart product with mst product
        $comSer             = new MyCommonService($this->entityManager);
        $cartList           = [];

        foreach ($myCart as $cartT) {
            $cartList[]     = $cartT['id'];
        }

        $mstProduct                     = $comSer->getMstProductsFromCart($cartList);
        $hsProductId                    = [];
        $hsMstProductCodeCheckShow      = [];

        foreach ($mstProduct as $itemP) {
            $hsProductId[$itemP['ec_product_id']]               = $itemP;
            $hsMstProductCodeCheckShow[$itemP['product_code']]  = "standar_price";
        }

        return [
            'form'                      => $form->createView(),
            'Order'                     => $Order,
            'hsProductId'               => $hsProductId,
            'hsMstProductCodeCheckShow' => $hsMstProductCodeCheckShow,
        ];
    }

    /**
     * PaymentMethodをコンテナから取得する.
     *
     * @param Order $Order
     * @param FormInterface $form
     *
     * @return PaymentMethodInterface
     */
    private function createPaymentMethod(Order $Order, FormInterface $form)
    {
        $PaymentMethod = $this->container->get($Order->getPayment()->getMethodClass());
        $PaymentMethod->setOrder($Order);
        $PaymentMethod->setFormType($form);

        return $PaymentMethod;
    }

    /**
     * 注文処理を行う.
     *
     * 決済プラグインによる決済処理および注文の確定処理を行います.
     *
     * @Route("/shopping/checkout", name="shopping_checkout", methods={"POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function checkout(Request $request)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文処理] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');
            $this->session->set('is_update_cart', 1);

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId         = $this->cartService->getPreOrderId();
        $Order              = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        $login_type         = $this->globalService->getLoginType();
        $login_code         = $this->globalService->getLoginCode();

        if (!$Order) {
            log_info('[注文処理] 購入処理中の受注が存在しません.', [$preOrderId]);
            return $this->redirectToRoute('shopping_error');
        }

        // フォームの生成.
        $form               = $this->createForm(OrderType::class, $Order, [
            // 確認画面から注文処理へ遷移する場合は, Orderエンティティで値を引き回すためフォーム項目の定義をスキップする.
            'skip_add_form' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[注文処理] 注文処理を開始します.', [$Order->getId()]);

            try {
                /*
                 * 集計処理
                 */
                log_info('[注文処理] 集計処理を開始します.', [$Order->getId()]);
                $response           = $this->executePurchaseFlow($Order);
                $this->entityManager->flush();

                if ($response) {
                    return $response;
                }

                log_info('[注文処理] PaymentMethodを取得します.', [$Order->getPayment()->getMethodClass()]);
                $paymentMethod      = $this->createPaymentMethod($Order, $form);

                /*
                 * 決済実行(前処理)
                 */
                log_info('[注文処理] PaymentMethod::applyを実行します.');
                if ($response       = $this->executeApply($paymentMethod)) {
                    return $response;
                }

                /*
                 * 決済実行
                 *
                 * PaymentMethod::checkoutでは決済処理が行われ, 正常に処理出来た場合はPurchaseFlow::commitがコールされます.
                 */
                log_info('[注文処理] PaymentMethod::checkoutを実行します.');
                if ($response       = $this->executeCheckout($paymentMethod)) {
                    return $response;
                }

                $this->entityManager->flush();

                //save more nvtrong
                $comS                       = new MyCommonService($this->entityManager);
                $orderNo                    = $Order->getOrderNo();
                $itemList                   = $Order->getItems()->toArray();
                $arEcLData                  = [];
                $hsArrEcProductCusProduct   = [];
                $arMstProduct               = $comS->getMstProductsOrderNo($orderNo);
                $hsArrRemmain               = [];
                $hsArrJanCode               = [];
                $hsArrProductQuantity       = [];

                foreach ($arMstProduct as $itemPro) {

                    $hsArrEcProductCusProduct[$itemPro['ec_order_lineno']]      = $itemPro['product_code'];
                    $hsArrRemmain[$itemPro['ec_order_lineno']]                  = $itemPro['quantity'];
                    $hsArrJanCode[$itemPro['ec_order_lineno']]                  = $itemPro['jan_code'];
                    $hsArrProductQuantity[$itemPro['ec_order_lineno']]          = $itemPro['product_quantity'];

                    if (isset($_COOKIE[$itemPro['product_id']])) {
                        unset($_COOKIE[$itemPro['product_id']]);
                        setcookie($itemPro['product_id'], null, -1, '/');
                    }
                }

                //customer_code
                $customer_id                    = $this->globalService->customerId();
                $oneCustomer                    = $comS->getMstCustomer($customer_id);
                $customerCode                   = $oneCustomer['customer_code'] ?? "";
                $moreOrder                      = $comS->getMoreOrder($Order->getPreOrderId());
                $ship_code                      = $moreOrder->getShippingCode();
                $seikyu_code                    = $moreOrder->getSeikyuCode();
                $shipping_plan_date             = $moreOrder->getDateWantDelivery();
                $otodoke_code                   = $moreOrder->getOtodokeCode();
                $remarks1                       = $moreOrder->getRemarks1();
                $remarks2                       = $moreOrder->getRemarks2();
                $remarks3                       = $moreOrder->getRemarks3();
                $remarks4                       = $moreOrder->getRemarks4();
                $location                       = $comS->getCustomerLocation($customerCode);
                $reCustomer                     = $comS->getCustomerRelationFromUser($customerCode, $login_type, $login_code);

                foreach ($itemList as $itemOr) {
                    if ($itemOr->isProduct()) {
                        $arEcLData[]                = [
                            'ec_order_no'           => $orderNo,
                            'ec_order_lineno'       => $itemOr->getId(),
                            'product_code'          => $hsArrEcProductCusProduct[$itemOr->getId()],
                            'customer_code'         => $reCustomer['customer_code'] ?? '',
                            'shipping_code'         => $ship_code,
                            'order_remain_num'      => $hsArrRemmain[$itemOr->getId()],
                            'shipping_plan_date'    => $shipping_plan_date,
                            'seikyu_code'           => $seikyu_code,
                            'order_price'           => $itemOr->getPrice(),
                            'demand_quantity'       => $itemOr->getQuantity(),
                            'otodoke_code'          => $otodoke_code,
                            'deli_plan_date'        => $shipping_plan_date,
                            'item_no'               => $hsArrJanCode[$itemOr->getId()],
                            'demand_unit'           => $hsArrProductQuantity[$itemOr->getId()] > 1 ? 'CS' : 'PC',
                            'dyna_model_seg2'       => $orderNo,
                            'dyna_model_seg3'       => 2,
                            'dyna_model_seg4'       => $orderNo,
                            'dyna_model_seg5'       => count($itemList),
                            'remarks1'              => $remarks1,
                            'remarks2'              => $remarks2,
                            'remarks3'              => $remarks3,
                            'remarks4'              => $remarks4,
                            'location'              => !empty($location) ? $location : "XB0201001",
                            ];
                    }
                }

                log_info('[saveOrderStatussaveOrderStatussaveOrderStatus', $arEcLData);
                $comS->saveOrderStatus($arEcLData);

               //$comS->saveOrderShiping($arEcLData);
                $comS->savedtOrder($arEcLData);

                log_info('[注文処理] 注文処理が完了しました.', [$Order->getId()]);

            }

            catch (ShoppingException $e) {
                log_error('[注文処理] 購入エラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();
                $this->addError($e->getMessage());
                return $this->redirectToRoute('shopping_error');

            }

            catch (\Exception $e) {
                log_error('[注文処理] 予期しないエラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();
                $this->addError('front.shopping.system_error');
                return $this->redirectToRoute('shopping_error');
            }

            // カート削除
            log_info('[注文処理] カートをクリアします.', [$Order->getId()]);
            $this->cartService->clear();

            // 受注IDをセッションにセット
            $this->session->set(OrderHelper::SESSION_ORDER_ID, $Order->getId());
            $commonService              = new MyCommonService($this->entityManager);
            $rate                       = $commonService->getTaxInfo()['tax_rate'];
            $tax                        = (float) $Order->getTotal() / (float) $rate;
            $paymentTotal               = (float) $Order->getTotal() + ((float) $Order->getTotal() / (float) $rate);

            $commonService->updateOrderNo($Order->getId(), $paymentTotal);
            $Order->setTax($tax);
            $Order->setPaymentTotal($paymentTotal);

            // Save info into Session to Send Mail
            $_SESSION["usc_" . $this->globalService->customerId()]['send_mail'] = [
                'order_id'              => $Order->getId(),
                'pre_order_id'          => $Order->getPreOrderId(),
            ];

            $this->entityManager->flush();

            log_info('[注文処理] 注文処理が完了しました. 購入完了画面へ遷移します.', [$Order->getId()]);
            $this->session->set('is_update_cart', 0);

            return $this->redirectToRoute('shopping_complete');
        }

        log_info('[注文処理] フォームエラーのため, 購入エラー画面へ遷移します.', [$Order->getId()]);

        return $this->redirectToRoute('shopping_error');
    }

    /**
     * PaymentMethod::applyを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function executeApply(PaymentMethodInterface $paymentMethod)
    {
        $dispatcher = $paymentMethod->apply(); // 決済処理中.

        // リンク式決済のように他のサイトへ遷移する場合などは, dispatcherに処理を移譲する.
        if ($dispatcher instanceof PaymentDispatcher) {
            $response = $dispatcher->getResponse();
            $this->entityManager->flush();

            // dispatcherがresponseを保持している場合はresponseを返す
            if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                log_info('[注文処理] PaymentMethod::applyが指定したレスポンスを表示します.');

                return $response;
            }

            // forwardすることも可能.
            if ($dispatcher->isForward()) {
                log_info('[注文処理] PaymentMethod::applyによりForwardします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->forwardToRoute($dispatcher->getRoute(), $dispatcher->getPathParameters(),
                    $dispatcher->getQueryParameters());
            } else {
                log_info('[注文処理] PaymentMethod::applyによりリダイレクトします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->redirectToRoute($dispatcher->getRoute(),
                    array_merge($dispatcher->getPathParameters(), $dispatcher->getQueryParameters()));
            }
        }
    }

    /**
     * PaymentMethod::checkoutを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     */
    protected function executeCheckout(PaymentMethodInterface $paymentMethod)
    {
        $PaymentResult = $paymentMethod->checkout();
        $response = $PaymentResult->getResponse();
        // PaymentResultがresponseを保持している場合はresponseを返す
        if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
            $this->entityManager->flush();
            log_info('[注文処理] PaymentMethod::checkoutが指定したレスポンスを表示します.');

            return $response;
        }

        // エラー時はロールバックして購入エラーとする.
        if (!$PaymentResult->isSuccess()) {
            $this->entityManager->rollback();
            foreach ($PaymentResult->getErrors() as $error) {
                $this->addError($error);
            }

            log_info('[注文処理] PaymentMethod::checkoutのエラーのため, 購入エラー画面へ遷移します.', [$PaymentResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        return null;
    }

    /**
     * get Otodoke Option.
     *
     * @Route("/shopping/otodoke", name="otodoke_option", methods={"POST"})
     * @Template("Block/leftMenu.twig")
     */
    public function getOtodokeOption (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $customer_id        = $request->get('customer_id', '');
                $shipping_code      = $request->get('shipping_code', '');
                $otodokeOpt         = $this->globalService->otodokeOption($customer_id, $shipping_code);

                $result             = [
                    'status'        => 1,
                    'data'          => $otodokeOpt ?? [],
                ];

                return $this->json($result, 200);
            }

            $result             = [
                'status'        => 0,
                'data'          => [],
            ];
            return $this->json($result, 400);

        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Change Shipping Code.
     *
     * @Route("/shopping/shipping/change", name="shipping_code_change", methods={"POST"})
     * @Template("Block/leftMenu.twig")
     */
    public function changeShippingCode (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $shipping_code                  = $request->get('shipping_code', '');

                //Nạp lại session shipping_code và otodoke_code
                $_SESSION['s_shipping_code']    = $shipping_code;
                $_SESSION['s_otodoke_code']     = '';

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => 0], 400);

        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Change Otodoke Code.
     *
     * @Route("/shopping/otodoke/change", name="otodoke_code_change", methods={"POST"})
     * @Template("Block/leftMenu.twig")
     */
    public function changeOtodokeCode (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $otodoke_code                   = $request->get('otodoke_code', '');

                //Nạp lại session otodoke_code
                $_SESSION['s_otodoke_code']     = $otodoke_code;

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => 0], 400);

        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Send mail order.
     *
     * @Route("/shopping/send-mail", name="shopping_send_mail", methods={"POST"})
     */
    public function sendMailOrder (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $customer_id            = $this->globalService->customerId();

                if (!empty($_SESSION["usc_" . $customer_id]) && !empty($_SESSION["usc_" . $customer_id]['send_mail'])) {
                    $pre_order_id       = $_SESSION["usc_" . $customer_id]['send_mail']['pre_order_id'] ?? "";
                    $order_id           = $_SESSION["usc_" . $customer_id]['send_mail']['order_id'] ?? "";

                    if (empty($pre_order_id) || empty($order_id)) return;

                    $commonService      = new MyCommonService($this->entityManager);
                    $Order              = $this->orderHelper->getPurchaseCompletedOrder($pre_order_id);

                    log_info('[注文処理] 注文メールの送信を行います.', [$order_id]);

                    $newOrder                           = null;
                    $customer_id                        = $this->globalService->customerId();
                    $customer                           = $commonService->getMstCustomer($customer_id);

                    /* Get infomation for case Supper user*/
                    $root_customer_id                   = $this->getUser()->getId();
                    $customer2                          = $commonService->getMstCustomer($root_customer_id);
                    $emailcc                            = "";

                    if (
                        !empty($customer2['customer_email']) &&
                        !empty($customer['customer_email']) &&
                        $customer2['customer_email'] != $customer['customer_email']
                    ) {
                        $emailcc                        = $customer2['customer_email'];
                    }
                    /* End */

                    $newOrder['name']                   = $customer['name01'] ?? "";
                    $newOrder['subtotal']               = $Order['subtotal'];
                    $newOrder['charge']                 = $Order['charge'];
                    $newOrder['discount']               = $Order['discount'];
                    $newOrder['delivery_fee_total']     = $Order['delivery_fee_total'];
                    $newOrder['tax']                    = $Order['tax'];
                    $newOrder['total']                  = $Order['total'];
                    $newOrder['payment_total']          = $Order['payment_total'];
                    $newOrder['rate']                   = $commonService->getTaxInfo()['tax_rate'];
                    $newOrder['company_name']           = $customer['company_name'] ?? "";
                    $newOrder['postal_code']            = $customer['postal_code'] ?? "";
                    $newOrder['addr01']                 = $customer['addr01'] ?? "";
                    $newOrder['addr02']                 = $customer['addr02'] ?? "";
                    $newOrder['addr03']                 = $customer['addr03'] ?? "";
                    $newOrder['phone_number']           = $customer['phone_number'] ?? "";
                    $newOrder['email']                  = $customer['customer_email'] ?? "";
                    $newOrder['emailcc']                = $emailcc;
                    $goods                              = $commonService->getMstProductsOrderCustomer($order_id);
                    $newOrder['ProductOrderItems']      = $goods;
                    $newOrder['tax']                    = $newOrder['subtotal'] / $newOrder['rate'];
                    $shipping                           = $commonService->getMoreOrderCustomer($pre_order_id);
                    $newOrder['Shipping']               = $shipping;

                    $Order->setName01($customer['name01']);
                    $Order->setCompanyName( $customer['company_name']);
                    $this->mailService->sendOrderMail($newOrder, $Order);
                    $this->entityManager->flush();

                    $_SESSION["usc_" . $customer_id]['send_mail'] = null;

                    return $this->json(['status' => 1, 'msg' => "OK"], 200);
                }
            }

            return $this->json(['status' => 1, 'msg' => ""], 200);

        } catch (\Exception $e) {
            return $this->json(['status' => 0, 'msg' => $e->getMessage()], 400);
        }
    }
}
