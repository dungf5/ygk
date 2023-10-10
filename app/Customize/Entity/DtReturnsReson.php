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

if (!class_exists('\Customize\Entity\DtReturnsReson', false)) {
    /**
     * DtReturnsReson.php
     *
     * @ORM\Table(name="dt_returns_reson")
     * @ORM\Entity(repositoryClass="Customize\Repository\DtReturnsReson")
     */
    class DtReturnsReson extends AbstractEntity
    {
        /**
         * @var integer
         * @ORM\Column(name="returns_reson_id", type="string", nullable=false)
         * @ORM\Id
         */
        private $returns_reson_id;

        /**
         * @var string
         *
         * @ORM\Column(name="returns_reson", type="string")
         */
        private $returns_reson;

        /**
         * @return string
         */
        public function getReturnsReson(): string
        {
            return $this->returns_reson;
        }

        /**
         * @param string $returns_reson
         */
        public function setReturnsReson(string $returns_reson): void
        {
            $this->returns_reson = $returns_reson;
        }
    }
}
