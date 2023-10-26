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
use Customize\Entity\DtOrder;
use Customize\Entity\MstCustomer;
use Customize\Repository\MstProductRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Customize\Service\MailService;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractShoppingController;
use Eccube\Entity\Order;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\ShoppingException;
use Eccube\Form\Type\Shopping\OrderType;
use Eccube\Repository\OrderRepository;
use Eccube\Service\CartService;
use Eccube\Service\OrderHelper;
use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseFlowResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @var MstProductRepository
     */
    protected $mstProductRepository;

    public function __construct(
        CartService $cartService,
        MailService $mailService,
        OrderRepository $orderRepository,
        OrderHelper $orderHelper,
        GlobalService $globalService,
        MstProductRepository $mstProductRepository
    ) {
        $this->cartService = $cartService;
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->globalService = $globalService;
        $this->mstProductRepository = $mstProductRepository;
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
     * @Route("/shopping", name="shopping", methods={"GET", "POST"})
     * @Template("Shopping/index.twig")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function index(PurchaseFlow $cartPurchaseFlow)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        $customer_order_no = $this->globalService->getCustomerOrderNo();
        $shipping_code = $this->globalService->getShippingCode();
        $otodoke_code = $this->globalService->getOtodokeCode();
        $remarks1 = $this->globalService->getRemarks1();
        $remarks2 = $this->globalService->getRemarks2();
        $remarks3 = $this->globalService->getRemarks3();
        $remarks4 = $this->globalService->getRemarks4();
        $delivery_date = $this->globalService->getDeliveryDate();

        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文手続] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');
            $this->session->set('is_update_cart', 1);

            return $this->redirectToRoute('mypage_login');
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

        // Override Customer
        if (!empty($Customer)) {
            $MstCustomer = $this->entityManager->getRepository(MstCustomer::class)->findOneBy(['ec_customer_id' => $this->globalService->customerId()]);

            if (!empty($MstCustomer)) {
                $Customer->setCustomerCode($MstCustomer->getCustomerCode() ?? '');
                $Customer->setName01($MstCustomer->getCustomerName() ?? '');
                $Customer->setName02($MstCustomer->getCustomerName() ?? '');
                $Customer->setCompanyName($MstCustomer->getCompanyName() ?? '');
                $Customer->setEmail($MstCustomer->getEmail() ?? '');
                $Customer->setPhoneNumber($MstCustomer->getPhoneNumber() ?? '');
                $Customer->setPostalCode($MstCustomer->getPostalCode() ?? '');
                $Customer->setAddr01($MstCustomer->getAddr01() ?? '');
                $Customer->setAddr02($MstCustomer->getAddr02() ?? '');
                $Customer->setAddr03($MstCustomer->getAddr03() ?? '');
            }
        }

        $this->entityManager->persist($Customer);
        $this->entityManager->flush();

        $Order = $this->orderHelper->initializeOrder($Cart, $Customer);

        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult = $this->executePurchaseFlow($Order, false);
        $this->entityManager->flush();

        if ($flowResult->hasError()) {
            log_info('[注文手続] Errorが発生したため購入エラー画面へ遷移します.', [$flowResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        if ($flowResult->hasWarning()) {
            log_info('[注文手続] Warningが発生しました.', [$flowResult->getWarning()]);

            // 受注明細と同期をとるため, CartPurchaseFlowを実行する
            $cartPurchaseFlow->validate($Cart, new PurchaseContext());
            $this->cartService->save();
        }

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        $form = $this->createForm(OrderType::class, $Order);

        // Adding more information
        $commonService = new MyCommonService($this->entityManager);
        $orderItems = $Order->getProductOrderItems();
        $rate = $commonService->getTaxInfo()['tax_rate'];
        $Order->setTax((float) $Order->getTotal() / (float) $rate);
        $Order->setPaymentTotal((int) $Order->getTotal() + (int) ((float) $Order->getTotal() / (float) $rate));
        $this->entityManager->persist($Order);
        $this->entityManager->flush();

        $shipping_option = $this->globalService->shippingOption();
        if (count($shipping_option) == 1 && isset($shipping_option[0]['shipping_no'])) {
            $shipping_code = $shipping_option[0]['shipping_no'];
        }

        $otodoke_option = $this->globalService->otodokeOption($this->globalService->customerId(), $shipping_code);
        if (count($otodoke_option) == 1 && isset($otodoke_option[0]['otodoke_code'])) {
            $otodoke_code = $otodoke_option[0]['otodoke_code'];
        }

        $Order->shipping_option = $shipping_option;
        $Order->otodoke_option = $otodoke_option;

        foreach ($orderItems as &$order_item) {
            // Get mst_product
            $mstProduct = $this->mstProductRepository->getData($order_item->getProduct()->getId());

            $order_item->setMstProduct($mstProduct);
        }

        return [
            'form' => $form->createView(),
            'Order' => $Order,
            'customer_order_no' => $customer_order_no,
            'shipping_code' => $shipping_code,
            'otodoke_code' => $otodoke_code,
            'remarks1' => $remarks1,
            'remarks2' => $remarks2,
            'remarks3' => $remarks3,
            'remarks4' => $remarks4,
            'delivery_date' => $delivery_date,
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
     *
     * @param Request $request
     *
     * @return array|PurchaseFlowResult|RedirectResponse|Response
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function confirm(Request $request)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        //Request param
        $shipping_code = $this->globalService->getShippingCode();
        $otodoke_code = $request->get('otodoke_code', $this->globalService->getOtodokeCode());
        $customer_order_no = $request->get('customer_order_no', '');
        $remarks1 = $request->get('remarks1', '');
        $remarks2 = $request->get('remarks2', '');
        $remarks3 = $request->get('remarks3', '');
        $remarks4 = $request->get('remarks4', '');
        $delivery_date = $request->get('date_picker_delivery', '');
        $customer_code = $this->globalService->customerCode();
        $login_type = $this->globalService->getLoginType();
        $login_code = $this->globalService->getLoginCode();

        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文確認] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('mypage_login');
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

            // Adding more information
            $commonService = new MyCommonService($this->entityManager);
            $orderItems = $Order->getProductOrderItems();
            $shipping = $commonService->getMstCustomerCode($shipping_code);
            $otodoke = $commonService->getMstCustomerCode($otodoke_code);
            $seikyuCode = $commonService->getCustomerBillSeikyuCode($customer_code, $login_type, $login_code);

            if (!empty($shipping)) {
                $Order->shipping_no = $shipping['customer_code'];
                $Order->shipping_name = $shipping['company_name'];
                $Order->shipping_address = '〒 '.$shipping['postal_code'].$shipping['addr01'].$shipping['addr02'].$shipping['addr03'];
            } else {
                $Order->shipping_no = '';
                $Order->shipping_name = '';
                $Order->shipping_address = '';
            }

            if (!empty($otodoke)) {
                $Order->otodoke_no = $otodoke['customer_code'];
                $Order->otodoke_name = $otodoke['company_name'];
                $Order->otodoke_address = '〒 '.$otodoke['postal_code'].$otodoke['addr01'].$otodoke['addr02'].$otodoke['addr03'];
            } else {
                $Order->otodoke_no = '';
                $Order->otodoke_name = '';
                $Order->otodoke_address = '';
            }

            // SeikyuCode
            if (!empty($seikyuCode)) {
                $Order->seikyu_code = $seikyuCode['customer_code'];
                $Order->seikyu_name = $seikyuCode['company_name'];
                $Order->seikyu_address = '〒 '.$seikyuCode['postal_code'].$seikyuCode['addr01'].$seikyuCode['addr02'].$seikyuCode['addr03'];
            } else {
                $Order->seikyu_code = '';
                $Order->seikyu_name = '';
                $Order->seikyu_address = '';
            }

            foreach ($orderItems as &$order_item) {
                // Get mst_product
                $mstProduct = $this->mstProductRepository->getData($order_item->getProduct()->getId());
                $order_item->setMstProduct($mstProduct);
            }

            //add default day delivery
            if (empty($delivery_date)) {
                $arrDayOff = $commonService->getDayOff();
                $dayOffSatSun = MyCommon::getDayWeekend();
                $arrDayOff = array_merge($arrDayOff, $dayOffSatSun);
                $delivery_date = MyCommon::get3DayAfterDayOff($arrDayOff);
            }

            $Order->customer_order_no = $customer_order_no;
            $Order->delivery_date = $delivery_date;
            $Order->remarks1 = $remarks1;
            $Order->remarks2 = $remarks2;
            $Order->remarks3 = $remarks3;
            $Order->remarks4 = $remarks4;

            //Push Session
            $_SESSION['s_otodoke_code'] = $otodoke_code;
            $_SESSION['customer_order_no'] = $customer_order_no;
            $_SESSION['remarks1'] = $remarks1;
            $_SESSION['remarks2'] = $remarks2;
            $_SESSION['remarks3'] = $remarks3;
            $_SESSION['remarks4'] = $remarks4;
            $_SESSION['delivery_date'] = $delivery_date;

            return [
                'form' => $form->createView(),
                'Order' => $Order,
            ];
        }

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
     *
     * @param Request $request
     *
     * @return PurchaseFlowResult|RedirectResponse|Response|null
     *
     * @throws Exception
     */
    public function checkout(Request $request)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文処理] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('mypage_login');
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
            $commonService = new MyCommonService($this->entityManager);
            $customer_order_no = $this->globalService->getCustomerOrderNo();
            $Order->setOrderNo($Order->getId());

            if (!empty($customer_order_no)) {
                $Order->setOrderNo($customer_order_no);
            }

            log_info('[注文処理] 注文処理を開始します.', [$Order->getOrderNo()]);

            try {
                /*
                 * 集計処理
                 */
                log_info('[注文処理] 集計処理を開始します.', [$Order->getOrderNo()]);
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

                // Adding more information
                $orderItems = $Order->getProductOrderItems();

                foreach ($orderItems as &$order_item) {
                    // Get mst_product
                    $mstProduct = $this->mstProductRepository->getData($order_item->getProduct()->getId());

                    $order_item->setMstProduct($mstProduct);
                }

                $customer_code = $this->globalService->customerCode();
                $login_type = $this->globalService->getLoginType();
                $login_code = $this->globalService->getLoginCode();
                $fusrdec1 = $this->globalService->getFusrdec1();
                $fusrstr8 = $this->globalService->getFusrstr8();
                $otodoke = $commonService->getMstCustomerCode($this->globalService->getOtodokeCode());
                $customer_relation = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);
                $location = $commonService->getCustomerLocation($customer_relation['customer_code'] ?? '');

                $Order->Otodoke = $otodoke;
                $Order->delivery_date = $this->globalService->getDeliveryDate();
                $Order->customer_code = $customer_relation['customer_code'] ?? '';
                $Order->seikyu_code = $customer_relation['seikyu_code'] ?? '';
                $Order->shipping_no = $this->globalService->getShippingCode();
                $Order->otodoke_no = $this->globalService->getOtodokeCode();
                $Order->remarks1 = $request->get('remarks1', '');
                $Order->remarks2 = $request->get('remarks2', '');
                $Order->remarks3 = $request->get('remarks3', '');
                $Order->remarks4 = $request->get('remarks4', '');
                $Order->location = !empty($location) ? $location : 'XB0201001';
                $Order->fvehicleno = ($fusrstr8 == 1 && $Order->getSubtotal() <= $fusrdec1) ? '1000' : '0000';

                log_info('[注文処理] 注文処理が完了しました.', [$Order->getOrderNo()]);
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
            log_info('[注文処理] カートをクリアします.', [$Order->getOrderNo()]);
            $this->cartService->clear();

            // 受注IDをセッションにセット
            // Change by task #1933
            $this->session->set(OrderHelper::SESSION_ORDER_ID, !empty($customer_order_no) ? $customer_order_no : $Order->getOrderNo());

            $rate = $commonService->getTaxInfo()['tax_rate'];
            $Order->setTax((float) $Order->getTotal() / (float) $rate);
            $Order->setPaymentTotal((int) $Order->getTotal() + (int) ((float) $Order->getTotal() / (float) $rate));
            $this->entityManager->persist($Order);
            $this->entityManager->flush();

            // Save info into Session to Send Mail
//            $_SESSION['usc_'.$this->globalService->customerId()]['send_mail'] = [
//                'order_id' => $Order->getId(),
//                'pre_order_id' => $Order->getPreOrderId(),
//            ];

            // Save dt_order and dt_order_status
            try {
                $commonService->saveOrderStatus($Order);
                $commonService->savedtOrder($Order);
            } catch (\Exception $e) {
                log_error('Insert dt_order and dt_order_status error: '.$e->getMessage());
            }

            // メール送信
            log_info('[注文処理] 注文メールの送信を行います.', [$Order->getOrderNo()]);
            $this->mailService->sendOrderMail($Order);
            $this->entityManager->flush();

            log_info('[注文処理] 注文処理が完了しました. 購入完了画面へ遷移します.', [$Order->getOrderNo()]);

            return $this->redirectToRoute('shopping_complete');
        }

        log_info('[注文処理] フォームエラーのため, 購入エラー画面へ遷移します.', [$Order->getOrderNo()]);

        return $this->redirectToRoute('shopping_error');
    }

    /**
     * PaymentMethod::applyを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
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
    public function getOtodokeOption(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $customer_id = $request->get('customer_id', '');
                $shipping_code = $request->get('shipping_code', '');
                $otodokeOpt = $this->globalService->otodokeOption($customer_id, $shipping_code);

                $result = [
                    'status' => 1,
                    'data' => $otodokeOpt ?? [],
                ];

                return $this->json($result, 200);
            }

            $result = [
                'status' => 0,
                'data' => [],
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
    public function changeShippingCode(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $shipping_code = $request->get('shipping_code', '');

                //Nạp lại session shipping_code và otodoke_code
                $_SESSION['s_shipping_code'] = $shipping_code;
                $_SESSION['s_otodoke_code'] = '';

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
    public function changeOtodokeCode(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $otodoke_code = $request->get('otodoke_code', '');

                //Nạp lại session otodoke_code
                $_SESSION['s_otodoke_code'] = $otodoke_code;

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => 0], 400);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

//    /**
//     * Send mail order.
//     *
//     * @Route("/shopping/send-mail", name="shopping_send_mail", methods={"POST"})
//     */
//    public function sendMailOrder(Request $request)
//    {
//        try {
//            if ('POST' === $request->getMethod()) {
//                $customer_id = $this->globalService->customerId();
//
//                if (!empty($_SESSION['usc_'.$customer_id]) && !empty($_SESSION['usc_'.$customer_id]['send_mail'])) {
//                    $pre_order_id = $_SESSION['usc_'.$customer_id]['send_mail']['pre_order_id'] ?? '';
//                    $order_id = $_SESSION['usc_'.$customer_id]['send_mail']['order_id'] ?? '';
//
//                    if (empty($pre_order_id) || empty($order_id)) {
//                        return;
//                    }
//
//                    $commonService = new MyCommonService($this->entityManager);
//                    $Order = $this->orderHelper->getPurchaseCompletedOrder($pre_order_id);
//
//                    log_info('[注文処理] 注文メールの送信を行います.', [$order_id]);
//
//                    $newOrder = null;
//                    $customer_id = $this->globalService->customerId();
//                    $customer = $commonService->getMstCustomer($customer_id);
//
//                    /* Get infomation for case Supper user*/
//                    $root_customer_id = $this->getUser()->getId();
//                    $customer2 = $commonService->getMstCustomer($root_customer_id);
//                    $emailcc = '';
//
//                    if (
//                        !empty($customer2['customer_email']) &&
//                        !empty($customer['customer_email']) &&
//                        $customer2['customer_email'] != $customer['customer_email']
//                    ) {
//                        $emailcc = $customer2['customer_email'];
//                    }
//                    /* End */
//
//                    $newOrder['name'] = $customer['name01'] ?? '';
//                    $newOrder['subtotal'] = $Order['subtotal'];
//                    $newOrder['charge'] = $Order['charge'];
//                    $newOrder['discount'] = $Order['discount'];
//                    $newOrder['delivery_fee_total'] = $Order['delivery_fee_total'];
//                    $newOrder['tax'] = $Order['tax'];
//                    $newOrder['total'] = $Order['total'];
//                    $newOrder['payment_total'] = $Order['payment_total'];
//                    $newOrder['rate'] = $commonService->getTaxInfo()['tax_rate'];
//                    $newOrder['company_name'] = $customer['company_name'] ?? '';
//                    $newOrder['postal_code'] = $customer['postal_code'] ?? '';
//                    $newOrder['addr01'] = $customer['addr01'] ?? '';
//                    $newOrder['addr02'] = $customer['addr02'] ?? '';
//                    $newOrder['addr03'] = $customer['addr03'] ?? '';
//                    $newOrder['phone_number'] = $customer['phone_number'] ?? '';
//                    $newOrder['email'] = $customer['customer_email'] ?? '';
//                    $newOrder['emailcc'] = $emailcc;
//                    $goods = $commonService->getMstProductsOrderCustomer($order_id);
//                    $newOrder['ProductOrderItems'] = $goods;
//                    $newOrder['tax'] = $newOrder['subtotal'] / $newOrder['rate'];
//                    $shipping = $commonService->getMoreOrderCustomer($pre_order_id);
//                    $newOrder['Shipping'] = $shipping;
//
//                    $Order->setName01($customer['name01']);
//                    $Order->setCompanyName($customer['company_name']);
//                    $this->mailService->sendOrderMail($newOrder, $Order);
//                    $this->entityManager->flush();
//
//                    $_SESSION['usc_'.$customer_id]['send_mail'] = null;
//
//                    return $this->json(['status' => 1, 'msg' => 'OK'], 200);
//                }
//            }
//
//            return $this->json(['status' => 1, 'msg' => ''], 200);
//        } catch (\Exception $e) {
//            return $this->json(['status' => 0, 'msg' => $e->getMessage()], 400);
//        }
//    }

    /**
     * Check Merge Order.
     *
     * @Route("/shopping/order/check_existed", name="check_order_existed", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkMergeOrder(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $customer_order_no = $request->get('customer_order_no', '');

                $commonService = new MyCommonService($this->entityManager);
                $customer_id = $this->globalService->customerId();
                $login_type = $this->globalService->getLoginType();
                $login_code = $this->globalService->getLoginCode();
                $arCusLogin = $commonService->getMstCustomer($customer_id);
                $relationCus = $commonService->getCustomerRelationFromUser($arCusLogin['customer_code'], $login_type, $login_code);

                // Get customer_code from dt_customer_relation
                $customer_code = $relationCus['customer_code'] ?? '';

                if (!empty($customer_code) && !empty($customer_order_no)) {
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $dtOrder = $this->entityManager->getRepository(DtOrder::class)->findOneBy([
                        'customer_code' => trim($customer_code),
                        'order_no' => trim($customer_order_no),
                    ], [
                        'order_lineno' => 'DESC',
                    ]);

                    if (!empty($dtOrder)) {
                        return $this->json(['status' => 1], 200);
                    }
                }
            }

            return $this->json(['status' => 0], 200);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * 購入完了画面を表示する.
     *
     * @Route("/shopping/complete", name="shopping_complete", methods={"GET"})
     * @Template("Shopping/complete.twig")
     */
    public function complete(Request $request)
    {
        log_info('[注文完了] 注文完了画面を表示します.');

        // 受注IDを取得
        $orderId = $this->session->get(OrderHelper::SESSION_ORDER_ID);

        if (empty($orderId)) {
            log_info('[注文完了] 受注IDを取得できないため, トップページへ遷移します.');

            return $this->redirectToRoute('homepage');
        }

        $Order = $this->orderRepository->find($orderId);

        $event = new EventArgs(
            [
                'Order' => $Order,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        log_info('[注文完了] 購入フローのセッションをクリアします. ');
        $this->orderHelper->removeSession();
        unset($_SESSION['customer_order_no']);
        unset($_SESSION['remarks1']);
        unset($_SESSION['remarks2']);
        unset($_SESSION['remarks3']);
        unset($_SESSION['remarks4']);
        unset($_SESSION['delivery_date']);
        unset($_SESSION['cart_product_type']);

        $hasNextCart = !empty($this->cartService->getCarts());

        log_info('[注文完了] 注文完了画面を表示しました. ', [$hasNextCart]);

        return [
            'Order' => $Order,
            'order_no' => $orderId,
            'hasNextCart' => $hasNextCart,
        ];
    }

    /**
     * Check Fusrstr.
     *
     * @Route("/shopping/order/check_fusrstr", name="check_fusrstr", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkFusrstr(Request $request)
    {
        try {
            $int_total_price = (int) $request->get('total_price', 0);
            if ('POST' === $request->getMethod()) {
                $int_fusrdec1 = $this->globalService->getFusrdec1();
                $int_fusrstr8 = $this->globalService->getFusrstr8();

                if ($int_fusrstr8 == 1 && $int_total_price < $int_fusrdec1) {
                    return $this->json(['status' => 0, 'message' => $int_fusrdec1], 200);
                }

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => -1, 'message' => 'Method not Allowed'], 400);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * MyCartController
     *
     * @param Request   $request
     * @Route("/shopping/delivery_date/check", name="check_delivery_date", methods={"POST"})
     *
     * @return array
     */
    public function checkDeliveryDate(Request $request)
    {
        $date_want_delivery = $request->get('date_want_delivery');

        if (!MyCommon::isEmptyOrNull($date_want_delivery)) {
            $result = [
                'is_ok' => '0',
                'msg' => 'OK',
                'date_want_delivery' => $date_want_delivery,
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
                ];
            } else {
                $result = [
                    'is_ok' => '1',
                    'msg' => 'OK saved',
                    'date_want_delivery' => $dayAfter,
                ];
            }
        }

        return $this->json($result, 200);
    }
}
