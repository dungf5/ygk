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

if (!class_exists('\Customize\Entity\DtExportCSV', false)) {
    /**
     * DtExportCSV
     *
     * @ORM\Table(name="dt_export_csv")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtExportCsvRepository")
     */
    class DtExportCSV extends AbstractEntity
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
         * @ORM\Column(name="file_name",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $file_name;
        /**
         * @var string
         *
         * @ORM\Column(name="directory",nullable=true, type="string", length=255, options={"comment":""})
         */
        private $directory;
        /**
         * @var string
         *
         * @ORM\Column(name="message",nullable=true, type="text", options={"comment":""})
         */
        private $message;
        /**
         * @ORM\Column(name="is_error",type="integer",nullable=true, options={"comment":"" ,"default":0 })
         */
        private $is_error;
        /**
         * @ORM\Column(name="is_send_mail",type="integer",nullable=true, options={"comment":"" ,"default":0 })
         */
        private $is_send_mail;
        /**
         * @var \DateTime
         *
         * @ORM\Column(name="in_date", type="datetimetz", nullable=false, options={"comment":""})
         */
        private $in_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="up_date", type="datetimetz", nullable=false, options={"comment":""})
         */
        private $up_date;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param int $id
         */
        public function setId(int $id)
        {
            $this->id = $id;
        }

        /**
         * @return string
         */
        public function getFileName()
        {
            return $this->file_name;
        }

        /**
         * @param $file_name
         */
        public function setFileName($file_name)
        {
            $this->file_name = $file_name;
        }

        /**
         * @return string
         */
        public function getDirectory()
        {
            return $this->directory;
        }

        /**
         * @param $directory
         */
        public function setDirectory($directory)
        {
            $this->directory = $directory;
        }

        /**
         * @return mixed
         */
        public function getIsError()
        {
            return $this->is_error;
        }

        /**
         * @param mixed $is_error
         */
        public function setIsError($is_error)
        {
            $this->is_error = $is_error;
        }

        /**
         * @return mixed
         */
        public function getIsSendMail()
        {
            return $this->is_send_mail;
        }

        /**
         * @param mixed $is_send_mail
         */
        public function setIsSendMail($is_send_mail)
        {
            $this->is_send_mail = $is_send_mail;
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
         * @return \DateTime
         */
        public function getInDate(): \DateTime
        {
            return $this->in_date;
        }

        /**
         * @param \DateTime $in_date
         */
        public function setInDate(\DateTime $in_date): void
        {
            $this->in_date = $in_date;
        }

        /**
         * @return \DateTime
         */
        public function getUpDate(): \DateTime
        {
            return $this->up_date;
        }

        /**
         * @param \DateTime $up_date
         */
        public function setUpDate(\DateTime $up_date): void
        {
            $this->up_date = $up_date;
        }
        
    }
}
