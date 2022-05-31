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

namespace Customize\Common;

class MyCommon
{
    public static function getRootDir()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    public static function checkExistText($source, $key)
    {
        if (strpos($source, $key) !== false) {
            return true;
        }

        return false;
    }

    public static function isEmptyOrNull($source)
    {
        if ($source == null) {
            return true;
        }
        if(is_object($source)){
            return  false;
        }
        if (is_array($source)) {
            if (count($source) == 0) {
                return true;
            }
        } else {
            if (is_string($source)) {
                if (strlen($source) == 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
