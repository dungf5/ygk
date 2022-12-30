<?php

/**
 * Global Service
 * Get information at everywhere
 */


namespace Customize\Service;


use Customize\Common\MyCommon;
use Customize\Entity\Price;
use Customize\Service\Common\MyCommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


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
    public function __construct (
        EntityManagerInterface $entityManager,
        ?ContainerInterface $container,
        MyCommonService $myCommon
    )
    {
        $this->entityManager    = $entityManager;
        $this->container        = $container;
        $this->myCommon         = new $myCommon($entityManager);
    }

    public function customerId()
    {
        if ($this->getUser()) {
            $Customer           = $this->getUser();
            $cutomerID          = $this->myCommon->getMstCustomer($Customer->getId())["ec_customer_id"];

            return $cutomerID ?? '';
        }

        return  '';
    }

    public function customerName()
    {
        if ($this->getUser()) {
            $Customer           = $this->getUser();
            $cutomerName        = $this->myCommon->getMstCustomer($Customer->getId())["name01"];

            return $cutomerName ?? '';
        }

        return  '';
    }

    public function companyName()
    {
        if ($this->getUser()) {
            $Customer           = $this->getUser();
            $companyName        = $this->myCommon->getMstCustomer($Customer->getId())["company_name"];

            return $companyName ?? '';
        }

        return  '';
    }

    public function shippingOption($customer_id = '')
    {
        try {
            if ($customer_id != '') {
                $arrSipping         = $this->myCommon->getMstShippingCustomer($customer_id, null);
                $arrSipping         = $arrSipping ?? [];

                if (count($arrSipping) == 1 && isset($arrSipping[0]['shipping_no'])) {
                    $_SESSION['s_shipping_code']    = $arrSipping[0]['shipping_no'] ?? '';
                }

                return $arrSipping;
            }

            return [];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function otodokeOption ($customer_id = '', $shipping_code = '') {
        try {
            if ($customer_id != '' && $shipping_code != '') {
                $arrOtodoke     = $this->myCommon->getCustomerOtodoke($customer_id, $shipping_code, null);

                return $arrOtodoke ?? [];
            }

            return [];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function getShippingCode ()
    {
        return $_SESSION['s_shipping_code'] ?? '';
    }

    public function getOtodokeCode ()
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
        if ($this->getUser()) {
            $Customer           = $this->getUser();
            $priceViewFlg       = $this->myCommon->getMstCustomer($Customer->getId())["price_view_flg"];

            return $priceViewFlg == 1 ? true : false;
        }

        return  true;
    }

    public function getPLType()
    {
        if ($this->getUser()) {
            $Customer           = $this->getUser();
            $plType             = $this->myCommon->getMstCustomer($Customer->getId())["pl_type"];

            return $plType ?? 0;
        }

        return  0;
    }
    /***
     * @param array $hsProductCodeInPrice
     * @var \Customize\Service\CartService $cartService
     * @var MyCommonService $commonService
     * @var GlobalService $globalService
     * @var UserInterface $Customer
     */
    public function setCartIndtPrice(&$hsProductCodeInPrice=[],$hsMstProductCodeCheckShow,$cartService,$commonService,$globalService,$Customer)
    {
        try {
            //************** update cart after change shipping code
            $Cart                   = $cartService->getCart();

            //$Customer               = $this->getUser() ;
            $arCusLogin             = $commonService->getMstCustomer($Customer->getId());
            $arCarItemId            = [];

            $cartId                 = $Cart->getId();
            $represent_type         = $globalService->getRepresentType();
            $shipping_code          = $globalService->getShippingCode();

            if ($represent_type == 1) {
                $productCart        = $commonService->getdtPriceFromCartV2([$cartId], $shipping_code);

            } else {
                $productCart        = $commonService->getdtPriceFromCartV2([$cartId], $arCusLogin['customer_code']);
            }

            $hsPriceUp = [];
            foreach ($productCart as $itemCart) {
                if ($itemCart['price_s02'] != null && ($itemCart['price_s02'] != '')) {
                    $price  = $itemCart['price_s02'];
                    $hsProductCodeInPrice[] = $itemCart["product_code"];
                }
            }
            foreach ($hsMstProductCodeCheckShow as $keyCheck=>$valueCheck){
                if(in_array($keyCheck, $hsProductCodeInPrice)){
                    $hsMstProductCodeCheckShow[$keyCheck]="good_price";
                }
            }
            return $hsMstProductCodeCheckShow;

            //*****************
        } catch (\Exception $e) {

            log_info($e->getTraceAsString());
        }
    }
}
