<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\Order', false)) {
    /**
     * Order
     *
     * @ORM\Table(name="dtb_order")
     * @ORM\Entity(repositoryClass="Customize\Repository\OrderRepository")
     */
    class Order extends AbstractEntity
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
         * @var float
         *
         * @ORM\Column(name="payment_total", type="decimal", precision=12, scale=2, options={"unsigned":true,"default":0})
         */
        private $payment_total = 0;

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
         * @var \Eccube\Entity\Customer
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer", inversedBy="Orders")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
         * })
         */
        private $Customer;

        /**
         * @var string
         *
         * @ORM\Column(name="discriminator_type",nullable=false, type="string", length=255, options={"comment":""})
         */
        private $discriminator_type;


        /**
         * @return int
         */
        public function getId(): int
        {
            return $this->id;
        }

        /**
         * @param int $id
         */
        public function setId(int $id): void
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
        public function setCustomerId($customer_id): void
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
        public function setCountryId($country_id): void
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
        public function setPrefId($pref_id): void
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
        public function setSexId($sex_id): void
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
        public function setJobId($job_id): void
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
        public function setPaymentId($payment_id): void
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
        public function setDeviceTypeId($device_type_id): void
        {
            $this->device_type_id = $device_type_id;
        }

        /**
         * @return string
         */
        public function getPreOrderId(): string
        {
            return $this->pre_order_id;
        }

        /**
         * @param string $pre_order_id
         */
        public function setPreOrderId(string $pre_order_id): void
        {
            $this->pre_order_id = $pre_order_id;
        }

        /**
         * @return string
         */
        public function getOrderNo(): string
        {
            return $this->order_no;
        }

        /**
         * @param string $order_no
         */
        public function setOrderNo(string $order_no): void
        {
            $this->order_no = $order_no;
        }

        /**
         * @return string
         */
        public function getMessage(): string
        {
            return $this->message;
        }

        /**
         * @param string $message
         */
        public function setMessage(string $message): void
        {
            $this->message = $message;
        }

        /**
         * @return string
         */
        public function getName01(): string
        {
            return $this->name01;
        }

        /**
         * @param string $name01
         */
        public function setName01(string $name01): void
        {
            $this->name01 = $name01;
        }

        /**
         * @return string
         */
        public function getName02(): string
        {
            return $this->name02;
        }

        /**
         * @param string $name02
         */
        public function setName02(string $name02): void
        {
            $this->name02 = $name02;
        }

        /**
         * @return string
         */
        public function getKana01(): string
        {
            return $this->kana01;
        }

        /**
         * @param string $kana01
         */
        public function setKana01(string $kana01): void
        {
            $this->kana01 = $kana01;
        }

        /**
         * @return string
         */
        public function getKana02(): string
        {
            return $this->kana02;
        }

        /**
         * @param string $kana02
         */
        public function setKana02(string $kana02): void
        {
            $this->kana02 = $kana02;
        }

        /**
         * @return string
         */
        public function getCompanyName(): string
        {
            return $this->company_name;
        }

        /**
         * @param string $company_name
         */
        public function setCompanyName(string $company_name): void
        {
            $this->company_name = $company_name;
        }

        /**
         * @return string
         */
        public function getEmail(): string
        {
            return $this->email;
        }

        /**
         * @param string $email
         */
        public function setEmail(string $email): void
        {
            $this->email = $email;
        }

        /**
         * @return string
         */
        public function getPhoneNumber(): string
        {
            return $this->phone_number;
        }

        /**
         * @param string $phone_number
         */
        public function setPhoneNumber(string $phone_number): void
        {
            $this->phone_number = $phone_number;
        }

        /**
         * @return string
         */
        public function getPostalCode(): string
        {
            return $this->postal_code;
        }

        /**
         * @param string $postal_code
         */
        public function setPostalCode(string $postal_code): void
        {
            $this->postal_code = $postal_code;
        }

        /**
         * @return string
         */
        public function getAddr01(): string
        {
            return $this->addr01;
        }

        /**
         * @param string $addr01
         */
        public function setAddr01(string $addr01): void
        {
            $this->addr01 = $addr01;
        }

        /**
         * @return string
         */
        public function getAddr02(): string
        {
            return $this->addr02;
        }

        /**
         * @param string $addr02
         */
        public function setAddr02(string $addr02): void
        {
            $this->addr02 = $addr02;
        }

        /**
         * @return string
         */
        public function getPaymentMethod(): string
        {
            return $this->payment_method;
        }

        /**
         * @param string $payment_method
         */
        public function setPaymentMethod(string $payment_method): void
        {
            $this->payment_method = $payment_method;
        }

        /**
         * @return string
         */
        public function getNote(): string
        {
            return $this->note;
        }

        /**
         * @param string $note
         */
        public function setNote(string $note): void
        {
            $this->note = $note;
        }

        /**
         * @return string
         */
        public function getCurrencyCode(): string
        {
            return $this->currency_code;
        }

        /**
         * @param string $currency_code
         */
        public function setCurrencyCode(string $currency_code): void
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
        public function setOrderStatusId($order_status_id): void
        {
            $this->order_status_id = $order_status_id;
        }

        /**
         * @return string
         */
        public function getDiscriminatorType(): string
        {
            return $this->discriminator_type;
        }

        /**
         * @param string $discriminator_type
         */
        public function setDiscriminatorType(string $discriminator_type): void
        {
            $this->discriminator_type = $discriminator_type;
        }
        /**
         * Set paymentTotal.
         *
         * @param string $paymentTotal
         *

         */
        public function setPaymentTotal($paymentTotal)
        {
            $this->payment_total = $paymentTotal;


        }

        /**
         * Get paymentTotal.
         *
         * @return float
         */
        public function getPaymentTotal()
        {
            return $this->payment_total;
        }
    }
}
