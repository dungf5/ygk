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
use Customize\Service\Common\MyCommonService;
use Eccube\Controller\AbstractShoppingController;
use Eccube\Entity\Order;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\ShoppingException;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Form\Type\Shopping\OrderType;
use Eccube\Repository\OrderRepository;
use Eccube\Service\CartService;
use Customize\Service\MailService;
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

    public function __construct(
        CartService $cartService,
        MailService $mailService,
        OrderRepository $orderRepository,
        OrderHelper $orderHelper
    ) {
        $this->cartService = $cartService;
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
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
        $commonService = new MyCommonService($this->entityManager);
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文手続] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            $this->session->set("is_update_cart",1);
            return $this->redirectToRoute('shopping_login');
        }

        // カートチェック.
        $Cart = $this->cartService->getCart();
        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            log_info('[注文手続] カートが購入フローへ遷移できない状態のため, カート画面に遷移します.');

            return $this->redirectToRoute('cart');
        }



        // 受注の初期化.
        log_info('[注文手続] 受注の初期化処理を開始します.');
        $Customer = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
        $arCusLogin = $commonService->getMstCustomer($Customer->getId());

        $is_update_cart = $this->session->get("is_update_cart","");
        //************** update cart when login
        $arCarItemId =[];
        if($is_update_cart==1){
            $cartId = $Cart->getId();
            $productCart = $commonService->getdtPriceFromCart([$cartId],$arCusLogin["customer_code"]);
            $hsPriceUp =[];
            foreach ($productCart as $itemCart){
                if($itemCart["price_s01"] != null && ($itemCart["price_s01"]!="") ){
                    $hsPriceUp[$itemCart["id"]] = $itemCart["price_s01"];
                    $arCarItemId[] =$itemCart["id"];
                }
            }
            $commonService->updateCartItem($hsPriceUp,$arCarItemId,$Cart);
            $this->session->set("is_update_cart",0);
        }
        //*****************


        $Order = $this->orderHelper->initializeOrder($Cart, $Customer);

        $mstShip = $commonService->getMstShippingCustomer($Customer->getId());


       $Order->arCusLogin =$arCusLogin;
        $dtBillSeikyuCode = $commonService->getCustomerBillSeikyuCode($Customer->getId());

        $Order->mstShips = $mstShip;
        $Order->dtBillSeikyuCode = $dtBillSeikyuCode;
        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult = $this->executePurchaseFlow($Order, false);
//        $this->entityManager->flush();
//
//        if ($flowResult->hasError()) {
//            log_info('[注文手続] Errorが発生したため購入エラー画面へ遷移します.', [$flowResult->getErrors()]);
//
//            return $this->redirectToRoute('shopping_error');
//        }
//
//        if ($flowResult->hasWarning()) {
//            log_info('[注文手続] Warningが発生しました.', [$flowResult->getWarning()]);
//
//            // 受注明細と同期をとるため, CartPurchaseFlowを実行する
//            $cartPurchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
//
//            // 注文フローで取得されるカートの入れ替わりを防止する
//            // @see https://github.com/EC-CUBE/ec-cube/issues/4293
//            $this->cartService->setPrimary($Cart->getCartKey());
//        }

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        $moreOrder = $commonService->getMoreOrder($Order->getPreOrderId());

        $shipping_no_checked = '';
        $seikyu_code_checked ='';

        if (!MyCommon::isEmptyOrNull($moreOrder)) {
            if(MyCommon::isEmptyOrNull($moreOrder["date_want_delivery"])){
                $moreOrder["date_want_delivery"]="";
            }
            if(MyCommon::isEmptyOrNull($moreOrder["date_want_delivery"])){
                $moreOrder["date_want_delivery"]="";
            }

            if(MyCommon::isEmptyOrNull($moreOrder["seikyu_code"])){
                $moreOrder["seikyu_code"]="";
            }
            //shipping_code":"Customize\Entity\MoreOrder":private]=> string(3) "333" ["otodoke_code"
            if(MyCommon::isEmptyOrNull($moreOrder["shipping_code"])){
                $moreOrder["shipping_code"]="";
            }
            if(MyCommon::isEmptyOrNull($moreOrder["otodoke_code"])){
                $moreOrder["otodoke_code"]="";
            }

        }

        if (!MyCommon::isEmptyOrNull($moreOrder)) {
            $Order->moreOrder = $moreOrder;
            $Order->hasMoreOrder = 1;
            foreach ($mstShip as $mS) {

                if ($mS['shipping_no'] == $moreOrder['shipping_code']) {
                    $shipping_no_checked = $mS['shipping_no'];
                }
            }
        } else {
            $Order->hasMoreOrder = 0;
        }
