<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtCustomerRelation', false)) {
    /**
     * DtCustomerRelation
     *
     * @ORM\Table(name="dt_customer_relation")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtCustomerRelationRepository")
     */
    class DtCustomerRelation extends AbstractEntity
    {
 /**
         * @var integer
         *
         * @ORM\Column(name="customer_code", type="integer", options={"unsigned":true})
         * @ORM\Id
         */
        private $customer_code;
        /**
         * @var string
         *
         * @ORM\Column(name="seikyu_code", type="string", length=10,options={"comment":"請求先コード noi nhan hoa don"}, nullable=false)
         * @ORM\Id
         */
        private $seikyu_code;
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
         * @ORM\Column(name="otodoke_code", type="string", length=4,options={"comment":"届け先コード diachinhanhang mstdelivery"}, nullable=false)
         * @ORM\Id
         */
        private $otodoke_code;
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
    }
}
