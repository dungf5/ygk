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
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\MstDelivery;
use Customize\Entity\MstShipping;
use Customize\Repository\OrderItemRepository;
use Customize\Repository\OrderRepository;
use Customize\Repository\ProductImageRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
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
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\PaginatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MypageController extends AbstractController
{

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
     * MypageController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param ProductImageRepository $productImageRepository
     * @param CartService $cartService
     * @param BaseInfoRepository $baseInfoRepository
     * @param PurchaseFlow $purchaseFlow
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductImageRepository $productImageRepository,
        OrderItemRepository $orderItemRepository,
        \Twig_Environment $twig,
        EntityManagerInterface $entityManager,
        BaseInfoRepository $baseInfoRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        GlobalService $globalService
    ) {
        $this->orderRepository          = $orderRepository;
        $this->productImageRepository   = $productImageRepository;
        $this->orderItemRepository      = $orderItemRepository;
         $this->twig                    = $twig;
        $this->entityManager            = $entityManager;
        $myCm                           = new MyCommonService($this->entityManager);

        if ($this->twig->getGlobals()["app"]->getUser() != null) {
            $MyDataMstCustomer                                  = $myCm->getMstCustomer($this->twig->getGlobals()["app"]->getUser()->getId());
            $this->twig->getGlobals()["app"]->MyDataMstCustomer = $MyDataMstCustomer;
        }

        $this->BaseInfo                             = $baseInfoRepository->get();
        $this->customerFavoriteProductRepository    = $customerFavoriteProductRepository;
        $this->globalService                        = $globalService;
    }

    /**
     * マイページ.
     *
     * @Route("/mypage/shipping_list", name="shippingList", methods={"GET"})
     * @Template("/Mypage/shipping_list.twig")
     */
    public function shippingList(Request $request)
    {
        $comS               = new MyCommonService($this->entityManager);
        $customer_code      = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $type               = $request->get("type");
        $shipping_no        = $request->get("shipping_no");
        $order_no           = $request->get("order_no");
        $jan_code           = $request->get("jan_code");
        $arRe               = $comS->getShipList($type, $customer_code, $shipping_no, $order_no, $jan_code);
        $otodoke_code       = '';
        $shipping_code      = '';

        if (count($arRe) > 0) {
            $otodoke_code   = $arRe[0]["otodoke_code"];
            $shipping_code  = $arRe[0]["shipping_code"];

            foreach ($arRe AS $key => &$item) {
                if ($item['jan_code'] == $jan_code) {
                    $item['highlight'] = true;
                } else {
                    $item['highlight'] = false;
                }
            }
        }

        $arMore             = $comS->getShipListExtend($otodoke_code, $shipping_code);
        $arReturn           = ["myData" => $arRe, "arMore" => $arMore];

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

        $htmlFileName = "Mypage/exportOrderPdf.twig";
        $delivery_no =MyCommon::getPara("delivery_no");
        $order_no_line_no =MyCommon::getPara("order_no_line_no");
        $myData  =(object)[];

       $mstDelivery = $this->entityManager->getRepository(MstDelivery::class);
      // $arRe = $mstDelivery->getQueryBuilderByDeli($delivery_no,$order_no_line_no);
        $comS = new MyCommonService($this->entityManager);
        $orderNo = explode("-",$order_no_line_no)[0];
        $arRe = $comS->getPdfDelivery($orderNo);



        //add special line
        $totalTax = 0;
        $totalaAmount = 0;
        $totalaAmountTax = 0;
        $inCr = 0;
        $totalTaxRe = 0;
        foreach ($arRe as  &$item){
            $inCr ++;
            $totalTax = $totalTax + $item["tax"];
            $totalaAmount = $totalaAmount + $item["amount"];
            $totalTaxRe = $totalTaxRe +  (10/100)*(int) $item["amount"];
            //$totalaAmountTax = $totalaAmountTax +$item["amount"]+$totalTaxRe;//$item["tax"];
            $item['is_total'] = 0;
            $item['autoIncr'] = $inCr;
            $item['delivery_date'] = explode(" ",$item['delivery_date'])[0] ;
        }
        $totalaAmountTax = $totalaAmount + $totalTaxRe;//$item["tax"];
        $arSpecial = ["is_total"=>1,'totalaAmount'=>$totalaAmount,'totalTax'=>$totalTax];
        $arRe[] =$arSpecial;


        $dirPdf = MyCommon::getHtmluserDataDir()."/pdf";
        FileUtil::makeDirectory($dirPdf);
        $arReturn = ["myData"=>$arRe,"OrderTotal"=>$totalaAmount,"totalTaxRe"=>$totalTaxRe,"totalaAmountTax"=>$totalaAmountTax ];
        $namePdf = "ship_".$delivery_no.".pdf";
        $file = $dirPdf."/".$namePdf;
        if(getenv("APP_IS_LOCAL")==0){
          $htmlBody = $this->twig->render($htmlFileName, $arReturn);

            MyCommon::converHtmlToPdf($dirPdf,$namePdf,$htmlBody);

            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"". basename($file) ."\"");

            readfile ($file);
            exit();
        }else{

            exec('"C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe" c:/wamp/www/test/pdf.html c:/wamp/www/test/pdf.pdf');
        }




       // MyCommon::converHtmlToPdf($dirPdf,$namePdf,$htmlBody);
        //$mpdf->WriteHTML($htmlBody);
      //  $mpdf->Output($inquiry_no.'.pdf', 'D');
        //$mpdf->Output();
        return $arReturn;


    }

    /**
     * マイページ.
     *
     * @Route("/mypage/", name="mypage", methods={"GET"})
     * @Template("/Mypage/index.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param                      = [
            'pageno'                => $request->get('pageno', 1),
            'search_order_date'     => $request->get('order_date', []),
            'search_order_status'   => $request->get('order_status', []),
            'search_order_shipping' => $request->get('order_shipping', []),
            'search_order_otodoke'  => $request->get('order_otodoke', []),
        ];

        if (empty($param['search_order_shipping'])) {
            $param['search_order_otodoke']  = [];
        }

        // Query data
        $customer_code  = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $customer_id    = $this->twig->getGlobals()["app"]->MyDataMstCustomer["ec_customer_id"];
        $login_type     = $this->globalService->getLoginType();

        $qb             = $this->orderItemRepository->getQueryBuilderByCustomer($param, $customer_code, $login_type);

        // Paginator
        $pagination     = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );
        $listItem       = $pagination->getItems();
        $arProductId    = [];
        $arOrderNo      = [];

        //modify data
        foreach ($listItem as &$myItem) {
            $arProductId[]                  = $myItem['product_id'];
            $arOrderNo[$myItem['ec_order_no']][$myItem['ec_order_lineno']] = $myItem['order_line_no'];

            if (is_object($myItem['update_date'])) {
                $myItem['update_date']      = $myItem['update_date']->format('Y-m-d');
                if (MyCommon::checkExistText($myItem['update_date'], '.000000')) {
                    $myItem['update_date']  = str_replace('.000000', '', $myItem['update_date']);
                }

                else {
                    $myItem['update_date']  = str_replace('000', '', $myItem['update_date']);
                }
            }

            if (isset(MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']])) {
                $myItem['order_status']     = MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']];
            }

            if (isset(MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']])) {
                $myItem['shipping_status']  = MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']];
            }

            $myItem['order_remain_num']     = $myItem['order_remain_num']*$myItem['quantity'];
            $myItem['reserve_stock_num']    = $myItem['reserve_stock_num']*$myItem['quantity'];

            $myItem['order_type']           = '';
             if (!empty($myItem['ec_type'])) {
                 if ($myItem['ec_type'] == "1") {
                     $myItem['order_type']  = 'EC';
                 }

                 if ($myItem['ec_type'] == "2") {
                     $myItem['order_type']  = 'EOS';
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
        //a.file_name,a.product_id,b.product_code
        foreach ($listImgs as $itemImg){
            $hsKeyImg[$itemImg["product_id"]] = $itemImg["file_name"];
        }

        foreach ($listItem as &$myItem) {
            if (isset($hsKeyImg[$myItem['product_id']])) {
                $myItem['main_img'] = $hsKeyImg[$myItem['product_id']];
            }

            else {
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

        $pagination->setItems($listItem);

        /*create list order date*/
        $orderDateList          = [];
        $orderDateList[]        = [
            'key'               => (string)date("Y-m", ),
            'value'             => (string)date("Y-m", ),
        ];

        for ($i = 1; $i < 14; $i++) {
            $date               = date("Y-m", strtotime("- $i month"));
            $orderDateList[]    = [
                'key'           => (string)$date,
                'value'         => (string)$date,
            ];
        }

        /*create list order status*/
        $orderStatusList        = [];
        $orderStatusList[]      = ['key' => '0', 'value' => '調査要'];
        $orderStatusList[]      = ['key' => '1', 'value' => '未確保'];
        $orderStatusList[]      = ['key' => '2', 'value' => '一部確保'];
        $orderStatusList[]      = ['key' => '3', 'value' => '確保済み'];
        $orderStatusList[]      = ['key' => '4', 'value' => 'キャンセル'];
        $orderStatusList[]      = ['key' => '9', 'value' => '注文完了'];

        /*create list shipping code*/
        $orderShippingList      = [];
        $shippingList           = $this->globalService->shippingOption();
        if (count($shippingList) > 1) {
            foreach ($shippingList AS $item) {
                $orderShippingList[]    = [
                    'key'               => $item["shipping_no"],
                    'value'             => $item["name01"] . '〒' . $item["postal_code"] . $item["addr01"] . $item["addr02"] . $item["addr03"],
                ];
            }
        }

        /*create list otodoke code*/
        $orderOtodeokeList      = [];
        $otodokeList            = $this->globalService->otodokeOption($customer_id, $param['search_order_shipping'][0] ?? '');
        if (count($otodokeList)) {
            foreach ($otodokeList AS $item) {
                $orderOtodeokeList[]    = [
                    'key'               => $item["otodoke_code"],
                    'value'             => $item["name01"] . '〒' . $item["postal_code"] . $item["addr01"] . $item["addr02"] . $item["addr03"],
                ];
            }
        }

        return [
            'pagination'                => $pagination,
            'hsProductImgMain'          => $hsProductImgMain,
            'orderDateOpt'              => $orderDateList,
            'orderStatusOpt'            => $orderStatusList,
            'orderShippingOpt'          => $orderShippingList,
            'orderOtodokeOpt'           => $orderOtodeokeList,
            'search_order_date'         => implode(",", $param['search_order_date']),
            'search_order_status'       => implode(",", $param['search_order_status']),
            'search_order_shipping'     => implode(",", $param['search_order_shipping']),
            'search_order_otodoke'      => implode(",", $param['search_order_otodoke']),
        ];
    }

    /**
     * ログイン画面.
     *
     * @Route("/mypage/login", name="mypage_login", methods={"GET", "POST"})
     * @Template("Mypage/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        // Check case must to choose shipping
        if(!empty($_SESSION["choose_shipping"])) {

            return [
                'shipping'      => TRUE,
            ];
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
        $this->session->set("is_update_cart", 1);

        return [
            'shipping'  => FALSE,
            'error'     => $utils->getLastAuthenticationError(),
            'form'      => $form->createView(),
        ];
    }
    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/favorite", name="mypage_favorite", methods={"GET"})
     * @Template("Mypage/favorite.twig")
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
     */
    public function changeShippingCode (Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $shipping_code                  = $request->get('shipping_code', '');
                $customerId                     = $_SESSION["customer_id"] ?? '';

                if (!empty($customerId)) {
                    try {
                        $loginType  = $_SESSION["usc_{$customerId}"]['login_type'] ?? '';

                        if (!empty($loginType) && $loginType == "represent_code") {
                            $_SESSION["choose_shipping"]                    = FALSE;
                            $_SESSION['s_shipping_code']                    = $shipping_code;
                            $_SESSION["usc_{$customerId}"]['login_type']    = "change_type";
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
     */
    public function shipping(Request $request, PaginatorInterface $paginator)
    {

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $Customer = $this->getUser();

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_hidden');
        $nf = new MstShipping();
        // paginator
        $customer_code = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $qb = $this->orderItemRepository->getQueryBuilderByCustomer([], $customer_code);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );


        $listItem = [];
        $listItem = $pagination->getItems();
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
                $myItem['order_status'] = MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']];
            }
            if (isset(MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']])) {
                $myItem['shipping_status'] = MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']];
            }
            $myItem['order_remain_num'] = $myItem['order_remain_num']*$myItem['quantity'];
            $myItem['reserve_stock_num'] = $myItem['reserve_stock_num']*$myItem['quantity'];

            $myItem['order_type'] = '';
            if (isset($myItem['flow_type'])) {
                if($myItem['flow_type']=="2"){
                    $myItem['order_type'] = 'EC';
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
        //a.file_name,a.product_id,b.product_code
        foreach ($listImgs as $itemImg){
            $hsKeyImg[$itemImg["product_id"]] = $itemImg["file_name"];
        }

        foreach ($listItem as &$myItem) {
            if (isset($hsKeyImg[$myItem['product_id']])) {
                $myItem['main_img'] = $hsKeyImg[$myItem['product_id']];
            }else{
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

        $pagination->setItems($listItem);

        return [
            'pagination' => $pagination, 'hsProductImgMain' => $hsProductImgMain
        ];
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/delivery/history", name="mypage_delivery_history", methods={"GET"})
     * @Template("Mypage/delivery.twig")
     */
    public function delivery(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $Customer = $this->getUser();

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_hidden');
        $nf = new MstShipping();
        // paginator
        $customer_code = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $qb = $this->orderItemRepository->getQueryBuilderByCustomer([], $customer_code);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => false]
        );


        $listItem = [];
        $listItem = $pagination->getItems();
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
                $myItem['order_status'] = MyConstant::ARR_ORDER_STATUS_TEXT[$myItem['order_status']];
            }
            if (isset(MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']])) {
                $myItem['shipping_status'] = MyConstant::ARR_SHIPPING_STATUS_TEXT[$myItem['shipping_status']];
            }
            $myItem['order_remain_num'] = $myItem['order_remain_num']*$myItem['quantity'];
            $myItem['reserve_stock_num'] = $myItem['reserve_stock_num']*$myItem['quantity'];

            $myItem['order_type'] = '';
            if (isset($myItem['flow_type'])) {
                if($myItem['flow_type']=="2"){
                    $myItem['order_type'] = 'EC';
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
        //a.file_name,a.product_id,b.product_code
        foreach ($listImgs as $itemImg){
            $hsKeyImg[$itemImg["product_id"]] = $itemImg["file_name"];
        }

        foreach ($listItem as &$myItem) {
            if (isset($hsKeyImg[$myItem['product_id']])) {
                $myItem['main_img'] = $hsKeyImg[$myItem['product_id']];
            }else{
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

        $pagination->setItems($listItem);

        return [
            'pagination' => $pagination, 'hsProductImgMain' => $hsProductImgMain
        ];
    }
}
