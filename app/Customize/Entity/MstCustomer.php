<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\MstCustomer', false)) {
    /**
     * MstCustomer
     *
     * @ORM\Table(name="mst_customer")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstCustomerRepository")
     */
    class MstCustomer extends AbstractEntity
    {
        /**
         * @var string
         *
         * @ORM\Column(name="customer_code", type="string", length=25,options={"comment":"顧客コード"}, nullable=true)
         * @ORM\Id
         */
        private $customer_code;

        /**
         * @ORM\Column(name="ec_customer_id",type="integer",nullable=true, options={"comment":"" ,"default":1 })
         */
        private $ec_customer_id;
        /**
         * @var string
         *
         * @ORM\Column(name="customer_name",nullable=true, type="string", length=50, options={"comment":"取引先担当者"})
         */
        private $customer_name;
        /**
         * @var string
         *
         * @ORM\Column(name="company_name",nullable=true, type="string", length=80, options={"comment":"会社名"})
         */
        private $company_name;
        /**
         * @var string
         *
         * @ORM\Column(name="company_name_abb",nullable=true, type="string", length=50, options={"comment":"会社略称"})
         */
        private $company_name_abb;
        /**
         * @var string
         *
         * @ORM\Column(name="department",nullable=true, type="string", length=50, options={"comment":"部署"})
         */
        private $department;
        /**
         * @var string
         *
         * @ORM\Column(name="postal_code",nullable=true, type="string", length=8, options={"comment":"郵便番号"})
         */
        private $postal_code;
        /**
         * @var string
         *
         * @ORM\Column(name="addr01",nullable=true, type="string", length=50, options={"comment":"住所1"})
         */
        private $addr01;
        /**
         * @var string
         *
         * @ORM\Column(name="addr02",nullable=true, type="string", length=50, options={"comment":"住所2"})
         */
        private $addr02;
        /**
         * @var string
         *
         * @ORM\Column(name="addr03",nullable=true, type="string", length=50, options={"comment":"住所3"})
         */
        private $addr03;
        /**
         * @var string
         *
         * @ORM\Column(name="email",nullable=true, type="string", length=50, options={"comment":"Email"})
         */
        private $email;
        /**
         * @var string
         *
         * @ORM\Column(name="phone_number",nullable=true, type="string", length=15, options={"comment":"TEL"})
         */
        private $phone_number;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ登録日時'")
         */
        private $create_date;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'データ更新日時'")
         */
        private $update_date;
        /**
         * @var int
         *
         * @ORM\Column(name="special_order_flg", type="integer", nullable=true)
         */
        private $special_order_flg;

        /**
         * @return string
         */
        public function getCustomerCode()
        {
            return $this->customer_code;
        }

        /**
         * @param string $customer_code
         */
        public function setCustomerCode(string $customer_code)
        {
            $this->customer_code = $customer_code;
        }

        /**
         * @return mixed
         */
        public function getEcCustomerId()
        {
            return $this->ec_customer_id;
        }

        /**
         * @param mixed $ec_customer_id
         */
        public function setEcCustomerId($ec_customer_id)
        {
            $this->ec_customer_id = $ec_customer_id;
        }

        /**
         * @return string
         */
        public function getCustomerName()
        {
            return $this->customer_name;
        }

        /**
         * @param string $customer_name
         */
        public function setCustomerName(string $customer_name)
        {
            $this->customer_name = $customer_name;
        }

        /**
         * @return string
         */
        public function getCompanyName()
        {
            return $this->company_name;
        }

        /**
         * @param string $company_name
         */
        public function setCompanyName(string $company_name)
        {
            $this->company_name = $company_name;
        }

        /**
         * @return string
         */
        public function getCompanyNameAbb()
        {
            return $this->company_name_abb;
        }

        /**
         * @param string $company_name_abb
         */
        public function setCompanyNameAbb(string $company_name_abb)
        {
            $this->company_name_abb = $company_name_abb;
        }

        /**
         * @return string
         */
        public function getDepartment()
        {
            return $this->department;
        }

        /**
         * @param string $department
         */
        public function setDepartment(string $department)
        {
            $this->department = $department;
        }

        /**
         * @return string
         */
        public function getPostalCode()
        {
            return $this->postal_code;
        }

        /**
         * @param string $postal_code
         */
        public function setPostalCode(string $postal_code)
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
         * @param string $addr01
         */
        public function setAddr01(string $addr01)
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
         * @param string $addr02
         */
        public function setAddr02(string $addr02)
        {
            $this->addr02 = $addr02;
        }

        /**
         * @return string
         */
        public function getAddr03()
        {
            return $this->addr03;
        }

        /**
         * @param string $addr03
         */
        public function setAddr03(string $addr03)
        {
            $this->addr03 = $addr03;
        }

        /**
         * @return string
         */
        public function getEmail()
        {
            return $this->email;
        }

        /**
         * @param string $email
         */
        public function setEmail(string $email)
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
         * @param string $phone_number
         */
        public function setPhoneNumber(string $phone_number)
        {
            $this->phone_number = $phone_number;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * @param $create_date
         */
        public function setCreateDate($create_date)
        {
            $this->create_date = $create_date;
        }

        /**
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }

        /**
         * @param $update_date
         */
        public function setUpdateDate($update_date)
        {
            $this->update_date = $update_date;
        }

        /**
         * @return int
         */
        public function getSpecialOrderFlg(): int
        {
            return $this->special_order_flg;
        }

        /**
         * @param int $special_order_flg
         */
        public function setSpecialOrderFlg(int $special_order_flg)
        {
            $this->special_order_flg = $special_order_flg;
        }

    }
}
