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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\Date;

if (!class_exists('\Customize\Entity\MstProductReturnsInfo', false)) {
    /**
     * MstProductReturnsInfo.php
     *
     * @ORM\Table(name="mst_product_returns_info")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstProductReturnsInfo")
     */
    class MstProductReturnsInfo extends AbstractEntity
    {
        /**
         * @var integer
         * @ORM\Column(name="returns_no", type="string", nullable=false)
         * @ORM\Id
         */
        private $returns_no;

        /**
         * @return string
         */
        public function getReturnsNo(): string
        {
            return $this->returns_no;
        }

        /**
         * @param string $returns_no
         */
        public function setReturnsNo(string $returns_no): void
        {
            $this->returns_no = $returns_no;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string")
         */
        private $customer_code;

        /**
         * @return string
         */
        public function getCustomerCode(): ?string
        {
            return $this->customer_code;
        }

        /**
         * @param string $customer_code
         */
        public function setCustomerCode(string $customer_code): void
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_code", type="string")
         */
        private $shipping_code;

        /**
         * @return string
         */
        public function getShippingCode(): ?string
        {
            return $this->shipping_code;
        }

        /**
         * @param string $shipping_code
         */
        public function setShippingCode(string $shipping_code): void
        {
            $this->shipping_code = $shipping_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_name", type="string")
         */
        private $shipping_name;

        /**
         * @return string
         */
        public function getShippingName(): ?string
        {
            return $this->shipping_name;
        }

        /**
         * @param string $shipping_name
         */
        public function setShippingName(string $shipping_name): void
        {
            $this->shipping_name = $shipping_name;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_code", type="string")
         */
        private $otodoke_code;

        /**
         * @return string
         */
        public function getOtodokeCode(): ?string
        {
            return $this->otodoke_code;
        }

        /**
         * @param string $otodoke_code
         */
        public function setOtodokeCode(string $otodoke_code): void
        {
            $this->otodoke_code = $otodoke_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="otodoke_name", type="string")
         */
        private $otodoke_name;

        /**
         * @return string
         */
        public function getOtodokeName(): ?string
        {
            return $this->otodoke_name;
        }

        /**
         * @param string $otodoke_name
         */
        public function setOtodokeName(string $otodoke_name): void
        {
            $this->otodoke_name = $otodoke_name;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_no", type="string")
         */
        private $shipping_no;

        /**
         * @return string
         */
        public function getShippingNo(): ?string
        {
            return $this->shipping_no;
        }

        /**
         * @param string $shipping_no
         */
        public function setShippingNo(string $shipping_no): void
        {
            $this->shipping_no = $shipping_no;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_date", type="string")
         */
        private $shipping_date;

        /**
         * @return string
         */
        public function getShippingDate(): ?string
        {
            return $this->shipping_date;
        }

        /**
         * @param string $shipping_date
         */
        public function setShippingDate(string $shipping_date): void
        {
            $this->shipping_date = $shipping_date;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="jan_code", type="string")
         */
        private $jan_code;

        /**
         * @return string
         */
        public function getJanCode(): ?string
        {
            return $this->jan_code;
        }

        /**
         * @param string $jan_code
         */
        public function setJanCode(string $jan_code): void
        {
            $this->jan_code = $jan_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="product_code", type="string")
         */
        private $product_code;

        /**
         * @return string
         */
        public function getProductCode(): ?string
        {
            return $this->product_code;
        }

        /**
         * @param string $product_code
         */
        public function setProductCode(string $product_code): void
        {
            $this->product_code = $product_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_num", type="string")
         */
        private $shipping_num;

        /**
         * @return string
         */
        public function getShippingNum(): ?string
        {
            return $this->shipping_num;
        }

        /**
         * @param string $shipping_num
         */
        public function setShippingNum(string $shipping_num): void
        {
            $this->shipping_num = $shipping_num;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="reason_returns_code", type="string")
         */
        private $reason_returns_code;

        /**
         * @return string
         */
        public function getReasonReturnsCode(): ?string
        {
            return $this->reason_returns_code;
        }

        /**
         * @param string $reason_returns_code
         */
        public function setReasonReturnsCode(string $reason_returns_code): void
        {
            $this->reason_returns_code = $reason_returns_code;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="customer_comment", type="string")
         */
        private $customer_comment;

        /**
         * @return string
         */
        public function getCustomerComment(): ?string
        {
            return $this->customer_comment;
        }

        /**
         * @param string $customer_comment
         */
        public function setCustomerComment(string $customer_comment): void
        {
            $this->customer_comment = $customer_comment;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_reviews_flag", type="string")
         */
        private $cus_reviews_flag;

        /**
         * @return string
         */
        public function getCusReviewsFlag(): ?string
        {
            return $this->cus_reviews_flag;
        }

        /**
         * @param string $cus_reviews_flag
         */
        public function setCusReviewsFlag(string $cus_reviews_flag): void
        {
            $this->cus_reviews_flag = $cus_reviews_flag;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="returns_num", type="string")
         */
        private $returns_num;

        /**
         * @return string
         */
        public function getReturnsNum(): ?string
        {
            return $this->returns_num;
        }

        /**
         * @param string $returns_num
         */
        public function setReturnsNum(string $returns_num): void
        {
            $this->returns_num = $returns_num;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path1", type="string")
         */
        private $cus_image_url_path1;

        /**
         * @return string
         */
        public function getCusImageUrlPath1(): ?string
        {
            return $this->cus_image_url_path1;
        }

        /**
         * @param string $cus_image_url_path1
         */
        public function setCusImageUrlPath1(?string $cus_image_url_path1): void
        {
            $this->cus_image_url_path1 = $cus_image_url_path1;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path2", type="string")
         */
        private $cus_image_url_path2;

        /**
         * @return string
         */
        public function getCusImageUrlPath2(): ?string
        {
            return $this->cus_image_url_path2;
        }

        /**
         * @param string $cus_image_url_path2
         */
        public function setCusImageUrlPath2(?string $cus_image_url_path2): void
        {
            $this->cus_image_url_path2 = $cus_image_url_path2;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path3", type="string")
         */
        private $cus_image_url_path3;

        /**
         * @return string
         */
        public function getCusImageUrlPath3(): ?string
        {
            return $this->cus_image_url_path3;
        }

        /**
         * @param string $cus_image_url_path2
         */
        public function setCusImageUrlPath3(?string $cus_image_url_path3): void
        {
            $this->cus_image_url_path3 = $cus_image_url_path3;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path4", type="string")
         */
        private $cus_image_url_path4;

        /**
         * @return string
         */
        public function getCusImageUrlPath4(): ?string
        {
            return $this->cus_image_url_path4;
        }

        /**
         * @param string $cus_image_url_path4
         */
        public function setCusImageUrlPath4(?string $cus_image_url_path4): void
        {
            $this->cus_image_url_path4 = $cus_image_url_path4;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path5", type="string")
         */
        private $cus_image_url_path5;

        /**
         * @return string
         */
        public function getCusImageUrlPath5(): ?string
        {
            return $this->cus_image_url_path5;
        }

        /**
         * @param string $cus_image_url_path5
         */
        public function setCusImageUrlPath5(?string $cus_image_url_path5): void
        {
            $this->cus_image_url_path5 = $cus_image_url_path5;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_image_url_path6", type="string")
         */
        private $cus_image_url_path6;

        /**
         * @return string
         */
        public function getCusImageUrlPath6(): ?string
        {
            return $this->cus_image_url_path6;
        }

        /**
         * @param string $cus_image_url_path6
         */
        public function setCusImageUrlPath6(?string $cus_image_url_path6): void
        {
            $this->cus_image_url_path6 = $cus_image_url_path6;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path1", type="string")
         */
        private $stock_image_url_path1;

        /**
         * @return string
         */
        public function getStockImageUrlPath1(): ?string
        {
            return $this->stock_image_url_path1;
        }

        /**
         * @param string $stock_image_url_path1
         */
        public function setStockImageUrlPath1(string $stock_image_url_path1): void
        {
            $this->stock_image_url_path1 = $stock_image_url_path1;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path2", type="string")
         */
        private $stock_image_url_path2;

        /**
         * @return string
         */
        public function getStockImageUrlPath2(): ?string
        {
            return $this->stock_image_url_path2;
        }

        /**
         * @param string $stock_image_url_path2
         */
        public function setStockImageUrlPath2(string $stock_image_url_path2): void
        {
            $this->stock_image_url_path2 = $stock_image_url_path2;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path3", type="string")
         */
        private $stock_image_url_path3;

        /**
         * @return string
         */
        public function getStockImageUrlPath3(): ?string
        {
            return $this->stock_image_url_path3;
        }

        /**
         * @param string $stock_image_url_path3
         */
        public function setStockImageUrlPath3(string $stock_image_url_path3): void
        {
            $this->stock_image_url_path3 = $stock_image_url_path3;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path4", type="string")
         */
        private $stock_image_url_path4;

        /**
         * @return string
         */
        public function getStockImageUrlPath4(): ?string
        {
            return $this->stock_image_url_path4;
        }

        /**
         * @param string $stock_image_url_path4
         */
        public function setStockImageUrlPath4(string $stock_image_url_path4): void
        {
            $this->stock_image_url_path4 = $stock_image_url_path4;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path5", type="string")
         */
        private $stock_image_url_path5;

        /**
         * @return string
         */
        public function getStockImageUrlPath5(): ?string
        {
            return $this->stock_image_url_path5;
        }

        /**
         * @param string $stock_image_url_path5
         */
        public function setStockImageUrlPath5(string $stock_image_url_path5): void
        {
            $this->stock_image_url_path5 = $stock_image_url_path5;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_image_url_path6", type="string")
         */
        private $stock_image_url_path6;

        /**
         * @return string
         */
        public function getStockImageUrlPath6(): ?string
        {
            return $this->stock_image_url_path6;
        }

        /**
         * @param string $stock_image_url_path6
         */
        public function setStockImageUrlPath6(string $stock_image_url_path6): void
        {
            $this->stock_image_url_path6 = $stock_image_url_path6;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="returns_request_date", type="string")
         */
        private $returns_request_date;

        /**
         * @return string
         */
        public function getReturnsRequestDate(): ?string
        {
            return $this->returns_request_date;
        }

        /**
         * @param string $returns_request_date
         */
        public function setReturnsRequestDate(string $returns_request_date): void
        {
            $this->returns_request_date = $returns_request_date;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="returns_status_flag", type="string")
         */
        private $returns_status_flag;

        /**
         * @return string
         */
        public function getReturnsStatusFlag(): ?string
        {
            return $this->returns_status_flag;
        }

        /**
         * @param string $returns_status_flag
         */
        public function setReturnsStatusFlag(string $returns_status_flag): void
        {
            $this->returns_status_flag = $returns_status_flag;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="stock_reviews_flag", type="string")
         */
        private $stock_reviews_flag;

        /**
         * @return string
         */
        public function getStockReviewsFlag(): ?string
        {
            return $this->stock_reviews_flag;
        }

        /**
         * @param string $stock_reviews_flag
         */
        public function setStockReviewsFlag(string $stock_reviews_flag): void
        {
            $this->stock_reviews_flag = $stock_reviews_flag;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="reviews_comment", type="string")
         */
        private $reviews_comment;

        /**
         * @return string
         */
        public function getReviewsComment(): ?string
        {
            return $this->reviews_comment;
        }

        /**
         * @param string $reviews_comment
         */
        public function setReviewsComment(string $reviews_comment): void
        {
            $this->reviews_comment = $reviews_comment;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="shipping_fee", type="string")
         */
        private $shipping_fee;

        /**
         * @return string
         */
        public function getShippingFee(): ?string
        {
            return $this->shipping_fee;
        }

        /**
         * @param string $shipping_fee
         */
        public function setShippingFee(string $shipping_fee): void
        {
            $this->shipping_fee = $shipping_fee;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="aprove_date", type="string")
         */
        private $aprove_date;

        /**
         * @return string
         */
        public function getAproveDate(): ?string
        {
            return $this->aprove_date;
        }

        /**
         * @param string $aprove_date
         */
        public function setAproveDate(string $aprove_date): void
        {
            $this->aprove_date = $aprove_date;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="aprove_date_not_yet", type="string")
         */
        private $aprove_date_not_yet;

        /**
         * @return string
         */
        public function getAproveDateNotYet(): ?string
        {
            return $this->aprove_date_not_yet;
        }

        /**
         * @param string $aprove_date_not_yet
         */
        public function setAproveDateNotYet(string $aprove_date_not_yet): void
        {
            $this->aprove_date_not_yet = $aprove_date_not_yet;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="aprove_comment_not_yet", type="string")
         */
        private $aprove_comment_not_yet;

        /**
         * @return string
         */
        public function getAproveCommentNotYet(): ?string
        {
            return $this->aprove_comment_not_yet;
        }

        /**
         * @param string $aprove_comment_not_yet
         */
        public function setAproveCommentNotYet(string $aprove_comment_not_yet): void
        {
            $this->aprove_comment_not_yet = $aprove_comment_not_yet;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="product_receipt_date", type="string")
         */
        private $product_receipt_date;

        /**
         * @return string
         */
        public function getProductReceiptDate(): ?string
        {
            return $this->product_receipt_date;
        }

        /**
         * @param string $product_receipt_date
         */
        public function setProductReceiptDate(string $product_receipt_date): void
        {
            $this->product_receipt_date = $product_receipt_date;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="receipt_comment", type="string")
         */
        private $receipt_comment;

        /**
         * @return string
         */
        public function getReceiptComment(): ?string
        {
            return $this->receipt_comment;
        }

        /**
         * @param string $receipt_noreceipt_commentt_yet_comment
         */
        public function setReceiptComment(string $receipt_comment): void
        {
            $this->receipt_comment = $receipt_comment;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="product_receipt_date_not_yet", type="string")
         */
        private $product_receipt_date_not_yet;

        /**
         * @return string
         */
        public function getProductReceiptDateNotYet(): ?string
        {
            return $this->product_receipt_date_not_yet;
        }

        /**
         * @param string $product_receipt_date_not_yet
         */
        public function setProductReceiptDateNotYet(string $product_receipt_date_not_yet): void
        {
            $this->product_receipt_date_not_yet = $product_receipt_date_not_yet;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="receipt_not_yet_comment", type="string")
         */
        private $receipt_not_yet_comment;

        /**
         * @return string
         */
        public function getReceiptNotYetComment(): ?string
        {
            return $this->receipt_not_yet_comment;
        }

        /**
         * @param string $receipt_not_yet_comment
         */
        public function setReceiptNotYetComment(string $receipt_not_yet_comment): void
        {
            $this->receipt_not_yet_comment = $receipt_not_yet_comment;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="returned_date", type="string")
         */
        private $returned_date;

        /**
         * @return string
         */
        public function getReturnedDate(): ?string
        {
            return $this->returned_date;
        }

        /**
         * @param string $returned_date
         */
        public function setReturnedDate(string $returned_date): void
        {
            $this->returned_date = $returned_date;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="xbj_reviews_flag", type="string")
         */
        private $xbj_reviews_flag;

        /**
         * @return string
         */
        public function getXbjReviewsFlag(): ?string
        {
            return $this->xbj_reviews_flag;
        }

        /**
         * @param string $xbj_reviews_flag
         */
        public function setXbjReviewsFlag(string $xbj_reviews_flag): void
        {
            $this->xbj_reviews_flag = $xbj_reviews_flag;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_no", type="string")
         */
        private $cus_order_no;

        /**
         * @return string
         */
        public function getCusOrderNo(): ?string
        {
            return $this->cus_order_no;
        }

        /**
         * @param string $cus_order_no
         */
        public function setCusOrderNo(string $cus_order_no): void
        {
            $this->cus_order_no = $cus_order_no;
        }

        /**
         * @var string
         *
         * @ORM\Column(name="cus_order_lineno", type="string")
         */
        private $cus_order_lineno;

        /**
         * @return string
         */
        public function getCusOrderLineno(): ?string
        {
            return $this->cus_order_lineno;
        }

        /**
         * @param string $cus_order_lineno
         */
        public function setCusOrderLineno(string $cus_order_lineno): void
        {
            $this->cus_order_lineno = $cus_order_lineno;
        }
    }
}
