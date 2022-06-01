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

use Customize\Common\MyCommon;
use Customize\Common\MyConstant;
use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\MstShipping;
use Customize\Repository\OrderRepository;
use Customize\Repository\ProductImageRepository;
use Doctrine\DBAL\Types\Type;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MypageController extends AbstractController
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ProductImageRepository
     */
    protected $productImageRepository;

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
        OrderRepository $orderRepository, ProductImageRepository $productImageRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productImageRepository = $productImageRepository;
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
        $qb = $this->orderRepository->getQueryBuilderByCustomer($Customer);

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
}
