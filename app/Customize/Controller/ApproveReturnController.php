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
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
}
