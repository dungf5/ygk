<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

if (!class_exists('\Customize\Entity\DtImportCSV', false)) {
    /**
     * DtImportCSV
     *
     * @ORM\Table(name="dt_import_csv")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtImportCsvRepository")
     */
    class DtImportCSV extends AbstractEntity
    {
        /**
         * @var integer
         * @ORM\Column(name="id", type="integer", nullable=false)
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
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
         * @var string|null
         *
         * @ORM\Column(name="message", type="text",options={"comment":""}, nullable=true)
         */
        private $message;

        /**
         * @var integer
         *
         * @ORM\Column(name="is_sync", type="integer", length=10, nullable=false, options={"comment":""})
         */
        private $is_sync;

        /**
         * @var integer
         *
         * @ORM\Column(name="is_error", type="integer", length=10, nullable=false, options={"comment":""})
         */
        private $is_error;

        /**
         * @var integer
         *
         * @ORM\Column(name="is_send_mail", type="integer", length=10, nullable=false, options={"comment":""})
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
         * @return string|null
         */
        public function getFileName()
        {
            return $this->file_name;
        }

        /**
         * @param string|null $file_name
         */
        public function setFileName($file_name = null): void
        {
            $this->file_name = $file_name;
        }

        /**
         * @return string|null
         */
        public function getDirectory()
        {
            return $this->directory;
        }

        /**
         * @param string|null $directory
         */
        public function setDirectory($directory = null): void
        {
            $this->directory = $directory;
        }


        /**
         * Get message.
         *
         * @return string|null
         */
        public function getMessage()
        {
            return $this->message;
        }

        /**
         * Set message.
         *
         * @param string|null $message
         */
        public function setMessage($message = null): void
        {
            $this->message = $message;
        }

        /**
         * @return integer|null
         */
        public function getIsSync()
        {
            return $this->is_sync;
        }

        /**
         * @param integer|null $is_sync
         */
        public function setIsSync($is_sync = null): void
        {
            $this->is_sync = $is_sync;
        }

        /**
         * @return integer|null
         */
        public function getIsError()
        {
            return $this->is_error;
        }

        /**
         * @param integer|null $is_error
         */
        public function setIsError($is_error = null): void
        {
            $this->is_error = $is_error;
        }

        /**
         * @return integer|null
         */
        public function getIsSendMail()
        {
            return $this->is_send_mail;
        }

        /**
         * @param integer|null $is_send_mail
         */
        public function setIsSendMail($is_send_mail = null): void
        {
            $this->is_send_mail = $is_send_mail;
        }

        /**
         * @return \DateTime|null
         */
        public function getInDate()
        {
            return $this->in_date;
        }

        /**
         * @param \DateTime|null $in_date
         */
        public function setInDate($in_date = null): void
        {
            $this->in_date = $in_date;
        }

        /**
         * @return \DateTime|null
         */
        public function getUpDate()
        {
            return $this->up_date;
        }

        /**
         * @param \DateTime|null $up_date
         */
        public function setUpDate($up_date = null): void
        {
            $this->up_date = $up_date;
        }
    }
}
