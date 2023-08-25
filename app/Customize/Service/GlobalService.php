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

namespace Customize\Service;

use Customize\Service\Common\MyCommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalService
{
    // FIXME 必要なメソッドのみ移植する
    use ControllerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MyCommonService
     */
    protected $myCommon;

    /**
     * EccubeExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?ContainerInterface $container,
        MyCommonService $myCommon
    ) {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->myCommon = new $myCommon($entityManager);
    }

    public function customerId()
    {
        return $_SESSION['customer_id'] ?? '';
    }

    public function customer()
    {
        if ($this->customerId() != '') {
            return $this->myCommon->getMstCustomer($this->customerId());
        }

        return null;
    }

    public function customerCode()
    {
        if ($this->customerId() != '') {
            $customerCode = $this->myCommon->getMstCustomer($this->customerId())['customer_code'];

            return $customerCode ?? '';
        }

        return '';
    }

    public function customerName()
    {
        if ($this->customerId() != '') {
            $cutomerName = $this->myCommon->getMstCustomer($this->customerId())['name01'];

            return $cutomerName ?? '';
        }

        return '';
    }

    public function companyName()
    {
        if ($this->customerId() != '') {
            $companyName = $this->myCommon->getMstCustomer($this->customerId())['company_name'];

            return $companyName ?? '';
        }

        return '';
    }

    public function shippingOption()
    {
        try {
            if ($this->customerId() != '') {
                $arrShipping = $this->myCommon->getMstShippingCustomer($this->getLoginType(), $this->customerId(), null);
                $arrShipping = $arrShipping ?? [];

                if (count($arrShipping) == 1 && isset($arrShipping[0]['shipping_no'])) {
                    $_SESSION['s_shipping_code'] = $arrShipping[0]['shipping_no'] ?? '';
                }

                return $arrShipping;
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function otodokeOption($customer_id = '', $shipping_code = '')
    {
        try {
            if ($customer_id != '' && $shipping_code != '') {
                $arrOtodoke = $this->myCommon->getCustomerOtodoke($this->getLoginType(), $customer_id, $shipping_code, null);

                return $arrOtodoke ?? [];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getShippingCode()
    {
        return $_SESSION['s_shipping_code'] ?? '';
    }

    public function getOtodokeCode()
    {
        return $_SESSION['s_otodoke_code'] ?? '';
    }

    public function getPreOrderId()
    {
        return $_SESSION['s_pre_order_id'] ?? '';
    }

    public function getRepresentType()
    {
        return $_SESSION['represent_type'] ?? 0;
    }

    public function getPriceViewFlg()
    {
        if ($this->customerId() != '') {
            $priceViewFlg = $this->myCommon->getMstCustomer($this->customerId())['price_view_flg'];

            return $priceViewFlg == 1 ? true : false;
        }

        return true;
    }

    public function getPLType()
    {
        if ($this->customerId() != '') {
            $plType = $this->myCommon->getMstCustomer($this->customerId())['pl_type'];

            return $plType ?? 0;
        }

        return 0;
    }

    public function getSpecialOrderFlg()
    {
        if ($this->customerId() != '') {
            $SpecialOrderFlg = $this->myCommon->getMstCustomer($this->customerId())['special_order_flg'];

            return $SpecialOrderFlg ?? 0;
        }

        return 0;
    }

    public function getLoginType()
    {
        try {
            if (!empty($_SESSION['usc_'.$this->customerId()])) {
                return $_SESSION['usc_'.$this->customerId()]['login_type'];
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getLoginCode()
    {
        try {
            if (!empty($_SESSION['usc_'.$this->customerId()])) {
                return $_SESSION['usc_'.$this->customerId()]['login_code'];
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPdfExportFlg()
    {
        if ($this->customerId() != '') {
            $pdfExportFlg = $this->myCommon->getMstCustomer($this->customerId())['pdf_export_flg'];

            return $pdfExportFlg ?? 0;
        }

        return 0;
    }

    public function getProductType()
    {
        // 1. 通常品 (normal product)
        // 2. 特注品 (special product)

        if ($this->getSpecialOrderFlg() != 1) {
            $_SESSION['s_product_type'] = 1;
        }

        return $_SESSION['s_product_type'] ?? 1;
    }

    public function getCartProductType()
    {
        // 1. 通常品 (normal product)
        // 2. 特注品 (special product)

        return $_SESSION['cart_product_type'] ?? '';
    }

    public function getRemarks1()
    {
        return $_SESSION['remarks1'] ?? '';
    }

    public function getRemarks2()
    {
        return $_SESSION['remarks2'] ?? '';
    }

    public function getRemarks3()
    {
        return $_SESSION['remarks3'] ?? '';
    }

    public function getRemarks4()
    {
        return $_SESSION['remarks4'] ?? '';
    }

    public function getDeliveryDate()
    {
        return $_SESSION['delivery_date'] ?? '';
    }

    public function getCustomerOrderNo()
    {
        return $_SESSION['customer_order_no'] ?? '';
    }
}
