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

if (!class_exists('\Customize\Entity\DtbOrderDaitoTest', false)) {
    /**
     * DtbOrderDaitoTest
     *
     * @ORM\Table(name="dtb_order_daito_test")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtbOrderDaitoTestRepository")
     */
    class DtbOrderDaitoTest extends AbstractEntity
    {
        /**
         * @var integer
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;
        /**
         * @ORM\Column(name="customer_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $customer_id;
        /**
         * @ORM\Column(name="country_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $country_id;
        /**
         * @ORM\Column(name="pref_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $pref_id;
        /**
         * @ORM\Column(name="sex_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $sex_id;
        /**
         * @ORM\Column(name="job_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $job_id;
        /**
         * @ORM\Column(name="payment_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $payment_id;
        /**
         * @ORM\Column(name="device_type_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $device_type_id;
        /**
         * @var string
         *
         * @ORM\Column(name="pre_order_id",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $pre_order_id;
        /**
         * @var string
         *
         * @ORM\Column(name="order_no",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $order_no;
        /**
         * @var string
         *
         * @ORM\Column(name="message",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $message;
        /**
         * @var string
         *
         * @ORM\Column(name="name01",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $name01;
        /**
         * @var string
         *
         * @ORM\Column(name="name02",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $name02;
        /**
         * @var string
         *
         * @ORM\Column(name="kana01",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $kana01;
        /**
         * @var string
         *
         * @ORM\Column(name="kana02",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $kana02;
        /**
         * @var string
         *
         * @ORM\Column(name="company_name",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $company_name;
        /**
         * @var string
         *
         * @ORM\Column(name="email",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $email;
        /**
         * @var string
         *
         * @ORM\Column(name="phone_number",nullable=true, type="string", length=14, options={"comment":""})
         */
        private $phone_number;
        /**
         * @var string
         *
         * @ORM\Column(name="postal_code",nullable=true, type="string", length=8, options={"comment":""})
         */
        private $postal_code;
        /**
         * @var string
         *
         * @ORM\Column(name="addr01",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $addr01;
        /**
         * @var string
         *
         * @ORM\Column(name="addr02",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $addr02;
        /**
         * @var string
         *
         * @ORM\Column(name="payment_method",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $payment_method;
        /**
         * @var string
         *
         * @ORM\Column(name="note",nullable=true, type="string", length=4000, options={"comment":""})
         */
        private $note;
        /**
         * @var string
         *
         * @ORM\Column(name="currency_code",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $currency_code;
        /**
         * @ORM\Column(name="order_status_id",type="integer",nullable=true, options={"comment":""  })
         */
        private $order_status_id;
        /**
         * @var string
         *
         * @ORM\Column(name="discriminator_type",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $discriminator_type;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param $id
         */
        public function setId($id)
        {
            $this->id = $id;
        }

        /**
         * @return mixed
         */
        public function getCustomerId()
        {
            return $this->customer_id;
        }

        /**
         * @param mixed $customer_id
         */
        public function setCustomerId($customer_id)
        {
            $this->customer_id = $customer_id;
        }

        /**
         * @return mixed
         */
        public function getCountryId()
        {
            return $this->country_id;
        }

        /**
         * @param mixed $country_id
         */
        public function setCountryId($country_id)
        {
            $this->country_id = $country_id;
        }

        /**
         * @return mixed
         */
        public function getPrefId()
        {
            return $this->pref_id;
        }

        /**
         * @param mixed $pref_id
         */
        public function setPrefId($pref_id)
        {
            $this->pref_id = $pref_id;
        }

        /**
         * @return mixed
         */
        public function getSexId()
        {
            return $this->sex_id;
        }

        /**
         * @param mixed $sex_id
         */
        public function setSexId($sex_id)
        {
            $this->sex_id = $sex_id;
        }

        /**
         * @return mixed
         */
        public function getJobId()
        {
            return $this->job_id;
        }

        /**
         * @param mixed $job_id
         */
        public function setJobId($job_id)
        {
            $this->job_id = $job_id;
        }

        /**
         * @return mixed
         */
        public function getPaymentId()
        {
            return $this->payment_id;
        }

        /**
         * @param mixed $payment_id
         */
        public function setPaymentId($payment_id)
        {
            $this->payment_id = $payment_id;
        }

        /**
         * @return mixed
         */
        public function getDeviceTypeId()
        {
            return $this->device_type_id;
        }

        /**
         * @param mixed $device_type_id
         */
        public function setDeviceTypeId($device_type_id)
        {
            $this->device_type_id = $device_type_id;
        }

        /**
         * @return string
         */
        public function getPreOrderId()
        {
            return $this->pre_order_id;
        }

        /**
         * @param $pre_order_id
         */
        public function setPreOrderId($pre_order_id)
        {
            $this->pre_order_id = $pre_order_id;
        }

        /**
         * @return string
         */
        public function getOrderNo()
        {
            return $this->order_no;
        }

        /**
         * @param $order_no
         */
        public function setOrderNo($order_no)
        {
            $this->order_no = $order_no;
        }

        /**
         * @return string
         */
        public function getMessage()
        {
            return $this->message;
        }

        /**
         * @param $message
         */
        public function setMessage($message)
        {
            $this->message = $message;
        }

        /**
         * @return string
         */
        public function getName01()
        {
            return $this->name01;
        }

        /**
         * @param $name01
         */
        public function setName01($name01)
        {
            $this->name01 = $name01;
        }

        /**
         * @return string
         */
        public function getName02()
        {
            return $this->name02;
        }

        /**
         * @param $name02
         */
        public function setName02($name02)
        {
            $this->name02 = $name02;
        }

        /**
         * @return string
         */
        public function getKana01()
        {
            return $this->kana01;
        }

        /**
         * @param $kana01
         */
        public function setKana01($kana01)
        {
            $this->kana01 = $kana01;
        }

        /**
         * @return string
         */
        public function getKana02()
        {
            return $this->kana02;
        }

        /**
         * @param $kana02
         */
        public function setKana02($kana02)
        {
            $this->kana02 = $kana02;
        }

        /**
         * @return string
         */
        public function getCompanyName()
        {
            return $this->company_name;
        }

        /**
         * @param $company_name
         */
        public function setCompanyName($company_name)
        {
            $this->company_name = $company_name;
        }

        /**
         * @return string
         */
        public function getEmail()
        {
            return $this->email;
        }

        /**
         * @param $email
         */
        public function setEmail($email)
        {
            $this->email = $email;
        }

        /**
         * @return string
         */
        public function getPhoneNumber()
        {
            return $this->phone_number;
        }

        /**
         * @param $phone_number
         */
        public function setPhoneNumber($phone_number)
        {
            $this->phone_number = $phone_number;
        }

        /**
         * @return string
         */
        public function getPostalCode()
        {
            return $this->postal_code;
        }

        /**
         * @param $postal_code
         */
        public function setPostalCode($postal_code)
        {
            $this->postal_code = $postal_code;
        }

        /**
         * @return string
         */
        public function getAddr01()
        {
            return $this->addr01;
        }

        /**
         * @param $addr01
         */
        public function setAddr01($addr01)
        {
            $this->addr01 = $addr01;
        }

        /**
         * @return string
         */
        public function getAddr02()
        {
            return $this->addr02;
        }

        /**
         * @param $addr02
         */
        public function setAddr02($addr02)
        {
            $this->addr02 = $addr02;
        }

        /**
         * @return string
         */
        public function getPaymentMethod()
        {
            return $this->payment_method;
        }

        /**
         * @param $payment_method
         */
        public function setPaymentMethod($payment_method)
        {
            $this->payment_method = $payment_method;
        }

        /**
         * @return string
         */
        public function getNote()
        {
            return $this->note;
        }

        /**
         * @param $note
         */
        public function setNote($note)
        {
            $this->note = $note;
        }

        /**
         * @return string
         */
        public function getCurrencyCode()
        {
            return $this->currency_code;
        }

        /**
         * @param $currency_code
         */
        public function setCurrencyCode($currency_code)
        {
            $this->currency_code = $currency_code;
        }

        /**
         * @return mixed
         */
        public function getOrderStatusId()
        {
            return $this->order_status_id;
        }

        /**
         * @param mixed $order_status_id
         */
        public function setOrderStatusId($order_status_id)
        {
            $this->order_status_id = $order_status_id;
        }

        /**
         * @return string
         */
        public function getDiscriminatorType()
        {
            return $this->discriminator_type;
        }

        /**
         * @param $discriminator_type
         */
        public function setDiscriminatorType($discriminator_type)
        {
            $this->discriminator_type = $discriminator_type;
        }
    }
}
