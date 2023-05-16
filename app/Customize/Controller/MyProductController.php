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
use Customize\Repository\MstDeliveryPlanRepository;
use Customize\Repository\MstProductRepository;
use Customize\Repository\PriceRepository;
use Customize\Repository\ProductRepository as ProductCustomizeRepository;
use Customize\Repository\StockListRepository;
use Customize\Service\Common\MyCommonService;
use Customize\Service\GlobalService;
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\AddCartType;
use Eccube\Form\Type\Master\ProductListMaxType;
use Eccube\Form\Type\Master\ProductListOrderByType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\Master\ProductListMaxRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MyProductController extends AbstractController
{
    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var AuthenticationUtils
     */
    protected $helper;

    /**
     * @var ProductListMaxRepository
     */
    protected $productListMaxRepository;

    private $title = '';

    /**
     * @var PriceRepository
     */
    protected $priceRepository;

    /**
     * @var StockListRepository
     */
    protected $stockListRepository;

    /**
     * @var MstProductRepository
     */
    protected $mstProductRepository;

    /**
     * @var
     */
    protected $mstDeliveryPlanRepository;

    /**
     * @var ProductCustomizeRepository
     */
    protected $productCustomizeRepository;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var GlobalService
     */
    protected $globalService;

    /***
     * MyProductController constructor.
     * @param PurchaseFlow $cartPurchaseFlow
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param CartService $cartService
     * @param ProductRepository $productRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param AuthenticationUtils $helper
     * @param ProductListMaxRepository $productListMaxRepository
     * @param PriceRepository $priceRepository
     * @param StockListRepository $stockListRepository
     */
    public function __construct(
        PurchaseFlow $cartPurchaseFlow,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        ProductRepository $productRepository,
        BaseInfoRepository $baseInfoRepository,
        AuthenticationUtils $helper,
        ProductListMaxRepository $productListMaxRepository,
        PriceRepository $priceRepository,
        StockListRepository $stockListRepository,
        MstProductRepository $mstProductRepository,
        MstDeliveryPlanRepository $mstDeliveryPlanRepository,
        EncoderFactoryInterface $encoderFactory, ProductCustomizeRepository $productCustomizeRepository,
        ProductClassRepository $productClassRepository,
        GlobalService $globalService
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->purchaseFlow = $cartPurchaseFlow;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->helper = $helper;
        $this->productListMaxRepository = $productListMaxRepository;
        $this->priceRepository = $priceRepository;
        $this->stockListRepository = $stockListRepository;
        $this->mstProductRepository = $mstProductRepository;
        $this->mstDeliveryPlanRepository = $mstDeliveryPlanRepository;
        $this->productCustomizeRepository = $productCustomizeRepository;
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $this->encoderFactory = $encoderFactory;
        $this->globalService = $globalService;
    }

    /**
     * 商品詳細画面.
     *
     * @Route("/products/detail/{id}", name="product_detail", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("Product/detail.twig")
     * @ParamConverter("Product", options={"repository_method" = "findWithSortedClassCategories"})
     *
     * @param Request $request
     * @param Product $Product
     *
     * @return array
     */
    public function detail(Request $request, Product $Product)
    {
        $referer = $request->headers->get('referer', '/products/list');
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        if (!$this->checkVisibility($Product)) {
            throw new NotFoundHttpException();
        }

        $builder = $this->formFactory->createNamedBuilder(
            '',
            AddCartType::class,
            null,
            [
                'product' => $Product,
                'id_add_product_id' => false,
            ]
        );

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_DETAIL_INITIALIZE, $event);

        $is_favorite = false;
        $price = null;
        $stock = null;
        $mstDeliveryPlan = null;
        $mstProduct = $this->mstProductRepository->getData($Product->getId());

        if (
            empty($mstProduct) ||
            (!$this->globalService->getSpecialOrderFlg() && strtoupper($mstProduct->getSpecialOrderFlg()) == 'Y')
        ) {
            return $this->redirect($referer);
        }

        $cmS = new MyCommonService($this->entityManager);
        $login_type = $this->globalService->getLoginType();
        $login_code = $this->globalService->getLoginCode();

        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $customer_code = $this->globalService->customerCode();
            $is_favorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
            $dtPrice = $cmS->getPriceFromDtPriceOfCusProductcodeV2($customer_code, $mstProduct->getProductCode(), $login_type, $login_code);
            $relationCus = $cmS->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

            if ($relationCus) {
                $customerCodeForLocation = $relationCus['customer_code'];
            }

            $location = $cmS->getCustomerLocation($customerCodeForLocation ?? '');
            $stock = $this->stockListRepository->getData($mstProduct->getProductCode(), $location);

            if ($stock) {
                $mstDeliveryPlan = $this->mstDeliveryPlanRepository->getData($mstProduct->getProductCode(), $stock);
            }
        }

        //Nếu dt_price no data
        if (empty($dtPrice)) {
            return $this->redirect($referer);
        }

        //check in cart
        $ecProductId = $Product->getId();
        $product_in_cart = $cmS->isProductEcIncart(MyCommon::getCarSession(), $ecProductId);
        $productClassId = '';
        $oneCartId = '';

        if ($product_in_cart == 1) {
            $cartInfoData = $cmS->getCartInfo(MyCommon::getCarSession(), $ecProductId);
            $productClassId = $cartInfoData[0]['productClassId'];
            $oneCartId = $cartInfoData[0]['cart_id'];
        }

        return [
            'title' => $this->title,
            'subtitle' => $Product->getName(),
            'form' => $builder->getForm()->createView(),
            'product_in_cart' => $product_in_cart,
            'Product' => $Product,
            'is_favorite' => $is_favorite,
            'productClassId' => $productClassId,
            'oneCartId' => $oneCartId,
            'Price' => $dtPrice,
            'Stock' => $stock,
            'MstProduct' => $mstProduct,
            'MstDeliveryPlan' => $mstDeliveryPlan,
            'url_referer' => $referer,
        ];
    }

    /**
     * 閲覧可能な商品かどうかを判定
     *
     * @param Product $Product
     *
     * @return boolean 閲覧可能な場合はtrue
     */
    protected function checkVisibility(Product $Product)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $is_admin = $this->session->has('_security_admin');

        // 管理ユーザの場合はステータスやオプションにかかわらず閲覧可能.
        if (!$is_admin) {
            // 在庫なし商品の非表示オプションが有効な場合.
            // if ($this->BaseInfo->isOptionNostockHidden()) {
            //     if (!$Product->getStockFind()) {
            //         return false;
            //     }
            // }
            // 公開ステータスでない商品は表示しない.
            if ($Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
                return false;
            }
        }

        return true;
    }

    /**
     * カートに追加.
     *
     * @Route("/products/get_total_cart", name="get_total_cart", methods={"GET"})
     */
    public function getTotalCart(Request $request)
    {
        $Carts = $this->cartService->getCarts();
        if (count($Carts) > 0) {
            $cartId = $Carts[0]->getId();
            $myComS = new MyCommonService($this->entityManager);
            $totalNew = $myComS->getTotalItemCart($cartId);

            return $this->json(['done' => true, 'messages' => 'Get cart Total', 'totalNew' => $totalNew]);
        }

        return $this->json(['done' => true, 'messages' => 'Get cart Total', 'totalNew' => 0]);
    }

    /**
     * カートに追加.
     *
     * @Route("/products/add_cart/{id}", name="product_add_cart", methods={"POST"}, requirements={"id" = "\d+"})
     */
    public function addCart(Request $request, Product $Product)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // エラーメッセージの配列
        $errorMessages = [];
        if (!$this->checkVisibility($Product)) {
            throw new NotFoundHttpException();
        }

        $builder = $this->formFactory->createNamedBuilder(
            '',
            AddCartType::class,
            null,
            [
                'product' => $Product,
                'id_add_product_id' => false,
            ]
        );

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Product' => $Product,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_CART_ADD_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new NotFoundHttpException();
        }

        $addCartData = $form->getData();

        log_info(
            'カート追加処理開始',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $addCartData['quantity'],
            ]
        );
        $carSession = MyCommon::getCarSession();

        //////////////////////////////check in cart
        $cmS = new MyCommonService($this->entityManager);
        $ecProductId = $Product->getId();
        $product_in_cart = $cmS->isProductEcIncart(MyCommon::getCarSession(), $ecProductId);

        if ($product_in_cart == 1) {
            //productClassId,b.cart_id,a.product_id
            $cartInfoData = $cmS->getCartInfo(MyCommon::getCarSession(), $ecProductId);
            $productClassId = $cartInfoData[0]['productClassId'];
            $oneCartId = $cartInfoData[0]['cart_id'];
            $ProductClass = $this->productClassRepository->find($productClassId);
            $msg = $this->cartService->updateProductCustomize($ProductClass, $addCartData['quantity'], $oneCartId, $productClassId);
        }

        // カートへ追加
        if ($product_in_cart == 0) {
            $this->cartService->addProductCustomize2022($addCartData['product_class_id'], $addCartData['quantity'], $carSession);
        }

        // 明細の正規化
        $Carts = $this->cartService->getCarts();
        $mstProduct = $this->mstProductRepository->getData($Product->getId());

        // set total price
        foreach ($Carts as $Cart) {
            if ($Cart['key_eccube'] == $carSession) {
                $totalPrice = 0;
                foreach ($Cart['CartItems'] as $CartItem) {
                    $totalPrice += $CartItem['price'] * $CartItem['quantity'];
                }

                $Cart->setTotalPrice($totalPrice);
                $Cart->setDeliveryFeeTotal(0);
            }
        }

        $this->cartService->saveCustomize();
        //update cookie
        foreach ($Carts as $Cart) {
            foreach ($Cart['CartItems'] as $CartItem) {
                if ($Cart['key_eccube'] == $carSession) {
                    if ($CartItem->getProductClass()->getProduct()->getId() == $Product->getId()) {
                        setcookie($Product->getId(), $CartItem['quantity'] * $mstProduct->getQuantity(), 0, '/');
                    }
                }
            }
        }

        log_info(
            'カート追加処理完了',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $addCartData['quantity'],
            ]
        );

        $event = new EventArgs(
            [
                'form' => $form,
                'Product' => $Product,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_CART_ADD_COMPLETE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        if ($request->isXmlHttpRequest()) {
            // ajaxでのリクエストの場合は結果をjson形式で返す。
            $myComS = new MyCommonService($this->entityManager);
            $cartId = $Carts[0]->getId();
            $totalNew = $myComS->getTotalItemCart($cartId);

            // 初期化
            $done = null;
            $messages = [];

            if (empty($errorMessages)) {
                // エラーが発生していない場合
                $done = true;
                array_push($messages, trans('front.product.add_cart_complete'));
            } else {
                // エラーが発生している場合
                $done = false;
                $messages = $errorMessages;
            }

            return $this->json([
                'done' => $done,
                'messages' => $messages,
                'totalNew' => $totalNew,
            ]);
        } else {
            // ajax以外でのリクエストの場合はカート画面へリダイレクト
            foreach ($errorMessages as $errorMessage) {
                $this->addRequestError($errorMessage);
            }

            return $this->redirectToRoute('cart');
        }
    }

    /**
     * 商品一覧画面.
     *
     * @Route("/products/list", name="product_list", methods={"GET"})
     * @Template("Product/product_list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);

        // Doctrine SQLFilter
        if ($this->BaseInfo->isOptionNostockHidden()) {
            $this->entityManager->getFilters()->enable('option_nostock_hidden');
        }

        // handleRequestは空のqueryの場合は無視するため
        if ($request->getMethod() === 'GET') {
            $request->query->set('pageno', $request->query->get('pageno', ''));
        }

        // searchForm
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createNamedBuilder('', \Customize\Form\Type\SearchProductType::class);

        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_INITIALIZE, $event);

        /* @var $searchForm \Symfony\Component\Form\FormInterface */
        $searchForm = $builder->getForm();

        $searchForm->handleRequest($request);
        $commonService = new MyCommonService($this->entityManager);
        $user = false;
        $customer_code = '';
        $login_type = '';
        $login_code = '';

        if ($this->isGranted('ROLE_USER')) {
            $user = true;
            $myC = new MyCommonService($this->entityManager);
            $login_type = $this->globalService->getLoginType();
            $login_code = $this->globalService->getLoginCode();
            $Customer_id = $this->globalService->customerId();
            $customer_code = $myC->getMstCustomer($Customer_id)['customer_code'] ?? '';
        }

        // paginator
        $searchData = $searchForm->getData();
        $customerCode = '';
        $shippingCode = '';
        $arProductCodeInDtPrice = [];
        $arTanakaNumber = [];
        $relationCus = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);

        if ($relationCus) {
            $customerCode = $relationCus['customer_code'];
            $shippingCode = $relationCus['shipping_code'];

            if (empty($shippingCode)) {
                $shippingCode = $this->globalService->getShippingCode();
            }

            $arPriceAndTanaka = $commonService->getPriceFromDtPriceOfCusV2($customerCode, $shippingCode);
            $arProductCodeInDtPrice = $arPriceAndTanaka[0];
            $arTanakaNumber = $arPriceAndTanaka[1];
        }

        $qb = $this->productCustomizeRepository->getQueryBuilderBySearchDataNewCustom($searchData, $user, $customerCode, $shippingCode, $arProductCodeInDtPrice, $arTanakaNumber);

        $event = new EventArgs(
            [
                'searchData' => $searchData,
                'qb' => $qb,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_SEARCH, $event);
        $searchData = $event->getArgument('searchData');
        $query = $qb->getQuery()->useResultCache(true, $this->eccubeConfig['eccube_result_cache_lifetime_short']);

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $query,
            !empty($searchData['pageno']) ? $searchData['pageno'] : 1,
            !empty($searchData['disp_number']) ? $searchData['disp_number']->getId() : $this->productListMaxRepository->findOneBy([], ['sort_no' => 'ASC'])->getId()
        );

        $ids = [];

        foreach ($pagination as $key => $Product) {
            $ids[] = $Product['id'];

            //Get dt_price.price_s01
//            $temp                       = $pagination[$key];
//            $temp['price_s01']          = '';
//            $priceTxt                   = $commonService->getPriceFromDtPriceOfCusProductcodeV2($customer_code, $Product['product_code'], $login_type, $login_code);
//            if ($priceTxt) {
//                $temp['price_s01']      = $priceTxt['price_s01'];
//            }
//
//            $pagination[$key]           = $temp;
        }

        $ProductsAndClassCategories = $this->productRepository->findProductsWithSortedClassCategories($ids, 'p.id');
        $listImgs = $commonService->getImageFromEcProductId($ids);
        $hsKeyImg = [];

        foreach ($listImgs as $itemImg) {
            $hsKeyImg[$itemImg['product_id']] = $itemImg['file_name'];
        }

        // addCart form
        $forms = [];

        foreach ($pagination as $Product) {
            if (!isset($ProductsAndClassCategories[$Product['id']])) {
                continue;
            }

            /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
            $builder = $this->formFactory->createNamedBuilder(
                '',
                AddCartType::class,
                null,
                [
                    'product' => $ProductsAndClassCategories[$Product['id']],
                    'allow_extra_fields' => true,
                ]
            );

            $addCartForm = $builder->getForm();
            $forms[$Product['id']] = $addCartForm->createView();
        }

        // 表示件数
        $builder = $this->formFactory->createNamedBuilder(
            'disp_number',
            ProductListMaxType::class,
            null,
            [
                'required' => false,
                'allow_extra_fields' => true,
                'choice_value' => 'sort_no',
            ]
        );

        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_DISP, $event);
        $dispNumberForm = $builder->getForm();
        $dispNumberForm->handleRequest($request);

        // ソート順
        $builder = $this->formFactory->createNamedBuilder(
            'orderby',
            ProductListOrderByType::class,
            null,
            [
                'required' => false, 'choice_value' => 'sort_no',
                'allow_extra_fields' => true,
            ]
        );

        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_ORDER, $event);

        $orderByForm = $builder->getForm();
        $orderByForm->handleRequest($request);
        $Category = $searchForm->get('category_id')->getData();

        return [
            'subtitle' => $this->getPageTitle($searchData),
            'pagination' => $pagination,
            'search_form' => $searchForm->createView(),
            'disp_number_form' => $dispNumberForm->createView(),
            'order_by_form' => $orderByForm->createView(),
            'forms' => $forms,
            'hsKeyImg' => $hsKeyImg,
            'Category' => $Category,
        ];
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
}
