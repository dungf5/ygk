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

use Customize\Common\FileUtil;
use Customize\Common\MyCommon;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ApproveReturnController extends AbstractController
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
     * 返品手続き
     *
     * @Route("/mypage/approve/1", name="approve_return_1", methods={"GET"})
     * @Template("Approve/approve1.twig")
     */
    public function approveReturn1(Request $request, PaginatorInterface $paginator)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'returns_status_flag' => 0,
            'search_jan_code' => $request->get('search_jan_code', ''),
            'search_returns_no' => $request->get('search_returns_no', 0),
            'search_request_date' => $request->get('search_request_date', 0),
            'search_customer' => $request->get('search_customer', 0),
            'search_shipping' => $request->get('search_shipping', 0),
            'search_otodoke' => $request->get('search_otodoke', 0),
            'search_product' => $request->get('search_product', 0),
        ];

        // paginator
        $commonService = new MyCommonService($this->entityManager);
        $qb = $this->mstProductReturnsInfoRepository->getReturnDataList($param);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => true]
        );

        /*create list order date*/
        $request_date_list = [];
        for ($i = 0; $i < 14; $i++) {
            $request_date_list[] = (string) date('Y-m', strtotime(date('Y-m-01')." -$i months"));
        }

        $returnNo = $commonService->getReturnNoList($param['returns_status_flag']);
        $customers = $commonService->getReturnCustomerList($param['returns_status_flag']);
        $shippings = $commonService->getReturnShippingList($param['returns_status_flag']);
        $otodokes = $commonService->getReturnOtodokeList($param['returns_status_flag']);
        $products = $commonService->getReturnProductList($param['returns_status_flag']);

        $listItem = !is_array($pagination) ? $pagination->getItems() : [];
        foreach ($listItem as &$item) {
            $item['url'] = $this->generateUrl('mypage_return_approve', ['returns_no' => $item['returns_no']], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $pagination->setItems($listItem);

        return [
            'pagination' => $pagination,
            'param' => $param,
            'request_date_list' => $request_date_list,
            'returnNo' => $returnNo,
            'customers' => $customers,
            'shippings' => $shippings,
            'otodokes' => $otodokes,
            'products' => $products,
        ];
    }

    /**
     * 返品手続き
     *
     * @Route("/mypage/approve/2", name="approve_return_2", methods={"GET"})
     * @Template("Approve/approve2.twig")
     */
    public function approveReturn2(Request $request, PaginatorInterface $paginator)
    {
        if (!empty($this->traitRedirectStockApprove())) {
            return $this->redirect($this->traitRedirectStockApprove());
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'returns_status_flag' => 1,
            'search_jan_code' => $request->get('search_jan_code', ''),
            'search_returns_no' => $request->get('search_returns_no', 0),
            'search_request_date' => $request->get('search_request_date', 0),
            'search_aprove_date' => $request->get('search_aprove_date', 0),
            'search_customer' => $request->get('search_customer', 0),
            'search_shipping' => $request->get('search_shipping', 0),
            'search_otodoke' => $request->get('search_otodoke', 0),
            'search_product' => $request->get('search_product', 0),
        ];

        // paginator
        $commonService = new MyCommonService($this->entityManager);
        $qb = $this->mstProductReturnsInfoRepository->getReturnDataList($param);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => true]
        );

        $listItem = !is_array($pagination) ? $pagination->getItems() : [];
        foreach ($listItem as &$item) {
            $item['url'] = $this->generateUrl('mypage_return_receipt', ['returns_no' => $item['returns_no']], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $pagination->setItems($listItem);

        /*create list order date*/
        $request_date_list = [];
        for ($i = 0; $i < 14; $i++) {
            $request_date_list[] = (string) date('Y-m', strtotime(date('Y-m-01')." -$i months"));
        }

        $returnNo = $commonService->getReturnNoList($param['returns_status_flag']);
        $customers = $commonService->getReturnCustomerList($param['returns_status_flag']);
        $shippings = $commonService->getReturnShippingList($param['returns_status_flag']);
        $otodokes = $commonService->getReturnOtodokeList($param['returns_status_flag']);
        $products = $commonService->getReturnProductList($param['returns_status_flag']);

        return [
            'pagination' => $pagination,
            'param' => $param,
            'request_date_list' => $request_date_list,
            'returnNo' => $returnNo,
            'customers' => $customers,
            'shippings' => $shippings,
            'otodokes' => $otodokes,
            'products' => $products,
        ];
    }

    /**
     * 返品手続き
     *
     * @Route("/mypage/approve/3", name="approve_return_3", methods={"GET"})
     * @Template("Approve/approve3.twig")
     */
    public function approveReturn3(Request $request, PaginatorInterface $paginator)
    {
        if (!empty($this->traitRedirectApprove())) {
            return $this->redirect($this->traitRedirectApprove());
        }

        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');

        //Params
        $param = [
            'returns_status_flag' => 3,
            'search_jan_code' => $request->get('search_jan_code', ''),
            'search_returns_no' => $request->get('search_returns_no', 0),
            'search_request_date' => $request->get('search_request_date', 0),
            'search_product_receipt_date' => $request->get('search_product_receipt_date', 0),
            'search_customer' => $request->get('search_customer', 0),
            'search_shipping' => $request->get('search_shipping', 0),
            'search_otodoke' => $request->get('search_otodoke', 0),
            'search_product' => $request->get('search_product', 0),
        ];

        // paginator
        $commonService = new MyCommonService($this->entityManager);
        $qb = $this->mstProductReturnsInfoRepository->getReturnDataList($param);

        // Paginator
        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['distinct' => true]
        );

        $listItem = !is_array($pagination) ? $pagination->getItems() : [];
        foreach ($listItem as &$item) {
            $item['url'] = $this->generateUrl('mypage_return_complete', ['returns_no' => $item['returns_no']], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $pagination->setItems($listItem);

        /*create list order date*/
        $request_date_list = [];
        for ($i = 0; $i < 14; $i++) {
            $request_date_list[] = (string) date('Y-m', strtotime(date('Y-m-01')." -$i months"));
        }

        $returnNo = $commonService->getReturnNoList($param['returns_status_flag']);
        $customers = $commonService->getReturnCustomerList($param['returns_status_flag']);
        $shippings = $commonService->getReturnShippingList($param['returns_status_flag']);
        $otodokes = $commonService->getReturnOtodokeList($param['returns_status_flag']);
        $products = $commonService->getReturnProductList($param['returns_status_flag']);

        return [
            'pagination' => $pagination,
            'param' => $param,
            'request_date_list' => $request_date_list,
            'returnNo' => $returnNo,
            'customers' => $customers,
            'shippings' => $shippings,
            'otodokes' => $otodokes,
            'products' => $products,
        ];
    }

    /**
     * Export PDF
     *
     * @Route("/mypage/approve/export/pdf", name="exportPdfApprove", methods={"GET"})
     * @Template("/Approve/exportPdfApprove.twig")
     */
    public function exportPdfApprove(Request $request)
    {
        if (!empty($this->traitRedirectStockApprove())) {
            return $this->redirect($this->traitRedirectStockApprove());
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '9072M');
            ini_set('max_execution_time', '0');
            ini_set('max_input_time', '-1');
            $htmlFileName = 'Approve/exportPdfApprove.twig';
            $returns_no = MyCommon::getPara('return_no');

            $params = [
                'search_returns_no' => MyCommon::getPara('search_returns_no'),
                'search_request_date' => MyCommon::getPara('search_request_date'),
                'search_aprove_date' => MyCommon::getPara('search_aprove_date'),
                'search_jan_code' => MyCommon::getPara('search_jan_code'),
                'search_customer' => MyCommon::getPara('search_customer'),
                'search_shipping' => MyCommon::getPara('search_shipping'),
                'search_otodoke' => MyCommon::getPara('search_otodoke'),
                'search_product' => MyCommon::getPara('search_product'),
                'returns_status_flag' => MyCommon::getPara('returns_status_flag'),
            ];

            $commonService = new MyCommonService($this->entityManager);

            if (!empty($params['returns_status_flag']) && (int) $params['returns_status_flag'] == 1) {
                if (!empty($this->traitRedirectStockApprove())) {
                    return $this->redirect($this->traitRedirectStockApprove());
                }
            }

            if (!empty($params['returns_status_flag']) && (int) $params['returns_status_flag'] != 1) {
                if (!empty($this->traitRedirectApprove())) {
                    return $this->redirect($this->traitRedirectApprove());
                }
            }

            if (trim($returns_no) == 'all') {
                $arr_returns_no = $commonService->getApproveNoPrintPDF($params);
            } else {
                $arr_returns_no = array_values(array_diff(explode(',', $returns_no), ['']));
            }

            $data = $commonService->getPdfApprove($params, $arr_returns_no);

            // Modify data
            foreach ($data as &$item) {
                try {
                    $generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
                    $barcode = base64_encode($generator->getBarcode($item['returns_no'], $generator::TYPE_CODE_39));
                } catch (\Exception $e) {
                    $barcode = '';
                }

                $item['barcode'] = $barcode;
                $item['create_date'] = date('Y年m月d日', strtotime($item['create_date']));
                $item['cus_image_url_path1'] = $this->imgToBase64($item['cus_image_url_path1']);
                $item['cus_image_url_path2'] = $this->imgToBase64($item['cus_image_url_path2']);
                $item['cus_image_url_path3'] = $this->imgToBase64($item['cus_image_url_path3']);
                $item['cus_image_url_path4'] = $this->imgToBase64($item['cus_image_url_path4']);
                $item['cus_image_url_path5'] = $this->imgToBase64($item['cus_image_url_path5']);
                $item['cus_image_url_path6'] = $this->imgToBase64($item['cus_image_url_path6']);
            }

            $arr_data['data'] = $data;
            $arr_data['line'] = $this->imgToBase64('html/user_data/assets/img/common/dash_dot.png');

            $dirPdf = MyCommon::getHtmluserDataDir().'/pdf';
            FileUtil::makeDirectory($dirPdf);
            $namePdf = count($arr_returns_no) == 1 ? 'returns_'.$arr_returns_no[0].'.pdf' : 'returns_'.date('YmdHis').'.pdf';
            $file = $dirPdf.'/'.$namePdf;
            $html = $this->twig->render($htmlFileName, $arr_data);

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

                if (count($arr_data)) {
                    return $arr_data;
                } else {
                    return $this->redirectToRoute('mypage_return_history');
                }
            }
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return $this->redirectToRoute('mypage_return_history');
        }
    }

    private function imgToBase64($path)
    {
        if (empty($path) || !file_exists($path)) {
            return '';
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            return '';
        }

        try {
            $data = file_get_contents($path);
            $base64 = 'data:image/'.$extension.';base64,'.base64_encode($data);

            return $base64;
        } catch (\Exception $e) {
            log_error($e->getMessage());

            return '';
        }
    }

    /**
     * ログイン画面.
     *
     * @Route("/approve/login", name="approve_login", methods={"GET", "POST"})
     * @Template("Approve/login.twig")
     *
     * @param Request $request
     * @param AuthenticationUtils $utils
     *
     * @return array|bool[]|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
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

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }
}
