<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\Date;

if (!class_exists('\Customize\Entity\MstProductReturnsInfo', false)) {
    /**
     * MstProductReturnsInfo.php
     *
     * @ORM\Table(name="dt_order_status")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstProductReturnsInfo")
     */
    class MstProductReturnsInfo extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=15, options={"comment":"STRA注文番号"})
         */
        private $returns_no;

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string")
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $otodoke_code;
        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_name", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $otodoke_name;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_no;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_date;
        /**
         * @var string
         *
         * @ORM\Column(name="jan_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $jan_code;
        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $product_code;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_num", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_num;
        /**
         * @var string
         *
         * @ORM\Column(name="reason_returns_code", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $reason_returns_code;
        /**
         * @var string
         *
         * @ORM\Column(name="customer_comment", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $customer_comment;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_reviews_flag", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_reviews_flag;
        /**
         * @var string
         *
         * @ORM\Column(name="returns_num", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $returns_num;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path1", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path1;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path2", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path2;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path3", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path3;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path4", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path4;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path5", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path5;
        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path6", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $cus_image_url_path6;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path1", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path1;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path2", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path2;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path3", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path3;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path4", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path4;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path5", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path5;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path6", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_image_url_path6;
        /**
         * @var string
         *
         * @ORM\Column(name="returns_request_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $returns_request_date;
        /**
         * @var string
         *
         * @ORM\Column(name="returns_status_flag", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $returns_status_flag;
        /**
         * @var string
         *
         * @ORM\Column(name="stock_reviews_flag", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $stock_reviews_flag;
        /**
         * @var string
         *
         * @ORM\Column(name="reviews_comment", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $reviews_comment;
        /**
         * @var string
         *
         * @ORM\Column(name="aprove_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $aprove_date;
        /**
         * @var string
         *
         * @ORM\Column(name="shipping_fee", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $shipping_fee;
        /**
         * @var string
         *
         * @ORM\Column(name="aprove_date_not_yet", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $aprove_date_not_yet;
        /**
         * @var string
         *
         * @ORM\Column(name="aprove_comment", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $aprove_comment;
        /**
         * @var string
         *
         * @ORM\Column(name="product_receipt_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $product_receipt_date;
        /**
         * @var string
         *
         * @ORM\Column(name="product_receipt_date_not_yet", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $product_receipt_date_not_yet;
        /**
         * @var string
         *
         * @ORM\Column(name="receipt_not_yet_comment", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $receipt_not_yet_comment;
        /**
         * @var string
         *
         * @ORM\Column(name="returned_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $returned_date;
        /**
         * @var string
         *
         * @ORM\Column(name="xbj_reviews_flag", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $xbj_reviews_flag;
        /**
         * @var string
         *
         * @ORM\Column(name="create_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $create_date;
        /**
         * @var string
         *
         * @ORM\Column(name="update_date", type="string", length=4,options={"comment":"出荷先コード khohang"}, nullable=false)
         * @ORM\Id
         */
        private $update_date;
    }
}
