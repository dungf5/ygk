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

namespace Customize\Controller\Mypage;

use Customize\Common\FileUtil;
use Customize\Common\MyCommon;
use Customize\Common\MyConstant;
use Customize\Controller\TraitController;
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Repository\DtReturnsImageInfoRepository;
use Customize\Repository\MstProductReturnsInfoRepository;
use Customize\Repository\MstShippingRepository;
use Customize\Repository\OrderItemRepository;
use Customize\Repository\OrderRepository;
use Customize\Repository\ProductImageRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Customize\Service\MailService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MypageController extends AbstractController
{
    use TraitController;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var ProductImageRepository
     */
    protected $productImageRepository;

    /**
     * @var MstShippingRepository
     */
    protected $mstShippingRepository;

    /**
     * @var MstProductReturnsInfoRepository
     */
    protected $mstProductReturnsInfoRepository;
    /**
     * @var DtReturnsImageInfoRepository
     */
    protected $dtReturnsImageInfoRepository;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var GlobalService
     */
    protected $globalService;
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * MypageController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param ProductImageRepository $productImageRepository
     * @param MstShippingRepository $mstShippingRepository
     * @param MstProductReturnsInfoRepository $mstProductReturnsInfoRepository
     * @param DtReturnsImageInfoRepository $dtReturnsImageInfoRepository
     * @param OrderItemRepository $orderItemRepository
     * @param \Twig_Environment $twig
     * @param EntityManagerInterface $entityManager
     * @param BaseInfoRepository $baseInfoRepository
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param GlobalService $globalService
     * @param MailService $mailService
     *
     * @throws \Exception
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductImageRepository $productImageRepository,
        MstShippingRepository $mstShippingRepository,
        MstProductReturnsInfoRepository $mstProductReturnsInfoRepository,
        DtReturnsImageInfoRepository $dtReturnsImageInfoRepository,
        OrderItemRepository $orderItemRepository,
        \Twig_Environment $twig,
        EntityManagerInterface $entityManager,
        BaseInfoRepository $baseInfoRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        GlobalService $globalService,
        MailService $mailService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productImageRepository = $productImageRepository;
        $this->mstShippingRepository = $mstShippingRepository;
        $this->mstProductReturnsInfoRepository = $mstProductReturnsInfoRepository;
        $this->dtReturnsImageInfoRepository = $dtReturnsImageInfoRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $myCm = new MyCommonService($this->entityManager);
        $this->globalService = $globalService;
        $this->mailService = $mailService;

        if ($this->twig->getGlobals()['app']->getUser() != null) {
            $MyDataMstCustomer = $myCm->getMstCustomer($this->globalService->customerId());
            $this->twig->getGlobals()['app']->MyDataMstCustomer = $MyDataMstCustomer;
        }

        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/shipping_list", name="shippingList", methods={"GET"})
     * @Template("/Mypage/shipping_list.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function shippingList(Request $request)
    {
        $comS = new MyCommonService($this->entityManager);
        $customer_code = $this->twig->getGlobals()['app']->MyDataMstCustomer['customer_code'];
        $type = $request->get('type');
        $shipping_no = $request->get('shipping_no');
        $order_no = $request->get('order_no');
        $jan_code = $request->get('jan_code');
        $login_type = $this->globalService->getLoginType();
        $arRe = $comS->getShipList($type, $customer_code, $shipping_no, $order_no, $jan_code, $login_type);
        $otodoke_code = '';
        $shipping_code = '';

        if (count($arRe) > 0) {
            $otodoke_code = $arRe[0]['otodoke_code'];
            $shipping_code = $arRe[0]['shipping_code'];

            foreach ($arRe as $key => &$item) {
                if ($item['jan_code'] == $jan_code) {
                    $item['highlight'] = true;
                } else {
                    $item['highlight'] = false;
                }
            }
        }

        $arMore = $comS->getShipListExtend($otodoke_code, $shipping_code);
        $arReturn = ['myData' => $arRe, 'arMore' => $arMore, 'login_type' => $login_type];

        return $arReturn;
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/exportOrderPdf", name="exportOrderPdf", methods={"GET"})
     * @Template("/Mypage/exportOrderPdf.twig")
     */
    public function exportOrderPdf(Request $request)
    {
        $htmlFileName = 'Mypage/exportOrderPdf.twig';
        $delivery_no = MyCommon::getPara('delivery_no');
        $order_no_line_no = MyCommon::getPara('order_no_line_no');

        $comS = new MyCommonService($this->entityManager);
        $orderNo = explode('-', $order_no_line_no)[0];

        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $comS->getMstCustomer($customer_id)['customer_code'] ?? '';

        $arRe = $comS->getPdfDelivery($delivery_no, $orderNo, $customer_code, $login_type);

        if (!count($arRe)) {
            return $this->redirectToRoute('mypage_delivery_history');
        }

        //add special line
        $totalTax = 0;
        $totalaAmount = 0;
        $inCr = 0;
        $totalTaxRe = 0;

        foreach ($arRe as &$item) {
            $inCr++;
            $totalTax = $totalTax + $item['tax'];
            $totalaAmount = $totalaAmount + $item['amount'];
            $totalTaxRe = $totalTaxRe + (10 / 100) * (int) $item['amount'];
            $item['is_total'] = 0;
            $item['autoIncr'] = $inCr;
            $item['delivery_date'] = explode(' ', $item['delivery_date'])[0];
        }

        $totalaAmountTax = $totalaAmount + $totalTaxRe; //$item["tax"];
        $arSpecial = ['is_total' => 1, 'totalaAmount' => $totalaAmount, 'totalTax' => $totalTax];
        $arRe[] = $arSpecial;

        $dirPdf = MyCommon::getHtmluserDataDir().'/pdf';
        FileUtil::makeDirectory($dirPdf);
        $arReturn = [
            'myDatas' => array_chunk($arRe, 20),
            'OrderTotal' => $totalaAmount,
            'totalTaxRe' => $totalTaxRe,
            'totalaAmountTax' => $totalaAmountTax,
        ];

        $namePdf = 'ship_'.$delivery_no.'.pdf';
        $file = $dirPdf.'/'.$namePdf;

        if (getenv('APP_IS_LOCAL') == 0) {
            $htmlBody = $this->twig->render($htmlFileName, $arReturn);
            MyCommon::converHtmlToPdf($dirPdf, $namePdf, $htmlBody);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');

            readfile($file);
            exit();
        } else {
            exec('"C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe" c:/wamp/www/test/pdf.html c:/wamp/www/test/pdf.pdf');
        }

        return $arReturn;
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/", name="mypage", methods={"GET"})
     * @Template("/Mypage/index.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'pageno' => $request->get('pageno', 1),
            'search_order_date' => $request->get('order_date', 0),
            'search_order_status' => $request->get('order_status', ''),
            'search_order_shipping' => $request->get('order_shipping', '0'),
            'search_order_otodoke' => $request->get('order_otodoke', '0'),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $my_common->getMstCustomer($customer_id)['customer_code'] ?? '';

        $qb = $this->orderItemRepository->getQueryBuilderByCustomer($param, $customer_code, $login_type);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        $listItem = !is_array($pagination) ? $pagination->getItems() : [];
        $arProductId = [];
        $arOrderNo = [];

        //modify data
        foreach ($listItem as &$myItem) {
            $arProductId[] = $myItem['product_id'];
            $arOrderNo[$myItem['ec_order_no']][$myItem['ec_order_lineno']] = $myItem['order_line_no'];

            if (is_object($myItem['update_date'])) {
                $myItem['update_date'] = $myItem['update_date']->format('Y-m-d');
                if (MyCommon::checkExistText($myItem['update_date'], '.000000')) {
                    $myItem['update_date'] = str_replace('.000000', '', $myItem['update_date']);
                } else {
                    $myItem['update_date'] = str_replace('000', '', $myItem['update_date']);
                }
            }

            if (isset(MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']])) {
                $myItem['order_status_name'] = MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']];
            }

            if (isset(MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']])) {
                $myItem['shipping_status'] = MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']];
            }

            $myItem['order_remain_num'] = $myItem['order_remain_num'] * $myItem['quantity'];
            $myItem['reserve_stock_num'] = $myItem['reserve_stock_num'] * $myItem['quantity'];
            $myItem['shipping_num'] = $myItem['shipping_num'] * $myItem['quantity'];

            $myItem['order_type'] = '';
            if (!empty($myItem['ec_type'])) {
                if ($myItem['ec_type'] == '1') {
                    $myItem['order_type'] = 'EC';
                }

                if ($myItem['ec_type'] == '2') {
                    $myItem['order_type'] = 'EOS';
                }
            }
        }

        //auto fill lino
        $arOrderNoAf = [];
        foreach ($arOrderNo as $keyOrder => $arEc) {
            $autoFileId = 1;
            foreach ($arEc as $keyLine => $valNo) {
                if (MyCommon::isEmptyOrNull($valNo)) {
                    $arOrderNoAf[$keyOrder][$keyLine] = $autoFileId;
                    $autoFileId++;
                }
            }
        }

        //get one image of product
        $hsProductImgMain = $this->productImageRepository->getImageMain($arProductId);
        $commonService = new MyCommonService($this->entityManager);
        $listImgs = $commonService->getImageFromEcProductId($arProductId);
        $hsKeyImg = [];

        foreach ($listImgs as $itemImg) {
            $hsKeyImg[$itemImg['product_id']] = $itemImg['file_name'];
        }

        foreach ($listItem as &$myItem) {
            if (isset($hsKeyImg[$myItem['product_id']])) {
                $myItem['main_img'] = $hsKeyImg[$myItem['product_id']];
            } else {
                $myItem['main_img'] = null;
            }

            if (MyCommon::isEmptyOrNull($myItem['order_line_no'])) {
                if (isset($arOrderNoAf[$myItem['ec_order_no']])) {
                    if (isset($arOrderNoAf[$myItem['ec_order_no']][$myItem['ec_order_lineno']])) {
                        $myItem['order_line_no'] = $arOrderNoAf[$myItem['ec_order_no']][$myItem['ec_order_lineno']];
                    }
                }
            }
        }

        if (!is_array($pagination) && count($pagination)) {
            $pagination->setItems($listItem);
        }

        /*create list order date*/
        $orderDateList = [];
        $orderDateList[] = [
            'key' => (string) date('Y-m', ),
            'value' => (string) date('Y-m', ),
        ];

        for ($i = 1; $i < 14; $i++) {
            $date = date('Y-m', strtotime(date('Y-m-01')." -$i months"));
            $orderDateList[] = [
                'key' => (string) $date,
                'value' => (string) $date,
            ];
        }

        /*create list order status*/
        $orderStatusList = [];
        $orderStatusList[] = ['key' => '0', 'value' => '調査要'];
        $orderStatusList[] = ['key' => '1', 'value' => '未確保'];
        $orderStatusList[] = ['key' => '2', 'value' => '一部確保'];
        $orderStatusList[] = ['key' => '3', 'value' => '確保済み'];
        $orderStatusList[] = ['key' => '4', 'value' => 'キャンセル'];
        $orderStatusList[] = ['key' => '9', 'value' => '注文完了'];

        /*create list shipping code*/
        $orderShippingList = [];
        $shippingList = $this->globalService->shippingOption();
        if (count($shippingList) > 1) {
            foreach ($shippingList as $item) {
                $orderShippingList[] = [
                    'key' => $item['shipping_no'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        /*create list otodoke code*/
        $s_order_shipping = (isset($param['search_order_shipping']) && $param['search_order_shipping'] != '0') ? $param['search_order_shipping'] : ($this->globalService->getShippingCode());
        $orderOtodeokeList = [];
        $otodokeList = $this->globalService->otodokeOption($customer_id, $s_order_shipping);
        if (count($otodokeList)) {
            foreach ($otodokeList as $item) {
                $orderOtodeokeList[] = [
                    'key' => $item['otodoke_code'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        return [
            'pagination' => $pagination,
            'hsProductImgMain' => $hsProductImgMain,
            'orderDateOpt' => $orderDateList,
            'orderStatusOpt' => $orderStatusList,
            'orderShippingOpt' => $orderShippingList,
            'orderOtodokeOpt' => $orderOtodeokeList,
            'search_order_date' => $param['search_order_date'],
            'search_order_status' => $param['search_order_status'],
            'search_order_shipping' => $param['search_order_shipping'],
            'search_order_otodoke' => $param['search_order_otodoke'],
        ];
    }

    /**
     * ログイン画面.
     *
     * @Route("/mypage/login", name="mypage_login", methods={"GET", "POST"})
     * @Template("Mypage/login.twig")
     *
     * @param Request $request
     * @param AuthenticationUtils $utils
     *
     * @return array|bool[]|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        // Check case must to choose represent
        if (!empty($_SESSION['choose_represent'])) {
            $my_common = new MyCommonService($this->entityManager);
            $representList = $my_common->getListRepresent();

            if (count($representList) > 1) {
                return [
                    'represent' => true,
                    'representOpt' => $representList,
                ];
            }

            if (count($representList) == 1) {
                $represent_code = $representList[0]['represent_code'];
                $customerId = $_SESSION['customer_id'] ?? '';

                if (!empty($represent_code) && !empty($customerId)) {
                    try {
                        $_SESSION['choose_represent'] = false;
                        $_SESSION["usc_{$customerId}"]['su_represent_code'] = $represent_code;

                        $new_customer_id = $representList[0]['id'];
                        $_SESSION['customer_id'] = $new_customer_id;
                        $_SESSION["usc_{$new_customer_id}"]['login_type'] = $my_common->checkLoginType($represent_code);
                        $_SESSION["usc_{$new_customer_id}"]['login_code'] = $represent_code;
                    } catch (\Exception $e) {
                        $_SESSION['choose_represent'] = true;
                        $_SESSION['customer_id'] = $customerId;
                        $_SESSION["usc_{$customerId}"]['su_represent_code'] = '';
                    }
                }
            }
        }

        // Check case must to choose shipping
        if (!empty($_SESSION['choose_shipping'])) {
            $shippingList = $this->globalService->shippingOption();

            if (count($shippingList) > 1) {
                return [
                    'shipping' => true,
                ];
            }

            if (count($shippingList) == 1) {
                $shipping_code = $shippingList[0]['shipping_no'];
                $customerId = $_SESSION['customer_id'] ?? '';

                if (!empty($customerId)) {
                    try {
                        $loginType = $_SESSION["usc_{$customerId}"]['login_type'] ?? '';

                        if (!empty($loginType) && $loginType == 'represent_code') {
                            $_SESSION['choose_shipping'] = false;
                            $_SESSION['s_shipping_code'] = $shipping_code;
                            $_SESSION["usc_{$customerId}"]['login_type'] = 'change_type';
                        }
                    } catch (\Exception $e) {
                        $_SESSION['choose_shipping'] = true;
                        $_SESSION['s_shipping_code'] = '';
                        $_SESSION["usc_{$customerId}"]['login_type'] = 'represent_code';
                    }
                }
            }
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('homepage');
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory->createNamedBuilder('', CustomerLoginType::class);

        $builder->get('login_memory')->setData((bool) $request->getSession()->get('_security.login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer instanceof Customer) {
                $builder->get('login_email')
                    ->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();
        $this->session->set('is_update_cart', 1);

        return [
            'shipping' => false,
            'represent' => false,
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/favorite", name="mypage_favorite", methods={"GET"})
     * @Template("Mypage/favorite.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function favorite(Request $request, PaginatorInterface $paginator)
    {
        if (!$this->BaseInfo->isOptionFavoriteProduct()) {
            throw new NotFoundHttpException();
        }
        $Customer = $this->getUser();

        // paginator
        $qb = $this->customerFavoriteProductRepository->getQueryBuilderByCustomer($Customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_FAVORITE_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['wrap-queries' => true]
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * Change Shipping Code.
     *
     * @Route("/mypage/shipping/change", name="mypage_shipping", methods={"POST"})
     * @Template("Mypage/login.twig")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeShippingCode(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $shipping_code = $request->get('shipping_code', '');
                $customerId = $_SESSION['customer_id'] ?? '';

                if (!empty($customerId)) {
                    try {
                        $loginType = $_SESSION["usc_{$customerId}"]['login_type'] ?? '';

                        if (!empty($loginType) && $loginType == 'represent_code') {
                            $_SESSION['choose_shipping'] = false;
                            $_SESSION['s_shipping_code'] = $shipping_code;
                            $_SESSION["usc_{$customerId}"]['login_type'] = 'change_type';
                        }
                    } catch (\Exception $e) {
                        return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
                    }
                }

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => 0], 400);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/shipping/history", name="mypage_shipping_history", methods={"GET"})
     * @Template("Mypage/shipping.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function shipping(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        $search_parameter = [
            'shipping_no' => $request->get('shipping_no', ''),
            'shipping_status' => $request->get('shipping_status', 0),
            'order_shipping' => $request->get('order_shipping', '0'),
            'order_otodoke' => $request->get('order_otodoke', '0'),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $my_common->getMstCustomer($customer_id)['customer_code'] ?? '';
        $qb = $this->orderItemRepository->getShippingByCustomer($search_parameter, $customer_code, $login_type);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        $listItem = !is_array($pagination) ? $pagination->getItems() : [];

        //modify data
        foreach ($listItem as &$myItem) {
            $myItem['shipping_company_code'] = trim($myItem['shipping_company_code']);
            $myItem['delivery_url'] = '';

            if ($myItem['shipping_company_code'] == '8003') {
                $inquiryNo = $myItem['inquiry_no'];
                $arrInquiry = explode('-', $inquiryNo);
                $count = (int) ($arrInquiry['1'] ?? null);
                $okurijoNo = 'okurijoNo='.($arrInquiry[0] ? trim($arrInquiry[0]) : '').',';

                for ($i = 1; $i < $count; $i++) {
                    $okurijoNo .= (int) $arrInquiry[0] + $i.',';
                }

                $okurijoNo = trim($okurijoNo, ',');

                $myItem['delivery_url'] = "https://k2k.sagawa-exp.co.jp/p/web/okurijosearch.do?{$okurijoNo}";
            }

            if ($myItem['shipping_company_code'] == '8004') {
                $inquiryNo = $myItem['inquiry_no'];
                $arrInquiry = explode('-', $inquiryNo);
                $count = (int) ($arrInquiry['1'] ?? null);
                $requestNo = 'requestNo1='.($arrInquiry[0] ? trim($arrInquiry[0]) : '').'&';

                for ($i = 1; $i < 10; $i++) {
                    $tempRequestNo = '';

                    if ($i < $count) {
                        $tempRequestNo = $arrInquiry[0] ?? '';
                        $tempRequestNo = !empty($tempRequestNo) ? (int) $tempRequestNo + $i : '';
                    }

                    $requestNo .= 'requestNo'.($i + 1).'='.$tempRequestNo.'&';
                }

                $myItem['delivery_url'] = "https://trackings.post.japanpost.jp/services/srv/search/?{$requestNo}search.x=104&search.y=15&startingUrlPatten=&locale=ja";
            }
        }

        $pagination->setItems($listItem);

        $orderShippingList = [];
        $shippingList = $this->globalService->shippingOption();
        if (count($shippingList) > 1) {
            foreach ($shippingList as $item) {
                $orderShippingList[] = [
                    'key' => $item['shipping_no'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        $s_order_shipping = (isset($search_parameter['order_shipping']) && $search_parameter['order_shipping'] != '0') ? $search_parameter['order_shipping'] : ($this->globalService->getShippingCode());
        $orderOtodeokeList = [];
        $otodokeList = $this->globalService->otodokeOption($customer_id, $s_order_shipping);
        if (count($otodokeList)) {
            foreach ($otodokeList as $item) {
                $orderOtodeokeList[] = [
                    'key' => $item['otodoke_code'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        return [
            'pagination' => $pagination,
            'search_parameter' => $search_parameter,
            'orderShippingOpt' => $orderShippingList,
            'orderOtodokeOpt' => $orderOtodeokeList,
            'login_type' => $login_type,
        ];
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/delivery/history", name="mypage_delivery_history", methods={"GET"})
     * @Template("Mypage/delivery.twig")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function delivery(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'pageno' => $request->get('pageno', 1),
            'delivery_no' => $request->get('delivery_no'),
            'search_shipping_date' => $request->get('shipping_date', 0),
            'search_order_shipping' => $request->get('order_shipping', '0'),
            'search_order_otodoke' => $request->get('order_otodoke', '0'),
            'search_sale_type' => $request->get('sale_type', '0'),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $my_common->getMstCustomer($customer_id)['customer_code'] ?? '';
        $qb = $this->orderItemRepository->getDeliveryByCustomer($param, $customer_code, $login_type);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        /*create list order date*/
        $shippingDateList = [];
        $shippingDateList[] = [
            'key' => (string) date('Y-m', ),
            'value' => (string) date('Y-m', ),
        ];

        for ($i = 1; $i < 14; $i++) {
            $date = date('Y-m', strtotime(date('Y-m-01')." -$i months"));
            $shippingDateList[] = [
                'key' => (string) $date,
                'value' => (string) $date,
            ];
        }

        /*create list order status*/
        $orderStatusList = [];
        $orderStatusList[] = ['key' => '0', 'value' => '調査要'];
        $orderStatusList[] = ['key' => '1', 'value' => '未確保'];
        $orderStatusList[] = ['key' => '2', 'value' => '一部確保'];
        $orderStatusList[] = ['key' => '3', 'value' => '確保済み'];
        $orderStatusList[] = ['key' => '4', 'value' => 'キャンセル'];
        $orderStatusList[] = ['key' => '9', 'value' => '注文完了'];

        /*create list shipping code*/
        $orderShippingList = [];
        $shippingList = $this->globalService->shippingOption();
        if (count($shippingList) > 1) {
            foreach ($shippingList as $item) {
                $orderShippingList[] = [
                    'key' => $item['shipping_no'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        /*create list otodoke code*/
        $s_order_shipping = (isset($param['search_order_shipping']) && $param['search_order_shipping'] != '0') ? $param['search_order_shipping'] : ($this->globalService->getShippingCode());
        $orderOtodeokeList = [];
        $otodokeList = $this->globalService->otodokeOption($customer_id, $s_order_shipping);
        if (count($otodokeList)) {
            foreach ($otodokeList as $item) {
                $orderOtodeokeList[] = [
                    'key' => $item['otodoke_code'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        return [
            'pagination' => $pagination,
            'shippingDateOpt' => $shippingDateList,
            'orderShippingOpt' => $orderShippingList,
            'orderOtodokeOpt' => $orderOtodeokeList,
            'login_type' => $login_type,
            'search_shipping_date' => $param['search_shipping_date'],
            'search_order_shipping' => $param['search_order_shipping'],
            'search_order_otodoke' => $param['search_order_otodoke'],
            'search_sale_type' => $param['search_sale_type'],
        ];
    }

    /**
     * Change Shipping Code.
     *
     * @Route("/mypage/represent/change", name="mypage_represent", methods={"POST"})
     * @Template("Mypage/login.twig")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeRepresentCode(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $represent_code = $request->get('represent_code', '');
                $represent_code = explode('-', $represent_code);
                $customerId = $_SESSION['customer_id'] ?? '';
                $my_common = new MyCommonService($this->entityManager);

                if (!empty($represent_code) && !empty($customerId)) {
                    try {
                        $_SESSION['choose_represent'] = false;
                        $_SESSION["usc_{$customerId}"]['su_represent_code'] = $represent_code[1] ?? '';

                        $new_customer_id = $represent_code[0] ?? '';
                        $_SESSION['customer_id'] = $new_customer_id;
                        $_SESSION["usc_{$new_customer_id}"]['login_type'] = $my_common->checkLoginType($represent_code[1] ?? '');
                        $_SESSION["usc_{$new_customer_id}"]['login_code'] = $represent_code[1] ?? '';
                    } catch (\Exception $e) {
                        return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
                    }
                }

                return $this->json(['status' => 1], 200);
            }

            return $this->json(['status' => 0], 400);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * 返品手続き
     *
     * @Route("/mypage/return", name="mypage_return", methods={"GET"})
     * @Template("Mypage/return.twig")
     */
    public function return(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'returns_status_flag' => '',
            'pageno' => $request->get('pageno', 1),
            'search_jan_code' => $request->get('search_jan_code', ''),
            'search_shipping_date' => $request->get('search_shipping_date', 0),
            'search_shipping' => $request->get('search_shipping', 0),
            'search_otodoke' => $request->get('search_otodoke', 0),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $user_login = $this->twig->getGlobals()['app']->getUser();
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $this->globalService->customerCode();

        if (!empty($_SESSION['usc_'.$customer_id]) && !empty($_SESSION['usc_'.$customer_id]['login_code'])) {
            $represent_code = $_SESSION['usc_'.$customer_id]['login_code'];
            $temp_customer_code = $my_common->getCustomerRelation($represent_code);
            if (!empty($temp_customer_code)) {
                $customer_code = $temp_customer_code['customer_code'];
            }
        }
        $qb = $this->mstProductReturnsInfoRepository->getShippingForReturn($param, $customer_code, $login_type);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        /*create list order date*/
        $shipping_date_list = [];
        for ($i = 0; $i < 24; $i++) {
            $shipping_date_list[] = (string) date('Y-m', strtotime(date('Y-m-01')." -$i months"));
        }

        $shippingList = $this->globalService->shippingOption();

        $search_shipping = (isset($param['search_shipping']) && $param['search_shipping'] != '0') ? $param['search_shipping'] : ($this->globalService->getShippingCode());
        $otodokes = $this->globalService->otodokeOption($customer_id, $search_shipping);

        return [
            'pagination' => $pagination,
            'customer_id' => $customer_id,
            'param' => $param,
            'shipping_date_list' => $shipping_date_list,
            'shippings' => count($shippingList) > 1 ? $shippingList : [],
            'otodokes' => $otodokes,
        ];
    }

    /**
     * 返品手続き
     *
     * @Route("/mypage/return/create", name="mypage_return_create", methods={"GET", "POST"})
     * @Template("Mypage/return_create.twig")
     */
    public function returnCreate(Request $request)
    {
        try {
            $commonService = new MyCommonService($this->entityManager);
            $login_type = $this->globalService->getLoginType();
            $customer_id = $this->globalService->customerId();
            $customer_code = $this->globalService->customerCode();
            $company_name = $this->globalService->companyName();
            $customer_shipping_code = $this->globalService->getShippingCode();
            $customer_otodoke_code = $this->globalService->getOtodokeCode();

            //Params
            $param = [
                'shipping_no' => $request->get('shipping_no', ''),
                'shipping_day' => $request->get('shipping_day', ''),
                'jan_code' => $request->get('jan_code', ''),
                'product_name' => $request->get('product_name', ''),
                'shipping_num' => $request->get('shipping_num', ''),
                'return_status' => $request->get('return_status', 0),
                'shipping_code' => $request->get('shipping_code', $customer_shipping_code),
                'otodoke_code' => $request->get('otodoke_code', $customer_otodoke_code),
                'return_num' => $request->get('return_num', ''),
                'return_reason' => $request->get('return_reason', ''),
                'customer_comment' => $request->get('customer_comment', ''),
                'product_status' => $request->get('product_status', '1'),
                'product_code' => $request->get('product_code', ''),
                'total_returns_num' => $request->get('total_returns_num', ''),
                'cus_order_no' => $request->get('cus_order_no', ''),
                'cus_order_lineno' => $request->get('cus_order_lineno', ''),
            ];

            $returns_reson = $commonService->getReturnsReson();
            $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
            $otodokes = [];
            if (empty($param['shipping_code'])) {
                $param['shipping_code'] = $shippings[0]['shipping_no'];
            }

            if (!empty($param['shipping_code'])) {
                $otodokes = $this->globalService->otodokeOption($customer_id, $param['shipping_code']);
                if (empty($param['otodoke_code'])) {
                    $param['otodoke_code'] = $otodokes[0]['otodoke_code'];
                }
            }

            return [
                'customer_id' => $customer_id,
                'customer_code' => $customer_code,
                'company_name' => $company_name,
                'returns_reson' => $returns_reson,
                'shippings' => $shippings,
                'otodokes' => $otodokes,
                'customer_shipping_code' => $customer_shipping_code,
                'customer_otodoke_code' => $customer_otodoke_code,
                'param' => $param,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnCreate(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品手続き確認
     *
     * @Route("/mypage/return/confirm", name="mypage_return_confirm", methods={"POST"})
     * @Template("Mypage/return_confirm.twig")
     */
    public function returnConfirm(Request $request)
    {
        try {
            $commonService = new MyCommonService($this->entityManager);
            $login_type = $this->globalService->getLoginType();
            $customer_id = $this->globalService->customerId();
            $customer_code = $this->globalService->customerCode();
            $company_name = $this->globalService->companyName();

            $returns_no = $request->get('returns_no');
            $shipping_code = $request->get('shipping_code', '');
            $otodoke_code = $request->get('otodoke_code', '');
            $shipping_no = $request->get('shipping_no', '');
            $shipping_day = $request->get('shipping_day', '');
            $jan_code = $request->get('jan_code', '');
            $shipping_num = $request->get('shipping_num', '');
            $return_status = $request->get('return_status', '');
            $return_reason = $request->get('return_reason', '');
            $customer_comment = $request->get('customer_comment', '');
            $return_num = $request->get('return_num', '');
            $product_status = $request->get('product_status', '');
            $cus_image_url_path = $request->get('cus_image_url_path', []);

            $product_code = $commonService->getJanCodeToProductCode($jan_code);

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson_column = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson_column[$return_reason] ?? '99';

            $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
            $shipping_name = '';
            foreach ($shippings as $shipping) {
                if ($shipping['shipping_no'] == $shipping_code) {
                    $shipping_name = "{$shipping['name01']} 〒 {$shipping['postal_code']} {$shipping['addr01']} {$shipping['addr03']} {$shipping['addr03']}";
                }
            }
            $otodokes = $this->globalService->otodokeOption($customer_id, $shipping_code);
            $otodoke_name = '';
            foreach ($otodokes as $otodoke) {
                if ($otodoke['otodoke_code'] == $otodoke_code) {
                    $otodoke_name = "{$otodoke['name01']} 〒 {$otodoke['postal_code']} {$otodoke['addr01']} {$otodoke['addr03']} {$otodoke['addr03']}";
                }
            }
            $product_name = $commonService->getJanCodeToProductName($jan_code);
            $shipping_num = $commonService->getDeliveredNum($shipping_no, $product_code);
            $returned_num = $commonService->getReturnedNum($shipping_no, $product_code);

            $images = $request->files->get('images', []);
            if (is_array($images) && count($images) > 0) {
                foreach ($images as $image) {
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        break;
                    }

                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                        break;
                    }

                    $size = $image->getSize();
                    if ($size / 1024 / 1024 > 7) {
                        break;
                    }

                    $filename = date('ymdHis').uniqid('_').'.'.$extension;
                    $path = $this->getParameter('eccube_return_image_dir');
                    if ($image->move($this->getParameter('eccube_return_image_dir'), $filename)) {
                        $cus_image_url_path[] = str_replace($this->getParameter('eccube_html_dir'), 'html', $path).'/'.$filename;
                    }
                }
            }

            $errors = [];
            if (empty($return_reason)) {
                $errors['return_reason'] = '返品理由を入力してください。';
            }
            if (empty($customer_comment)) {
                $errors['customer_comment'] = '顧客コメントを入力してください。';
            }
            if ($return_num == '') {
                $errors['return_num'] = '返品数を入力してください。';
            } elseif ($return_num <= 0) {
                $errors['return_num'] = '1以上で入力してください';
            } else {
                $cond = $shipping_num > $returned_num ? $shipping_num - $returned_num : $shipping_num;
                if ($return_num > $cond) {
                    $errors['return_num'] = '出荷数以上の数量は入力できません。';
                }
            }
            if (count($images) < 1) {
                $errors['images'] = '商品画像をファイル添付より選択してください。';
            }
            if (count($images) > 6) {
                $errors['images'] = '最大6枚の画像';
            }

            return [
                'customer_id' => $customer_id,
                'customer_code' => $customer_code,
                'company_name' => $company_name,
                'returns_no' => $returns_no,
                'shipping_code' => $shipping_code,
                'shipping_name' => $shipping_name,
                'otodoke_code' => $otodoke_code,
                'otodoke_name' => $otodoke_name,
                'shipping_num' => $shipping_num,
                'returned_num' => $returned_num,
                'shipping_no' => $shipping_no,
                'shipping_day' => $shipping_day,
                'jan_code' => $jan_code,
                'product_code' => $product_code,
                'product_name' => $product_name,
                'shipping_num' => $shipping_num,
                'return_status' => $return_status,
                'returns_reson' => $returns_reson,
                'returns_reson_text' => $returns_reson_text,
                'return_reason' => $return_reason,
                'customer_comment' => $customer_comment,
                'return_num' => $return_num,
                'product_status' => $product_status,
                'cus_image_url_path' => $cus_image_url_path,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnConfirm(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返却手続き保存
     *
     * @Route("/mypage/return/save", name="mypage_return_save", methods={"POST"})
     * @Template("Mypage/return_create.twig")
     */
    public function returnSave(Request $request)
    {
        try {
            $commonService = new MyCommonService($this->entityManager);
            $login_type = $this->globalService->getLoginType();
            $customer_id = $this->globalService->customerId();
            $customer_code = $this->globalService->customerCode();
            $company_name = $this->globalService->companyName();
            $customer_shipping_code = $this->globalService->getShippingCode();
            $customer_otodoke_code = $this->globalService->getOtodokeCode();
            $customer = $this->globalService->customer();

            //Params
            $param = [
                'shipping_no' => $request->get('shipping_no', ''),
                'shipping_day' => $request->get('shipping_day', ''),
                'jan_code' => $request->get('jan_code', ''),
                'product_name' => $request->get('product_name', ''),
                'shipping_num' => $request->get('shipping_num', ''),
                'return_status' => $request->get('return_status', 0),
                'shipping_code' => $request->get('shipping_code', $customer_shipping_code),
                'otodoke_code' => $request->get('otodoke_code', $customer_otodoke_code),
                'return_num' => $request->get('return_num', ''),
                'return_reason' => $request->get('return_reason', ''),
                'customer_comment' => $request->get('customer_comment', ''),
                'product_status' => $request->get('product_status', '1'),
                'product_code' => $request->get('product_code', ''),
                'total_returns_num' => $request->get('total_returns_num', ''),
                'cus_order_no' => $request->get('cus_order_no', ''),
                'cus_order_lineno' => $request->get('cus_order_lineno', ''),
            ];

            // Set Aprove auto. returns_status_flag = 1
            if (
                in_array(trim($param['return_reason']), ['02', '04']) &&
                date('Ymd', strtotime(' - 30 days')) <= date('Ymd', strtotime($param['shipping_day'])) &&
                date('Ymd', strtotime($param['shipping_day'])) <= date('Ymd')
            ) {
                $param['returns_status_flag'] = 1;
            } else {
                $param['returns_status_flag'] = 0;
            }

            $returns_reson = $commonService->getReturnsReson();
            $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
            $otodokes = [];
            if (empty($param['shipping_code'])) {
                $param['shipping_code'] = $shippings[0]['shipping_no'];
            }

            if (!empty($param['shipping_code'])) {
                $otodokes = $this->globalService->otodokeOption($customer_id, $param['shipping_code']);
                if (empty($param['otodoke_code'])) {
                    $param['otodoke_code'] = $otodokes[0]['otodoke_code'];
                }
            }

            $errors = [];
            if (empty($param['shipping_no'])) {
                $errors['shipping_no'] = '出荷指示№を入力してください。';
            }

            if (empty($param['jan_code'])) {
                $errors['jan_code'] = 'JANコードを入力してください。';
            }

            if (empty($param['return_reason'])) {
                $errors['return_reason'] = '返品理由を入力してください。';
            }

            if (empty($param['customer_comment'])) {
                $errors['customer_comment'] = '顧客コメントを入力してください。';
            }

            if ($param['return_num'] == '') {
                $errors['return_num'] = '返品数を入力してください。';
            } elseif ((int) $param['return_num'] <= 0) {
                $errors['return_num'] = '1以上で入力してください。';
            } elseif (
                ((int) $param['total_returns_num'] > (int) $param['shipping_num']) ||
                ((int) $param['return_num'] > ((int) $param['shipping_num'] - (int) $param['total_returns_num']))
            ) {
                $errors['return_num'] = '出荷数以上の数量は入力できません。';
            }

            $images = $request->files->get('images', []);

            if (count($images) < 1) {
                $errors['images'] = '商品画像をファイル添付より選択してください。';
            }
            if (count($images) > 6) {
                $errors['images'] = '最大6枚の画像';
            }

            if (!empty($errors)) {
                return [
                    'customer_id' => $customer_id,
                    'customer_code' => $customer_code,
                    'company_name' => $company_name,
                    'returns_reson' => $returns_reson,
                    'shippings' => $shippings,
                    'otodokes' => $otodokes,
                    'customer_shipping_code' => $param['shipping_code'],
                    'customer_otodoke_code' => $param['otodoke_code'],
                    'param' => $param,
                    'errors' => $errors,
                ];
            } else {
                // Insert mst_product_returns_info
                $returns_no = $commonService->getReturnsNo();

                $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
                $shipping_name = '';
                foreach ($shippings as $shipping) {
                    if ($shipping['shipping_no'] == $param['shipping_code']) {
                        $shipping_name = "{$shipping['name01']} 〒 {$shipping['postal_code']} {$shipping['addr01']} {$shipping['addr03']} {$shipping['addr03']}";
                    }
                }

                $otodokes = $this->globalService->otodokeOption($customer_id, $param['shipping_code']);
                $otodoke_name = '';
                foreach ($otodokes as $otodoke) {
                    if ($otodoke['otodoke_code'] == $param['otodoke_code']) {
                        $otodoke_name = "{$otodoke['name01']} 〒 {$otodoke['postal_code']} {$otodoke['addr01']} {$otodoke['addr03']} {$otodoke['addr03']}";
                    }
                }

                if (is_array($images) && count($images) > 0) {
                    foreach ($images as $image) {
                        $mimeType = $image->getMimeType();
                        if (0 !== strpos($mimeType, 'image')) {
                            break;
                        }

                        $extension = $image->getClientOriginalExtension();
                        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                            break;
                        }

                        $size = $image->getSize();
                        if ($size / 1024 / 1024 > 7) {
                            break;
                        }

                        $filename = date('ymdHis').uniqid('_').'.'.$extension;
                        $path = $this->getParameter('eccube_return_image_dir');
                        if ($image->move($this->getParameter('eccube_return_image_dir'), $filename)) {
                            $cus_image_url_path[] = str_replace($this->getParameter('eccube_html_dir'), 'html', $path).'/'.$filename;
                        }
                    }
                }

                $mst_product_returns_info = $this->mstProductReturnsInfoRepository->insertData(
                    [
                        'returns_no' => $returns_no,
                        'customer_code' => $customer_code,
                        'shipping_code' => $param['shipping_code'],
                        'shipping_name' => $shipping_name,
                        'otodoke_code' => $param['otodoke_code'],
                        'otodoke_name' => $otodoke_name,
                        'shipping_no' => $param['shipping_no'],
                        'shipping_date' => $param['shipping_day'],
                        'jan_code' => $param['jan_code'],
                        'product_code' => $param['product_code'],
                        'shipping_num' => $param['shipping_num'],
                        'reason_returns_code' => $param['return_reason'],
                        'customer_comment' => $param['customer_comment'],
                        'return_num' => $param['return_num'],
                        'cus_reviews_flag' => $param['product_status'],
                        'cus_image_url_path1' => @$cus_image_url_path[0] ?? '',
                        'cus_image_url_path2' => @$cus_image_url_path[1] ?? '',
                        'cus_image_url_path3' => @$cus_image_url_path[2] ?? '',
                        'cus_image_url_path4' => @$cus_image_url_path[3] ?? '',
                        'cus_image_url_path5' => @$cus_image_url_path[4] ?? '',
                        'cus_image_url_path6' => @$cus_image_url_path[5] ?? '',
                        'returns_status_flag' => $param['returns_status_flag'],
                        'returns_request_date' => date('Y-m-d H:i:s'),
                        'cus_order_no' => $param['cus_order_no'],
                        'cus_order_lineno' => $param['cus_order_lineno'],
                    ]);

                if (!empty($cus_image_url_path)) {
                    $this->dtReturnsImageInfoRepository->insertData(
                        [
                            'returns_no' => $mst_product_returns_info->getReturnsNo(),
                            'cus_image_url_path1' => $mst_product_returns_info->getCusImageUrlPath1(),
                            'cus_image_url_path2' => $mst_product_returns_info->getCusImageUrlPath2(),
                            'cus_image_url_path3' => $mst_product_returns_info->getCusImageUrlPath3(),
                            'cus_image_url_path4' => $mst_product_returns_info->getCusImageUrlPath4(),
                            'cus_image_url_path5' => $mst_product_returns_info->getCusImageUrlPath5(),
                            'cus_image_url_path6' => $mst_product_returns_info->getCusImageUrlPath6(),
                        ]);
                }

                // Send mail User
//                $email = $customer['customer_email'] ?? $customer['email'];
//                $url_preview = $this->generateUrl('mypage_return_preview', ['returns_no' => $mst_product_returns_info->getReturnsNo()], UrlGeneratorInterface::ABSOLUTE_URL);
//                $this->mailService->sendMailReturnProductPreview($email, $url_preview);

                if (!$param['returns_status_flag']) {
                    // Send mail Aprove step 1
                    $email2 = getenv('EMAIL_RETURN_CC') ?? '';
                    $url_approve = $this->generateUrl('mypage_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->mailService->sendMailReturnProductApprove($email2, ['url_approve' => $url_approve, 'company_name' => $customer['company_name'] ?? '']);
                } else {
                    // Auto Aprove Step 1, send mail aprove step 2
                    $email2 = getenv('EMAIL_RETURN_CC') ?? '';
                    $url_approve_finish = $this->generateUrl('mypage_return_approve_finish', ['returns_no' => $mst_product_returns_info->getReturnsNo()], UrlGeneratorInterface::ABSOLUTE_URL);
                    $url_receipt = $this->generateUrl('mypage_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->mailService->sendMailReturnProductApproveYes($email2,
                        [
                            'url_approve_finish' => $url_approve_finish,
                            'url_receipt' => $url_receipt,
                            'company_name' => $customer['company_name'] ?? '',
                        ]
                    );
                }

                return $this->redirectToRoute('mypage_return_save_complete');
            }
        } catch (\Exception $e) {
            log_error('MypageController.php returnSave(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返却手続き保存
     *
     * @Route("/mypage/return/save/complete", name="mypage_return_save_complete", methods={"GET"})
     * @Template("Mypage/return_save.twig")
     */
    public function returnSaveComplete(Request $request)
    {
        return [];
    }

//    /**
//     * 返却手続き保存
//     *
//     * @Route("/mypage/return/save", name="mypage_return_save", methods={"POST"})
//     * @Template("Mypage/return_save.twig")
//     */
//    public function returnSave(Request $request)
//    {
//        try {
//            $commonService = new MyCommonService($this->entityManager);
//            $login_type = $this->globalService->getLoginType();
//            $customer_id = $this->globalService->customerId();
//            $customer_code = $this->globalService->customerCode();
//            $customer = $this->globalService->customer();
//
//            $returns_no = $request->get('returns_no');
//            $shipping_code = $request->get('shipping_code', '');
//            $otodoke_code = $request->get('otodoke_code', '');
//            $shipping_no = $request->get('shipping_no', '');
//            $shipping_day = $request->get('shipping_day', '');
//            $jan_code = $request->get('jan_code', '');
//            $product_code = $request->get('product_code', '');
//            $return_reason = $request->get('return_reason');
//            $customer_comment = $request->get('customer_comment', '');
//            $return_num = $request->get('return_num', '');
//            $product_status = $request->get('product_status', '');
//            $cus_image_url_path = $request->get('cus_image_url_path', []);
//
//            $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
//            $shipping_name = '';
//            foreach ($shippings as $shipping) {
//                if ($shipping['shipping_no'] == $shipping_code) {
//                    $shipping_name = "{$shipping['name01']} 〒 {$shipping['postal_code']} {$shipping['addr01']} {$shipping['addr03']} {$shipping['addr03']}";
//                }
//            }
//            $otodokes = $this->globalService->otodokeOption($customer_id, $shipping_code);
//            $otodoke_name = '';
//            foreach ($otodokes as $otodoke) {
//                if ($otodoke['otodoke_code'] == $otodoke_code) {
//                    $otodoke_name = "{$otodoke['name01']} 〒 {$otodoke['postal_code']} {$otodoke['addr01']} {$otodoke['addr03']} {$otodoke['addr03']}";
//                }
//            }
//
//            $images = $request->files->get('images', []);
//            if (is_array($images) && count($images) > 0) {
//                foreach ($images as $image) {
//                    $mimeType = $image->getMimeType();
//                    if (0 !== strpos($mimeType, 'image')) {
//                        break;
//                    }
//
//                    $extension = $image->getClientOriginalExtension();
//                    if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
//                        break;
//                    }
//
//                    $size = $image->getSize();
//                    if ($size / 1024 / 1024 > 7) {
//                        break;
//                    }
//
//                    $filename = date('ymdHis').uniqid('_').'.'.$extension;
//                    $path = $this->getParameter('eccube_return_image_dir');
//                    if ($image->move($this->getParameter('eccube_return_image_dir'), $filename)) {
//                        $cus_image_url_path[] = str_replace($this->getParameter('eccube_html_dir'), 'html', $path).'/'.$filename;
//                    }
//                }
//            }
//
//            $returns_no = !empty($returns_no) ? $returns_no : $commonService->getReturnsNo();
//            $shipping_date = date('Y-m-d', strtotime(str_replace('/', '-', $shipping_day)));
//            $shipping_num = $commonService->getDeliveredNum($shipping_no, $product_code);
//
//            $mst_product_returns_info = $this->mstProductReturnsInfoRepository->insertData([
//                'returns_no' => $returns_no,
//                'customer_code' => $customer_code,
//                'shipping_code' => $shipping_code,
//                'shipping_name' => $shipping_name,
//                'otodoke_code' => $otodoke_code,
//                'otodoke_name' => $otodoke_name,
//                'shipping_no' => $shipping_no,
//                'shipping_date' => $shipping_date,
//                'jan_code' => $jan_code,
//                'product_code' => $product_code,
//                'shipping_num' => $shipping_num,
//                'reason_returns_code' => $return_reason,
//                'customer_comment' => $customer_comment,
//                'return_num' => $return_num,
//                'cus_reviews_flag' => $product_status,
//                'cus_image_url_path1' => @$cus_image_url_path[0],
//                'cus_image_url_path2' => @$cus_image_url_path[1],
//                'cus_image_url_path3' => @$cus_image_url_path[2],
//                'cus_image_url_path4' => @$cus_image_url_path[3],
//                'cus_image_url_path5' => @$cus_image_url_path[4],
//                'cus_image_url_path6' => @$cus_image_url_path[5],
//                'returns_status_flag' => 0,
//                'returns_request_date' => date('Y-m-d H:i:s'),
//            ]);
//            if (count($cus_image_url_path) > 0) {
//                $this->dtReturnsImageInfoRepository->insertData([
//                    'returns_no' => $mst_product_returns_info->getReturnsNo(),
//                    'cus_image_url_path1' => $mst_product_returns_info->getCusImageUrlPath1(),
//                    'cus_image_url_path2' => $mst_product_returns_info->getCusImageUrlPath2(),
//                    'cus_image_url_path3' => $mst_product_returns_info->getCusImageUrlPath3(),
//                    'cus_image_url_path4' => $mst_product_returns_info->getCusImageUrlPath4(),
//                    'cus_image_url_path5' => $mst_product_returns_info->getCusImageUrlPath5(),
//                    'cus_image_url_path6' => $mst_product_returns_info->getCusImageUrlPath6(),
//                ]);
//            }
//
//            $customer = $commonService->getMstCustomerCode($customer_code);
//            $email = $customer['email'];
//            $url_preview = $this->generateUrl('mypage_return_preview', ['returns_no' => $mst_product_returns_info->getReturnsNo()], UrlGeneratorInterface::ABSOLUTE_URL);
//            $this->mailService->sendMailReturnProductPreview($email, $url_preview);
//
//            $email = $customer['email'];
//            $url_approve = $this->generateUrl('mypage_return_approve', ['returns_no' => $mst_product_returns_info->getReturnsNo()], UrlGeneratorInterface::ABSOLUTE_URL);
//            $this->mailService->sendMailReturnProductApprove($email, $url_approve);
//
//            return [
//                'save' => true,
//            ];
//        } catch (\Exception $e) {
//            log_error('MypageController.php returnSave(): '.$e->getMessage());
//
//            return $this->redirectToRoute('mypage_return');
//        }
//    }

    /**
     * 返品手順の編集
     *
     * @Route("/mypage/return/edit/{returns_no}", name="mypage_return_edit", methods={"GET", "POST"})
     * @Template("Mypage/return_edit.twig")
     */
    public function returnEdit(Request $request, string $returns_no)
    {
        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info)) {
                return $this->redirectToRoute('mypage_return');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());
            $login_type = $this->globalService->getLoginType();
            $customer_id = $this->globalService->customerId();
            $company_name = $this->globalService->companyName();

            $shippings = $commonService->getMstShippingCustomer($login_type, $customer_id);
            $otodokes = [];
            if (!empty($product_returns_info['shipping_code'])) {
                $otodokes = $this->globalService->otodokeOption($customer_id, $product_returns_info['shipping_code']);
            }
            $returns_reson = $commonService->getReturnsReson();

            $returns_num = $request->get('returns_num', $product_returns_info->getReturnsNum());

            return [
                'customer_id' => $customer_id,
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'company_name' => $company_name,
                'shippings' => $shippings,
                'otodokes' => $otodokes,
                'returns_reson' => $returns_reson,
                'returns_num' => $returns_num,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnEdit(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品プロセスのプレビュー
     *
     * @Route("/mypage/return/preview/{returns_no}", name="mypage_return_preview", methods={"GET"})
     * @Template("Mypage/return_preview.twig")
     */
    public function returnPreview(Request $request, string $returns_no)
    {
        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info)) {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = @$returns_reson[$product_returns_info->getReasonReturnsCode()];

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnPreview(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品プロセスを承認しました
     *
     * @Route("/mypage/return/approve/{returns_no}", name="mypage_return_approve", methods={"GET"})
     * @Template("Mypage/return_approve.twig")
     */
    public function returnApprove(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }

        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info) || $product_returns_info->getReturnsStatusFlag() != '0') {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());
            $shipping_num = $commonService->getDeliveredNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode());
            $returned_num = $commonService->getReturnedNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode(), $product_returns_info->getReturnsNo());

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            $returns_num = $request->get('returns_num', $product_returns_info->getReturnsNum());
            $cond = $shipping_num > $returned_num ? $shipping_num - $returned_num : $shipping_num;
            $returns_num = $returns_num > $cond ? $product_returns_info->getReturnsNum() : $returns_num;

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
                'returns_num' => $returns_num,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnApprove(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 承認された返品プロセスを完了しました
     *
     * @Route("/mypage/return/approve/{returns_no}/finish", name="mypage_return_approve_finish", methods={"GET", "POST"})
     * @Template("Mypage/return_approve_finish.twig")
     */
    public function returnApproveFinish(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }

        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info)) {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());
            $shipping_num = $commonService->getDeliveredNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode());
            $returned_num = $commonService->getReturnedNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode(), $product_returns_info->getReturnsNo());

            $cus_reviews_flag = $request->get('cus_reviews_flag', '');
            $shipping_fee = $request->get('shipping_fee', '');
            $aprove_comment_not_yet = $request->get('aprove_comment_not_yet', '');
            $submit = $request->get('submit', '');

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            $returns_num = $request->get('returns_num', $product_returns_info->getReturnsNum());
            $cond = $shipping_num > $returned_num ? $shipping_num - $returned_num : $shipping_num;
            $returns_num = $returns_num > $cond ? $product_returns_info->getReturnsNum() : $returns_num;

            try {
                $generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
                $barcode = base64_encode($generator->getBarcode($returns_no, $generator::TYPE_CODE_39));
            } catch (\Exception $e) {
                $barcode = '';
            }

            if ('POST' === $request->getMethod()) {
                $data = [
                    'cus_reviews_flag' => $cus_reviews_flag,
                    'shipping_fee' => $shipping_fee,
                    'aprove_comment_not_yet' => $aprove_comment_not_yet,
                    'returns_num' => $returns_num,
                ];
                if ($submit == 'aprove') {
                    $data['returns_status_flag'] = 1;
                    $data['aprove_date'] = date('Y-m-d H:i:s');
                } else {
                    $data['returns_status_flag'] = 2;
                    $data['aprove_date_not_yet'] = date('Y-m-d H:i:s');
                }

                $product_returns_info = $this->mstProductReturnsInfoRepository->updadteData($returns_no, $data);

                if ($submit == 'aprove') {
                    $email = getenv('EMAIL_RETURN_CC') ?? '';
                    $url_approve_finish = $this->generateUrl('mypage_return_approve_finish', ['returns_no' => $product_returns_info->getReturnsNo()], UrlGeneratorInterface::ABSOLUTE_URL);
                    $url_receipt = $this->generateUrl('mypage_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->mailService->sendMailReturnProductApproveYes($email,
                        [
                            'url_approve_finish' => $url_approve_finish,
                            'url_receipt' => $url_receipt,
                            'company_name' => $customer['company_name'] ?? '',
                        ]
                    );
                } else {
                    $email = $customer['customer_email'] ?? $customer['email'];
                    $this->mailService->sendMailReturnProductApproveNo($email, $aprove_comment_not_yet);
                }
            }

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'shipping_num' => $shipping_num,
                'returned_num' => $returned_num,
                'returns_reson_text' => $returns_reson_text,
                'barcode' => $barcode,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnApproveFinish(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 領収書返品手続き
     *
     * @Route("/mypage/return/receipt/{returns_no}", name="mypage_return_receipt", methods={"GET"})
     * @Template("Mypage/return_receipt.twig")
     */
    public function returnReceipt(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectStockApprove())) {
            return $this->redirect($this->traitRedirectStockApprove());
        }

        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info) || $product_returns_info->getReturnsStatusFlag() != '1') {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());
            $shipping_num = $commonService->getDeliveredNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode());
            $returned_num = $commonService->getReturnedNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode(), $product_returns_info->getReturnsNo());

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            $returns_num = $request->get('returns_num', $product_returns_info->getReturnsNum());
            $cond = $shipping_num > $returned_num ? $shipping_num - $returned_num : $shipping_num;
            $returns_num = $returns_num > $cond ? $product_returns_info->getReturnsNum() : $returns_num;

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
                'returns_num' => $returns_num,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnReceipt(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 受け取った返品を完了する
     *
     * @Route("/mypage/return/receipt/{returns_no}/finish", name="mypage_return_receipt_finish", methods={"GET", "POST"})
     * @Template("Mypage/return_receipt_finish.twig")
     */
    public function returnReceiptFinish(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectStockApprove())) {
            return $this->redirect($this->traitRedirectStockApprove());
        }

        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info)) {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());
            $shipping_num = $commonService->getDeliveredNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode());
            $returned_num = $commonService->getReturnedNum($product_returns_info->getShippingNo(), $product_returns_info->getProductCode(), $product_returns_info->getReturnsNo());

            $receipt = $request->get('receipt', '');
            $stock_reviews_flag = $request->get('stock_reviews_flag', '');
            $receipt_comment = $request->get('receipt_comment', '');
            $receipt_not_yet_comment = $request->get('receipt_not_yet_comment', '');
            $images = $request->files->get('images', []);

            $returns_num = $request->get('returns_num', $product_returns_info->getReturnsNum());
            $cond = $shipping_num > $returned_num ? $shipping_num - $returned_num : $shipping_num;
            $returns_num = $returns_num > $cond ? $product_returns_info->getReturnsNum() : $returns_num;

            $stock_image_url_path = [];
            if (count($images) > 0) {
                foreach ($images as $image) {
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        break;
                    }

                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                        break;
                    }

                    $size = $image->getSize();
                    if ($size / 1024 / 1024 > 7) {
                        break;
                    }

                    $filename = date('ymdHis').uniqid('_').'.'.$extension;
                    $path = $this->getParameter('eccube_return_image_dir');
                    if ($image->move($this->getParameter('eccube_return_image_dir'), $filename)) {
                        $stock_image_url_path[] = str_replace($this->getParameter('eccube_html_dir'), 'html', $path).'/'.$filename;
                    }
                }
            }

            if ('POST' === $request->getMethod()) {
                $data = [
                    'returns_num' => $returns_num,
                    'stock_image_url_path1' => @$stock_image_url_path[0],
                    'stock_image_url_path2' => @$stock_image_url_path[1],
                    'stock_image_url_path3' => @$stock_image_url_path[2],
                    'stock_image_url_path4' => @$stock_image_url_path[3],
                    'stock_image_url_path5' => @$stock_image_url_path[4],
                    'stock_image_url_path6' => @$stock_image_url_path[5],
                ];

                if ($receipt == 'yes') {
                    $data['returns_status_flag'] = 3;
                    $data['receipt_comment'] = $receipt_comment;
                    $data['product_receipt_date'] = date('Y-m-d H:i:s');
                    $data['stock_reviews_flag'] = $stock_reviews_flag;
                } else {
                    $data['returns_status_flag'] = 4;
                    $data['receipt_not_yet_comment'] = $receipt_not_yet_comment;
                    $data['product_receipt_date_not_yet'] = date('Y-m-d H:i:s');
                }
                $product_returns_info = $this->mstProductReturnsInfoRepository->updadteData($returns_no, $data);
                if (count($stock_image_url_path) > 0) {
                    $this->dtReturnsImageInfoRepository->insertData([
                        'returns_no' => $product_returns_info->getReturnsNo(),
                        'stock_image_url_path1' => $product_returns_info->getStockImageUrlPath1(),
                        'stock_image_url_path2' => $product_returns_info->getStockImageUrlPath2(),
                        'stock_image_url_path3' => $product_returns_info->getStockImageUrlPath3(),
                        'stock_image_url_path4' => $product_returns_info->getStockImageUrlPath4(),
                        'stock_image_url_path5' => $product_returns_info->getStockImageUrlPath5(),
                        'stock_image_url_path6' => $product_returns_info->getStockImageUrlPath6(),
                    ]);
                }

                if ($receipt == 'yes') {
                    $email = $email = getenv('EMAIL_RETURN_CC') ?? '';
                    $url_return_receipt_finish = $this->generateUrl('mypage_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $cus_reviews_flag = $product_returns_info->getCusReviewsFlag() == 1 ? '良品' : ($product_returns_info->getCusReviewsFlag() == 2 ? '不良品' : '');
                    $this->mailService->sendMailReturnProductReceiptYes($email,
                        [
                            'receipt_comment' => $receipt_comment,
                            'url_return_receipt_finish' => $url_return_receipt_finish,
                            'cus_reviews_flag' => $cus_reviews_flag,
                            'company_name' => $customer['company_name'] ?? '',
                        ]
                    );
                } else {
                    $email = $customer['customer_email'] ?? $customer['email'];
                    $this->mailService->sendMailReturnProductReceiptNo($email, $receipt_not_yet_comment);
                }
            }

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnReceipt(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品手続き完了
     *
     * @Route("/mypage/return/complete/{returns_no}", name="mypage_return_complete", methods={"GET"})
     * @Template("Mypage/return_complete.twig")
     */
    public function returnComplete(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }

        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info) || $product_returns_info->getReturnsStatusFlag() != '3') {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnComplete(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品手続き完了完了
     *
     * @Route("/mypage/return/complete/{returns_no}/finish", name="mypage_return_complete_finish", methods={"GET", "POST"})
     * @Template("Mypage/return_complete_finish.twig")
     */
    public function returnCompleteFinish(Request $request, string $returns_no)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }
        
        try {
            $commonService = new MyCommonService($this->entityManager);
            $product_returns_info = $this->mstProductReturnsInfoRepository->find($returns_no);

            if (empty($product_returns_info) || ('GET' === $request->getMethod() && $product_returns_info->getReturnsStatusFlag() != '5')) {
                return $this->redirectToRoute('mypage_return_history');
            }

            if (empty($product_returns_info) || ('POST' === $request->getMethod() && $product_returns_info->getReturnsStatusFlag() != '3')) {
                return $this->redirectToRoute('mypage_return_history');
            }

            $customer = $commonService->getMstCustomerCode($product_returns_info->getCustomerCode());
            $product_name = $commonService->getJanCodeToProductName($product_returns_info->getJanCode());

            $xbj_reviews_flag = $request->get('xbj_reviews_flag', 0);

            if ('POST' === $request->getMethod()) {
                $data = [
                    'xbj_reviews_flag' => $xbj_reviews_flag,
                    'returns_status_flag' => 5,
                    'returned_date' => date('Y-m-d H:i:s'),
                ];

                $product_returns_info = $this->mstProductReturnsInfoRepository->updadteData($returns_no, $data);

                $email = $customer['customer_email'] ?? $customer['email'];
                $this->mailService->sendMailReturnProductComplete($email);
            }

            $returns_reson = $commonService->getReturnsReson();
            $returns_reson = array_column($returns_reson, 'returns_reson', 'returns_reson_id');
            $returns_reson_text = $returns_reson[$product_returns_info->getReasonReturnsCode()];

            return [
                'product_returns_info' => $product_returns_info,
                'customer' => $customer,
                'product_name' => $product_name,
                'returns_reson_text' => $returns_reson_text,
            ];
        } catch (\Exception $e) {
            log_error('MypageController.php returnCompleteFinish(): '.$e->getMessage());

            return $this->redirectToRoute('mypage_return');
        }
    }

    /**
     * 返品履歴
     *
     * @Route("/mypage/return/history", name="mypage_return_history", methods={"GET"})
     * @Template("Mypage/return_history.twig")
     */
    public function returnHistory(Request $request, PaginatorInterface $paginator)
    {
        if (!empty($this->traitRedirect())) {
            return $this->redirect($this->traitRedirect());
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'search_request_date' => $request->get('search_request_date', 0),
            'search_reason_return' => $request->get('search_reason_return', 0),
            'search_shipping' => $request->get('search_shipping', 0),
            'search_otodoke' => $request->get('search_otodoke', 0),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $this->globalService->customerCode();
        $qb = $this->mstProductReturnsInfoRepository->getReturnDataList($param, $customer_code);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        /*create list order date*/
        $request_date_list = [];
        for ($i = 0; $i < 14; $i++) {
            $request_date_list[] = (string) date('Y-m', strtotime(date('Y-m-01')." -$i months"));
        }

        $returns_resons = $my_common->getReturnsReson();
        $shippings = $my_common->getMstShippingCustomer($login_type, $customer_id);
        $otodokes = [];
        if ($param['search_shipping'] > 0) {
            $otodokes = $this->globalService->otodokeOption($customer_id, $param['search_shipping']);
        }

        return [
            'pagination' => $pagination,
            'param' => $param,
            'request_date_list' => $request_date_list,
            'returns_resons' => $returns_resons,
            'shippings' => $shippings,
            'otodokes' => $otodokes,
        ];
    }

    /**
     * get product name.
     *
     * @Route("/mypage/product/name", name="mypage_product_name", methods={"GET"})
     * @Template("")
     */
    public function getProductName(Request $request)
    {
        $result = [
            'status' => false,
        ];

        try {
            $jan_code = $request->get('jan_code');
            $shipping_no = $request->get('shipping_no');

            $my_common = new MyCommonService($this->entityManager);
            $product_code = $my_common->getJanCodeToProductCode($jan_code);

            $product_name = $my_common->getJanCodeToProductName($jan_code);
            $shipping_num = $my_common->getDeliveredNum($shipping_no, $product_code);
            $returned_num = $my_common->getReturnedNum($shipping_no, $product_code);

            $result['data'] = [
                'jan_code' => $jan_code,
                'shipping_no' => $shipping_no,
                'product_name' => $product_name,
                'product_code' => $product_code,
                'shipping_num' => $shipping_num,
                'returned_num' => $returned_num,
            ];
            $result['status'] = true;
        } catch (\Exception $e) {
        }

        return $this->json($result, 200);
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/delivery/print", name="mypage_delivery_print", methods={"GET"})
     * @Template("Mypage/delivery_print.twig")
     */
    public function deliveryPrint(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'pageno' => $request->get('pageno', 1),
            'delivery_no' => $request->get('delivery_no'),
            'search_shipping_date' => $request->get('shipping_date', 0),
            'search_order_shipping' => $request->get('order_shipping', '0'),
            'search_order_otodoke' => $request->get('order_otodoke', '0'),
            'search_sale_type' => $request->get('sale_type', '0'),
            'search_shipping_date_from' => $request->get('search_shipping_date_from', ''),
            'search_shipping_date_to' => $request->get('search_shipping_date_to', ''),
        ];

        // paginator
        $my_common = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $my_common->getMstCustomer($customer_id)['customer_code'] ?? '';
        $qb = $this->orderItemRepository->getDeliveryByCustomer($param, $customer_code, $login_type);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );

        /*create list order date*/
        $shippingDateList = [];
        $shippingDateList[] = [
            'key' => (string) date('Y-m', ),
            'value' => (string) date('Y-m', ),
        ];

        for ($i = 1; $i < 14; $i++) {
            $date = date('Y-m', strtotime("- $i month"));
            $shippingDateList[] = [
                'key' => (string) $date,
                'value' => (string) $date,
            ];
        }

        /*create list order status*/
        $orderStatusList = [];
        $orderStatusList[] = ['key' => '0', 'value' => '調査要'];
        $orderStatusList[] = ['key' => '1', 'value' => '未確保'];
        $orderStatusList[] = ['key' => '2', 'value' => '一部確保'];
        $orderStatusList[] = ['key' => '3', 'value' => '確保済み'];
        $orderStatusList[] = ['key' => '4', 'value' => 'キャンセル'];
        $orderStatusList[] = ['key' => '9', 'value' => '注文完了'];

        /*create list shipping code*/
        $orderShippingList = [];
        $shippingList = $this->globalService->shippingOption();
        if (count($shippingList) > 1) {
            foreach ($shippingList as $item) {
                $orderShippingList[] = [
                    'key' => $item['shipping_no'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        /*create list otodoke code*/
        $s_order_shipping = (isset($param['search_order_shipping']) && $param['search_order_shipping'] != '0') ? $param['search_order_shipping'] : ($this->globalService->getShippingCode());
        $orderOtodeokeList = [];
        $otodokeList = $this->globalService->otodokeOption($customer_id, $s_order_shipping);
        if (count($otodokeList)) {
            foreach ($otodokeList as $item) {
                $orderOtodeokeList[] = [
                    'key' => $item['otodoke_code'],
                    'value' => $item['name01'].'〒'.$item['postal_code'].$item['addr01'].$item['addr02'].$item['addr03'],
                ];
            }
        }

        return [
            'pagination' => $pagination,
            'shippingDateOpt' => $shippingDateList,
            'orderShippingOpt' => $orderShippingList,
            'orderOtodokeOpt' => $orderOtodeokeList,
            'login_type' => $login_type,
            'search_shipping_date' => $param['search_shipping_date'],
            'search_order_shipping' => $param['search_order_shipping'],
            'search_order_otodoke' => $param['search_order_otodoke'],
            'search_sale_type' => $param['search_sale_type'],
            'search_shipping_date_from' => $param['search_shipping_date_from'],
            'search_shipping_date_to' => $param['search_shipping_date_to'],
        ];
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/export_Pdf_multiple", name="exportPdfMultiple", methods={"GET"})
     * @Template("/Mypage/exportPdfMultiple.twig")
     */
    public function exportPdfMultiple(Request $request)
    {
        $htmlFileName = 'Mypage/exportPdfMultiple.twig';
        $preview = MyCommon::getPara('preview');
        $delivery_no = MyCommon::getPara('delivery_no');
        $params = [
            'search_shipping_date' => MyCommon::getPara('shipping_date'),
            'search_order_shipping' => MyCommon::getPara('order_shipping'),
            'search_order_otodoke' => MyCommon::getPara('order_otodoke'),
            'search_sale_type' => MyCommon::getPara('sale_type'),
            'search_shipping_date_from' => MyCommon::getPara('search_shipping_date_from'),
            'search_shipping_date_to' => MyCommon::getPara('search_shipping_date_to'),
        ];

        $comS = new MyCommonService($this->entityManager);
        $customer_id = $this->globalService->customerId();
        $login_type = $this->globalService->getLoginType();
        $customer_code = $comS->getMstCustomer($customer_id)['customer_code'] ?? '';

        if (trim($delivery_no) == 'all') {
            $arr_delivery_no = $comS->getDeliveryNoPrintPDF($customer_code, $login_type, $params);
        } else {
            $arr_delivery_no = array_diff(explode(',', $delivery_no), ['']);
        }

        $arr_data = [];
        foreach ($arr_delivery_no as $item_delivery_no) {
            $arRe = $comS->getPdfDelivery($item_delivery_no, '', $customer_code, $login_type);

            if (!count($arRe)) {
                continue;
            }

            //add special line
            $totalTax = 0;
            $totalaAmount = 0;
            $inCr = 0;
            $totalTaxRe = 0;

            foreach ($arRe as &$item) {
                $inCr++;
                $totalTax = $totalTax + $item['tax'];
                $totalaAmount = $totalaAmount + $item['amount'];
                $totalTaxRe = $totalTaxRe + (10 / 100) * (int) $item['amount'];
                $item['is_total'] = 0;
                $item['autoIncr'] = $inCr;
                $item['delivery_date'] = explode(' ', $item['delivery_date'])[0];
            }

            $totalaAmountTax = $totalaAmount + $totalTaxRe; //$item["tax"];
            $arSpecial = ['is_total' => 1, 'totalaAmount' => $totalaAmount, 'totalTax' => $totalTax];
            $arRe[] = $arSpecial;

            $arReturn = [
                'myDatas' => array_chunk($arRe, 20),
                'OrderTotal' => $totalaAmount,
                'totalTaxRe' => $totalTaxRe,
                'totalaAmountTax' => $totalaAmountTax,
            ];

            $arr_data['data'][] = $arReturn;
        }

        if (!$preview) {
            $dirPdf = MyCommon::getHtmluserDataDir().'/pdf';
            FileUtil::makeDirectory($dirPdf);
            $namePdf = 'ship_'.date('Ymd').'.pdf';
            $file = $dirPdf.'/'.$namePdf;

            if (getenv('APP_IS_LOCAL') == 0) {
                $htmlBody = $this->twig->render($htmlFileName, $arr_data);
                MyCommon::converHtmlToPdf($dirPdf, $namePdf, $htmlBody);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');

                readfile($file);
                exit();
            } else {
                exec('"C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe" c:/wamp/www/test/pdf.html c:/wamp/www/test/pdf.pdf');
            }
        }

        if (!empty($arr_data)) {
            return $arr_data;
        } else {
            return $this->redirectToRoute('mypage_delivery_print');
        }
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/return/history/export/pdf", name="exportPdfReturn", methods={"GET"})
     * @Template("/Mypage/exportPdfReturns.twig")
     */
    public function exportPdfReturn(Request $request)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '9072M');
            ini_set('max_execution_time', '0');
            ini_set('max_input_time', '-1');
            $htmlFileName = 'Mypage/exportPdfReturns.twig';
            $returns_no = MyCommon::getPara('return_no');

            $params = [
                'search_request_date' => MyCommon::getPara('search_request_date'),
                'search_reason_return' => MyCommon::getPara('search_reason_return'),
                'search_shipping' => MyCommon::getPara('search_shipping'),
                'search_otodoke' => MyCommon::getPara('search_otodoke'),
            ];

            $commonService = new MyCommonService($this->entityManager);
            $customer_code = $this->globalService->customerCode();

            if (trim($returns_no) == 'all') {
                $arr_returns_no = $commonService->getReturnNoPrintPDF($customer_code, $params);
            } else {
                $arr_returns_no = array_values(array_diff(explode(',', $returns_no), ['']));
            }

            $data = $commonService->getPdfReturns($customer_code, $arr_returns_no);
            $data['data'] = array_chunk($data, 12);
            $data['customer'] = $this->globalService->customer();

            $dirPdf = MyCommon::getHtmluserDataDir().'/pdf';
            FileUtil::makeDirectory($dirPdf);
            $namePdf = count($arr_returns_no) == 1 ? 'returns_'.$arr_returns_no[0].'.pdf' : 'returns_'.date('YmdHis').'.pdf';
            $file = $dirPdf.'/'.$namePdf;
            $html = $this->twig->render($htmlFileName, $data);

            if (env('APP_IS_LOCAL', 1) != 1) {
                MyCommon::converHtmlToPdf($dirPdf, $namePdf, $html);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                readfile($file);
                unlink($file);
                unlink(str_replace('.pdf', '.html', $file));

                return;
            } else {
                //$dompdf = new Dompdf();
                //$dompdf->loadHtml($html);
                //$dompdf->setPaper('A4');
                //$dompdf->render();
                //$output = $dompdf->output();
                //file_put_contents($file, $output);
                //$dompdf->stream($file);

                if (count($data)) {
                    return $data;
                } else {
                    return $this->redirectToRoute('mypage_return_history');
                }
            }
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return $this->redirectToRoute('mypage_return_history');
        }
    }
}
