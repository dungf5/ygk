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
     * MypageController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param ProductImageRepository $productImageRepository
     * @param CartService $cartService
     * @param BaseInfoRepository $baseInfoRepository
     * @param PurchaseFlow $purchaseFlow
     */
    public function __construct(
        OrderRepository $orderRepository, ProductImageRepository $productImageRepository,OrderItemRepository $orderItemRepository,  \Twig_Environment $twig
    ,EntityManagerInterface $entityManager,BaseInfoRepository $baseInfoRepository, CustomerFavoriteProductRepository $customerFavoriteProductRepository) {
        $this->orderRepository = $orderRepository;
        $this->productImageRepository = $productImageRepository;
        $this->orderItemRepository = $orderItemRepository;
         $this->twig = $twig;
        $this->entityManager =$entityManager;
        $myCm = new MyCommonService($this->entityManager);
        if($this->twig->getGlobals()["app"]->getUser()!=null){
            $MyDataMstCustomer = $myCm->getMstCustomer($this->twig->getGlobals()["app"]->getUser()->getId());
            $this->twig->getGlobals()["app"]->MyDataMstCustomer=$MyDataMstCustomer;
        }

        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;



    }

    /**
     * マイページ.
     *
     * @Route("/mypage/shipping_list", name="shippingList", methods={"GET"})
     * @Template("/Mypage/shipping_list.twig")
     */
    public function shippingList(Request $request)
    {

        $arRe=[];
        $comS = new MyCommonService($this->entityManager);
        $customer_code = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $shipping_no = $request->get("shipping_no");
        $order_no = $request->get("order_no");

        $arRe = $comS->getShipList($customer_code,$shipping_no,$order_no);

        $arReturn = ["myData"=>$arRe];

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
        $myData  =(object)[];

        $mstDelivery = $this->entityManager->getRepository(MstDelivery::class);
        $arRe = $mstDelivery->getQueryBuilderByDeli($delivery_no);


        //add special line
        $totalTax = 0;
        $totalaAmount = 0;
        $totalaAmountTax = 0;
        foreach ($arRe as  &$item){
            $totalTax = $totalTax + $item["tax"];
            $totalaAmount = $totalaAmount + $item["amount"];
            $totalaAmountTax = $totalaAmountTax +$item["amount"]+$item["tax"];
            $item['is_total'] = 0;
            $item['delivery_date'] = explode(" ",$item['delivery_date'])[0] ;
        }
        $arSpecial = ["is_total"=>1,'totalaAmount'=>$totalaAmount,'totalTax'=>$totalTax];
        $arRe[] =$arSpecial;


        $dirPdf = MyCommon::getHtmluserDataDir()."/pdf";
        FileUtil::makeDirectory($dirPdf);
        $arReturn = ["myData"=>$arRe,"OrderTotal"=>$totalaAmount,"totalaAmountTax"=>$totalaAmountTax ];
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
        $Customer = $this->getUser();

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_hidden');
        $nf = new MstShipping();
        // paginator
        $customer_code = $this->twig->getGlobals()["app"]->MyDataMstCustomer["customer_code"];
        $qb = $this->orderItemRepository->getQueryBuilderByCustomer($customer_code);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_INDEX_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax']
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

            // if (isset($myItem['shipping_status'])) {
            $myItem['order_type'] = 'EC';
            //}
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
            'pagination' => $pagination, 'hsProductImgMain' => $hsProductImgMain,
        ];
    }
    /**
     * ログイン画面.
     *
     * @Route("/mypage/login_check", name="mypage_login_check", methods={"GET", "POST"})
     * @Template("Mypage/login.twig")
     */
    public function login_check(Request $request, AuthenticationUtils $utils)
    {


        $login_code = $request->get('login_code');
        $myC = new MyCommonService($this->entityManager);
        $strRe = 'FAIL';
        $dataGet = $myC->getEmailFromUserCode($login_code);
        if(count($dataGet)==1){
            $strRe =  $dataGet[0]["email"];
        }
        echo json_encode(["email"=>$strRe]) ;
        die();

        //return $this->redirectToRoute('mypage');
    }
    /**
     * ログイン画面.
     *
     * @Route("/mypage/login", name="mypage_login", methods={"GET", "POST"})
     * @Template("Mypage/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('mypage');
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', CustomerLoginType::class);

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
        $this->session->set("is_update_cart",1);
        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
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
}
