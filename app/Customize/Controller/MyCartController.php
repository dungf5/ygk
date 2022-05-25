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

use Customize\Service\Common\MyCommonService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MyCartController extends AbstractController
{
    /**
     * MyCartController
     *
     * @param Request   $request
     * @Route("/cart_save_temp", name="cart_save_temp", methods={"POST"})
     * @Template("Ajax/add_list.twig")
     *
     * @return array
     */
    public function index(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $commonService = new MyCommonService($this->entityManager);
            $shipping_code = $request->get('shipping_code');
            $pre_order_id = $request->get('pre_order_id');
            $customer_id = $request->get('customer_id');

            $commonService->saveTempCart($shipping_code, $pre_order_id);
            $arrOtoProductOrder = $commonService->getCustomerOtodoke($customer_id, $shipping_code);

            $data = (object) [];
            $data->shipping = $arrOtoProductOrder;


            $result = ['is_ok' => '1', 'msg' => 'OK', 'data' => $data];

            return $result; //$this->json($result, 200);
        }

        return [];
    }

    /**
     * MyCartController
     *
     * @param Request   $request
     * @Route("/cart_save_temp_order", name="cart_save_temp_order", methods={"POST"})
     *
     * @return array
     */
    public function cartSaveTempOrder(Request $request)
    {
        $result = ['is_ok' => '0', 'msg' => 'NG', 'delivery_code' => ''];
        if ('POST' === $request->getMethod()) {
            $commonService = new MyCommonService($this->entityManager);
            $delivery_code = $request->get('delivery_code');
            $pre_order_id = $request->get('pre_order_id');
            $bill_code = $request->get('bill_code');
            $result = ['is_ok' => '1', 'msg' => 'OK', 'delivery_code' => $delivery_code];

            if (!empty($bill_code)) {
                $commonService->saveTempCartBillSeiky($bill_code, $pre_order_id);
                $result = ['is_ok' => '1', 'msg' => 'OK', 'bill_code' => $bill_code, 'pre_order_id' => $pre_order_id];
            }
            if (!empty($delivery_code)) {
                $commonService->saveTempCartDeliCodeOto($delivery_code, $pre_order_id);
                $result = ['is_ok' => '1', 'msg' => 'OK', 'delivery_code' => $delivery_code, 'pre_order_id' => $pre_order_id];
            }



            return $this->json($result, 200);
        }

        return $this->json($result, 400);
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