//        if(count($mstShip)==1){
//            $Order->shipping_no_checked = $mstShip[0]['shipping_no'];
//        }else{
//
//        }
        $Order->shipping_no_checked = $shipping_no_checked;

        $Order->seikyu_code_checked = isset($moreOrder['seikyu_code'])??'' ;

        $Order->rate = $commonService->getTaxInfo()['tax_rate'];
        $Order->setPaymentTotal((float)$Order->getTotal() + ((float)$Order->getTotal()/(float)$Order->rate));

        $form = $this->createForm(OrderType::class, $Order);

        return [
            'form' => $form->createView(),
            'Order' => $Order,
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
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);

        if (!$Order) {
            log_info('[注文確認] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        $form = $this->createForm(OrderType::class, $Order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[注文確認] 集計処理を開始します.', [$Order->getId()]);
            $response = $this->executePurchaseFlow($Order);
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            log_info('[注文確認] PaymentMethod::verifyを実行します.', [$Order->getPayment()->getMethodClass()]);
            $paymentMethod = $this->createPaymentMethod($Order, $form);
            $PaymentResult = $paymentMethod->verify();

            if ($PaymentResult) {
                if (!$PaymentResult->isSuccess()) {
                    $this->entityManager->rollback();
                    foreach ($PaymentResult->getErrors() as $error) {
                        $this->addError($error);
                    }

                    log_info('[注文確認] PaymentMethod::verifyのエラーのため, 注文手続き画面へ遷移します.', [$PaymentResult->getErrors()]);

                    return $this->redirectToRoute('shopping');
                }

                $response = $PaymentResult->getResponse();
                if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                    $this->entityManager->flush();

                    log_info('[注文確認] PaymentMethod::verifyが指定したレスポンスを表示します.');

                    return $response;
                }
            }

            $this->entityManager->flush();

            log_info('[注文確認] 注文確認画面を表示します.');
            //nvtrong start
            $Customer = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
            $commonService = new MyCommonService($this->entityManager);
            $rate = $commonService->getTaxInfo()['tax_rate'] ;
            $paymentTotal = (float)$Order->getTotal() + ((float)$Order->getTotal()/(float)$rate);


            $moreOrder = $commonService->getMoreOrder($Order->getPreOrderId());

            $mstShip = $commonService->getMstShippingCustomer($Customer->getId(), $moreOrder);

            $dtBillSeikyuCode = $commonService->getCustomerBillSeikyuCode($Customer->getId(), $moreOrder);
            $arCusLogin = $commonService->getMstCustomer($Customer->getId());
            $Order->arCusLogin =$arCusLogin;
            $arrOtoProductOrder = $commonService->getCustomerOtodoke($Customer->getId(), $moreOrder->getShippingCode(), $moreOrder);
            $Order->MoreOrder = $moreOrder;
            $Order->mstShips = $mstShip;
            $Order->dtBillSeikyuCode = $dtBillSeikyuCode;
            $Order->dtCustomerOtodoke = $arrOtoProductOrder;
            $Order->rate =$rate;

            $Order->setPaymentTotal($paymentTotal);


            // Update order_no
            $commonService->updateOrderNo($Order->getId(),$paymentTotal);

            log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);
            //nvtrong end

            return [
                'form' => $form->createView(),
                'Order' => $Order,
            ];
        }

        log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);

        // FIXME @Templateの差し替え.
        $request->attributes->set('_template', new Template(['template' => 'Shopping/index.twig']));

        return [
            'form' => $form->createView(),
            'Order' => $Order,
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
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文処理] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');
            $this->session->set("is_update_cart",1);
            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[注文処理] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        // フォームの生成.
        $form = $this->createForm(OrderType::class, $Order, [
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
                $response = $this->executePurchaseFlow($Order);
                $this->entityManager->flush();

                if ($response) {
                    return $response;
                }

                log_info('[注文処理] PaymentMethodを取得します.', [$Order->getPayment()->getMethodClass()]);
                $paymentMethod = $this->createPaymentMethod($Order, $form);

                /*
                 * 決済実行(前処理)
                 */
                log_info('[注文処理] PaymentMethod::applyを実行します.');
                if ($response = $this->executeApply($paymentMethod)) {
                    return $response;
                }

                /*
                 * 決済実行
                 *
                 * PaymentMethod::checkoutでは決済処理が行われ, 正常に処理出来た場合はPurchaseFlow::commitがコールされます.
                 */
                log_info('[注文処理] PaymentMethod::checkoutを実行します.');
                if ($response = $this->executeCheckout($paymentMethod)) {
                    return $response;
                }

                $this->entityManager->flush();

                //save more nvtrong
                $comS = new MyCommonService($this->entityManager);

                $orderNo = $Order->getOrderNo();
                $itemList = $Order->getItems()->toArray();
                $arEcLData = [];
                $hsArrEcProductCusProduct=[];
                $arEcProduct =[];
                ///
                $arMstProduct = $comS->getMstProductsOrderNo($orderNo);
                $hsArrRemmain=[];
                foreach ($arMstProduct as $itemPro) {
                    $hsArrEcProductCusProduct[$itemPro["ec_order_lineno"]] = $itemPro["product_code"];
                    $hsArrRemmain[$itemPro["ec_order_lineno"]] = $itemPro["quantity"];
                }
                //customer_code

                $oneCustomer = $comS->getMstCustomer( $Order->getCustomer()->getId());
                $customerCode = $oneCustomer['customer_code'];
                $moreOrder = $comS->getMoreOrder($Order->getPreOrderId());

                $ship_code = $moreOrder->getShippingCode();
                $seikyu_code = $moreOrder->getSeikyuCode();
                $shipping_plan_date = $moreOrder->getDateWantDelivery();
                $otodoke_code = $moreOrder->getOtodokeCode();

                //dd($itemList);
                foreach ($itemList as $itemOr) {
                    if ($itemOr->isProduct()) {
                        $arEcLData[] = ['ec_order_no' => $orderNo,
                            'ec_order_lineno' => $itemOr->getId()
                            ,'product_code'=>$hsArrEcProductCusProduct[$itemOr->getId()]
                            ,'customer_code'=> $customerCode
                            ,'shipping_code'=> $ship_code,
                            'order_remain_num'=>$hsArrRemmain[$itemOr->getId()],
                            'shipping_plan_date'=>$shipping_plan_date,
                            'seikyu_code'=>$seikyu_code,
                            //dtorder
                            'order_price'=>$itemOr->getPrice(),
                            'demand_quantity'=>$itemOr->getQuantity(),
                            'otodoke_code'=>$otodoke_code,
                            ];

                    }
                }
                log_info('[saveOrderStatussaveOrderStatussaveOrderStatus', $arEcLData);
                $comS->saveOrderStatus($arEcLData);

                $comS->saveOrderShiping($arEcLData);
                $comS->savedtOrder($arEcLData);

                log_info('[注文処理] 注文処理が完了しました.', [$Order->getId()]);
            } catch (ShoppingException $e) {
                log_error('[注文処理] 購入エラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();

                $this->addError($e->getMessage());

                return $this->redirectToRoute('shopping_error');
            } catch (\Exception $e) {
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
            $commonService = new MyCommonService($this->entityManager);
            $rate = $commonService->getTaxInfo()['tax_rate'] ;
            $paymentTotal = (float)$Order->getTotal() + ((float)$Order->getTotal()/(float)$rate);
            $commonService->updateOrderNo($Order->getId(),$paymentTotal);
            $Order->setPaymentTotal($paymentTotal);
            // メール送信
            log_info('[注文処理] 注文メールの送信を行います.', [$Order->getId()]);
            // Get info order
            $newOrder = null;
            // Get info customer

            $user = $this->getUser();
            $customer = $commonService->getMstCustomer($user->getId());
            $newOrder['name'] = $customer['name01'];
            // Get info order
            $newOrder['subtotal'] = $Order['subtotal'];
            $newOrder['charge'] = $Order['charge'];
            $newOrder['discount'] = $Order['discount'];
            $newOrder['delivery_fee_total'] = $Order['delivery_fee_total'];
            $newOrder['tax'] = $Order['tax'];
            $newOrder['total'] = $Order['total'];
            $newOrder['payment_total'] = $Order['payment_total'];
            // Get info tax
            $newOrder['rate'] = $commonService->getTaxInfo()['tax_rate'];
            // Get Customer
            $newOrder['company_name'] = $customer['company_name'];
            $newOrder['postal_code'] = $customer['postal_code'];
            $newOrder['addr01'] = $customer['addr01'];
            $newOrder['addr02'] = $customer['addr02'];
            $newOrder['addr03'] = $customer['addr03'];
            $newOrder['phone_number'] = $customer['phone_number'];
            $newOrder['email'] = $customer['email'];
            // Get Product
            $goods = $commonService->getMstProductsOrderCustomer($Order->getId());
            $newOrder['ProductOrderItems'] = $goods;
            // Get Shipping
            //$shipping = $commonService->getMstShippingOrder($user->getId(),$Order->getId());
            $shipping = $commonService->getMoreOrderCustomer($Order->getPreOrderId());
            $newOrder['Shipping'] = $shipping;

            $this->mailService->sendOrderMail($newOrder, $Order);

            $this->entityManager->flush();

            log_info('[注文処理] 注文処理が完了しました. 購入完了画面へ遷移します.', [$Order->getId()]);
            $this->session->set("is_update_cart",0);
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
}
