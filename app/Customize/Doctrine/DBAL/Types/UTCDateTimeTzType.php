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

namespace Customize\Doctrine\DBAL\Types;

use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeTzType;

class UTCDateTimeTzType extends DateTimeTzType
{
    /**
     * UTCのタイムゾーン
     *
     * @var \DateTimeZone
     */
    protected static $utc;

    /**
     * アプリケーションのタイムゾーン
     *
     * @var \DateTimeZone
     */
    protected static $timezone;

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(self::getUtcTimeZone());
        }

        return $this->myConvertToDatabaseValue($value, $platform);
    }
    /**
     * {@inheritdoc}
     */
    public function myConvertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        //nvtrong modify
        if ($value === null || $value instanceof \DateTime) {
            return $value->format("Y-m-d H:i:s.u");
        }
        if ($value instanceof DateTimeInterface) {
            $stringFormat = $platform->getDateTimeTzFormatString();
            if(strpos($value,".") !==false){
                $stringFormat = "Y-m-d H:i:s.u";
            }
            return $stringFormat;
            // return $value->format("Y-m-d H:i:s.u");
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        //nvtrong modify
        if ($value === null || $value instanceof \DateTime) {
            return $value;
        }

        $stringFormat = $platform->getDateTimeTzFormatString();
        if(strpos($value,".") !==false){
            $stringFormat = "Y-m-d H:i:s.u";
        }
        $converted = \DateTime::createFromFormat(
        // $platform->getDateTimeTzFormatString(),
            $stringFormat,
            $value,
            self::getUtcTimeZone()
        );


        if (!$converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeTzFormatString());
        }
        self::setTimeZone();
        $converted->setTimezone(self::getTimezone());

        return $converted;
    }

    /**
     * @return \DateTimeZone
     */
    protected static function getUtcTimeZone()
    {
        if (is_null(self::$utc)) {
            // self::$utc = new \DateTimeZone('Asia/Tokyo');;
            self::$utc = new \DateTimeZone('UTC');;
        }

        return self::$utc;
    }

    /**
     * @return \DateTimeZone
     */
    public static function getTimezone()
    {
        if (is_null(self::$timezone)) {
            throw new \LogicException(sprintf('%s::$timezone is undefined.', self::class));
        }

        return self::$timezone;
    }

    /**
     * @param string $timezone
     */
    public static function setTimeZone($timezone = 'UTC')
    {
        self::$timezone = new \DateTimeZone($timezone);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
