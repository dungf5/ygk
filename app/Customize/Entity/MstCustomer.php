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
         * @return mixed
         */
        public function getEcCustomerId()
        {
            return $this->ec_customer_id;
        }

        /**
         * @param mixed $ec_customer_id
         */
        public function setEcCustomerId($ec_customer_id): void
        {
            $this->ec_customer_id = $ec_customer_id;
        }
    }
}
