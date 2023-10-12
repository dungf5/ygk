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
use Eccube\Service\PurchaseFlow\PurchaseContext;
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
     * 商品一覧画面.
     *
     * @Route("/products/list", name="product_list", methods={"GET"})
     * @Template("Product/product_list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        //Not login. Redirect home page
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('homepage');
        }
        log_info('Start MyProductController/Index');
        $time_start = time();
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        // Doctrine SQLFilter
        if ($this->BaseInfo->isOptionNostockHidden()) {
            $this->entityManager->getFilters()->enable('option_nostock_hidden');
        }
        // searchForm
        $builder = $this->formFactory->createNamedBuilder('', \Customize\Form\Type\SearchProductType::class);
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
            $request->query->set('pageno', $request->query->get('pageno', ''));
        }
        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_INITIALIZE, $event);
        $searchForm = $builder->getForm();
        $searchForm->handleRequest($request);
        $searchData = $searchForm->getData();
        $commonService = new MyCommonService($this->entityManager);
        $customer_code = '';
        if ($this->isGranted('ROLE_USER')) {
            $customer_code = $this->globalService->customerCode();
        }
        $login_type = $this->globalService->getLoginType();
        $login_code = $this->globalService->getLoginCode();
        $customerCode = '';
        $shippingCode = '';
        $location = '';
        $arTanakaNumber = [];
        $relationCus = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);
        if ($relationCus) {
            $customerCode = $relationCus['customer_code'];
            $shippingCode = $relationCus['shipping_code'];
            if (empty($shippingCode)) {
                $shippingCode = $this->globalService->getShippingCode();
            }
            $location = $commonService->getCustomerLocation($customerCode);
            $arTanakaNumber = $commonService->getTankaList($searchData, $shippingCode, $customerCode);
        }
        $qb = $this->productCustomizeRepository->getQueryBuilderBySearchData($searchData, $customerCode, $shippingCode, $arTanakaNumber, $location);
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
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_ORDER, $event);
        $orderByForm = $builder->getForm();
        $orderByForm->handleRequest($request);
        $Category = $searchForm->get('category_id')->getData();
        $time_end = time();
        $execution_time = ($time_end - $time_start);
        log_info("Time Total for MyProductController/Index Is: $execution_time s");
        log_info('End MyProductController/Index');
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
        //Not login. Redirect home page
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('homepage');
        }
        log_info('Start MyProductController/Detail');
        $time_start = time();
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
        //Check product type
        if ($this->globalService->getProductType() == 2 && $this->globalService->getSpecialOrderFlg() == 1) {
            if ($mstProduct->getSpecialOrderFlg() == null || strtolower($mstProduct->getSpecialOrderFlg()) != 'y') {
                return $this->redirect($referer);
            } else {
                // special product
                $mstProduct->product_type = '2';
            }
        } elseif (strtolower($mstProduct->getSpecialOrderFlg()) == 'y') {
            return $this->redirect($referer);
        } else {
            // normal product
            $mstProduct->product_type = '1';
        }
        if (
            empty($mstProduct) ||
            (!$this->globalService->getSpecialOrderFlg() && strtoupper($mstProduct->getSpecialOrderFlg()) == 'Y')
        ) {
            return $this->redirect($referer);
        }
        $commonService = new MyCommonService($this->entityManager);
        $login_type = $this->globalService->getLoginType();
        $login_code = $this->globalService->getLoginCode();
        if ($this->isGranted('ROLE_USER')) {
            $Customer = $this->getUser();
            $customer_code = $this->globalService->customerCode();
            $is_favorite = $this->customerFavoriteProductRepository->isFavorite($Customer, $Product);
            $relationCus = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);
            if ($relationCus) {
                $customerCode = $relationCus['customer_code'];
                $shippingCode = $relationCus['shipping_code'];
                if (empty($shippingCode)) {
                    $shippingCode = $this->globalService->getShippingCode();
                }
                $price = $commonService->getPriceFromDtPrice($customerCode, $shippingCode, $mstProduct->getProductCode());
                //Nếu dt_price no data
                if (empty($price)) {
                    log_info('MyProductController/Detail: No Price_S01');
                    return $this->redirect($referer);
                }
                $location = $commonService->getCustomerLocation($customerCode ?? '');
                $stock = $this->stockListRepository->getData($mstProduct->getProductCode(), $location);
            }
            if ($stock) {
                $mstDeliveryPlan = $this->mstDeliveryPlanRepository->getData($mstProduct->getProductCode(), $stock);
            }
        }
        $time_end = time();
        $execution_time = ($time_end - $time_start);
        log_info("Time Total for MyProductController/Detail Is: $execution_time s");
        log_info('End MyProductController/Detail');
        return [
            'title' => $this->title,
            'subtitle' => $Product->getName(),
            'form' => $builder->getForm()->createView(),
            'Product' => $Product,
            'is_favorite' => $is_favorite,
            'Price' => $price,
            'Stock' => $stock,
            'MstProduct' => $mstProduct,
            'MstDeliveryPlan' => $mstDeliveryPlan,
            'url_referer' => $referer,
        ];
    }
    /**
     * カートに追加.
     *
     * @Route("/products/add_cart/{id}", name="product_add_cart", methods={"POST"}, requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Product $Product
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addCart(Request $request, Product $Product)
    {
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $commonService = new MyCommonService($this->entityManager);
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
        $form = $builder->getForm();
        $form->handleRequest($request);
        $carSession = MyCommon::getCarSession();
        // Get mst_product
        $mstProduct = $this->mstProductRepository->getData($Product->getId());
        $addCartData = $form->getData();
        $product_quantity = $addCartData['quantity'];
        if (!empty($mstProduct) && (int) $mstProduct->getQuantity() > 1) {
            $product_quantity = (int) $addCartData['quantity'] / (int) $mstProduct['quantity'];
        }
        $price = $mstProduct['unit_price'] ?? 0;
        // Override price
        $customer_code = $this->globalService->customerCode();
        $login_type = $this->globalService->getLoginType();
        $login_code = $this->globalService->getLoginCode();
        $relationCus = $commonService->getCustomerRelationFromUser($customer_code, $login_type, $login_code);
        if ($relationCus) {
            $customerCode = $relationCus['customer_code'];
            $shippingCode = $relationCus['shipping_code'];
            if (empty($shippingCode)) {
                $shippingCode = $this->globalService->getShippingCode();
            }
            $dtPrice = $commonService->getPriceFromDtPrice($customerCode, $shippingCode, $mstProduct->getProductCode());
        }
        if (!empty($dtPrice)) {
            $price = $dtPrice['price_s01'];
        }
        // End - Override price
        log_info(
            'カート追加処理開始',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $product_quantity,
                'price' => $price,
                'product_type' => $request->get('product_type', 1),
            ]
        );
        // カートへ追加
        $this->cartService->addProduct($addCartData['product_class_id'], $product_quantity, $price);
        // 明細の正規化
        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
            // 復旧不可のエラーが発生した場合は追加した明細を削除.
            if ($result->hasError()) {
                $this->cartService->removeProduct($addCartData['product_class_id']);
                foreach ($result->getErrors() as $error) {
                    $errorMessages[] = $error->getMessage();
                }
            }
            foreach ($result->getWarning() as $warning) {
                $errorMessages[] = $warning->getMessage();
            }
            //update cookie
            foreach ($Cart['CartItems'] as $CartItem) {
                if ($Cart['key_eccube'] == $carSession) {
                    if ($CartItem->getProductClass()->getProduct()->getId() == $Product->getId()) {
                        setcookie($Product->getId(), $CartItem['quantity'] * $mstProduct->getQuantity(), 0, '/');
                    }
                }
            }
        }
        $this->cartService->save();
        $cartId = $Carts[0]->getId() ?? '';
        log_info(
            'カート追加処理完了',
            [
                'product_id' => $Product->getId(),
                'product_class_id' => $addCartData['product_class_id'],
                'quantity' => $product_quantity,
                'price' => $price,
                'product_type' => $request->get('product_type', 1),
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
            return $this->json(['done' => $done, 'messages' => $messages, 'cart_quantity_total' => $commonService->getTotalItemCart($cartId) ?? 1]);
        } else {
            // ajax以外でのリクエストの場合はカート画面へリダイレクト
            foreach ($errorMessages as $errorMessage) {
                $this->addRequestError($errorMessage);
            }
            return $this->redirectToRoute('cart');
        }
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
    /**
     * Change Product Type.
     *
     * @Route("/products/type/change", name="products_type_change", methods={"POST"})
     * @Template("Product/product_list.twig")
     */
    public function changeProductType(Request $request)
    {
        try {
            if ('POST' === $request->getMethod()) {
                $product_type = $request->get('product_type', 1);
                //Nạp lại session product_type
                $_SESSION['s_product_type'] = in_array($product_type, [1, 2]) ? $product_type : 1;
                return $this->json(['status' => 1], 200);
            }
            return $this->json(['status' => 0, 'message' => 'Method not Allowed'], 400);
        } catch (\Exception $e) {
            return $this->json(['status' => -1, 'message' => $e->getMessage()], 400);
        }
    }
}