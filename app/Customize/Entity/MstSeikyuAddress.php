<?php
namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\MstSeikyuAddress', false)) {
    /**
     * MstSeikyuAddress
     *
     * @ORM\Table(name="mst_seikyu_address")
     * @ORM\Entity(repositoryClass="Customize\Repository\MstSeikyuAddressRepository")
     */
    class MstSeikyuAddress extends AbstractEntity
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
         * @var string
         *
         * @ORM\Column(name="postal_code",nullable=true, type="string", length=8, options={"comment":"郵便番号"})
         */
        private $postal_code;
/**
         * @var string
         *
         * @ORM\Column(name="seikyu_code",nullable=true, type="string", length=10, options={"comment":""})
         */
        private $seikyu_code;
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
