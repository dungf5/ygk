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
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\PaginatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MypageController extends AbstractController
{
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
    ) {
        $this->orderRepository = $orderRepository;
        $this->productImageRepository = $productImageRepository;
        $this->orderItemRepository = $orderItemRepository;
         $this->twig = $twig;
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
        $inquiry_no =MyCommon::getPara("inquiry_no");
        $myData  =(object)[];

        $mstDelivery = $this->entityManager->getRepository(MstDelivery::class);
        $arRe = $mstDelivery->getQueryBuilderByDeli($inquiry_no);

        //add special line
        $totalTax = 0;
        $totalaAmount = 0;
        foreach ($arRe as  &$item){
            $totalTax = $totalTax + $item["tax"];
            $totalaAmount = $totalaAmount + $item["amount"];
            $item['is_total'] = 0;
        }
        $arSpecial = ["is_total"=>1,'totalaAmount'=>$totalaAmount,'totalTax'=>$totalTax];
        $arRe[] =$arSpecial;

        $inquiry_no = MyCommon::getPara("inquiry_no");
        $dirPdf = MyCommon::getHtmluserDataDir()."/pdf";
        FileUtil::makeDirectory($dirPdf);
        $arReturn = ["myData"=>$arRe,"OrderTotal"=>$totalaAmount];
        $namePdf = "ship_".$inquiry_no.".pdf";
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
//        $mpdf = new Mpdf();
//        $mpdf->WriteHTML('<h1>Hello world!</h1>');
//        $mpdf->Output();

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $Customer = $this->getUser();

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_hidden');
        $nf = new MstShipping();
        // paginator
        $qb = $this->orderItemRepository->getQueryBuilderByCustomer($Customer);

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
        foreach ($listItem as &$myItem) {
            if (isset($hsProductImgMain[$myItem['product_id']])) {
                $myItem['main_img'] = $hsProductImgMain[$myItem['product_id']];
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

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }
}
