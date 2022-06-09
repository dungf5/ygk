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
    /***
     * document root .
     * @return mixed
     */
    public static function getRootDir()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }
    public static function getHtmluserDataDir()
    {

        return MyCommon::getRootDir().'/html/user_data';
    }
    //

    public static function getPara($key)
    {
        if (isset($_REQUEST[$key])) {
            return trim($_REQUEST[$key]);
        }

        return '';
    }

    public static function checkExistText($source, $key)
    {
        if (strpos($source, $key) !== false) {
            return true;
        }

        return false;
    }

    public static function converHtmlToPdf($pathSave, $nameFile, $htmlPdfContent, $marginTop = '0', $marginBottom = '0', $margin_left = 0, $margin_right = 0)
    {
        ini_set('memory_limit', '9072M');
        ini_set('MAX_EXECUTION_TIME', '-1');
        $fullPathHtml = $pathSave.'/'.str_replace('.pdf', '.html', $nameFile);
        $fullPathPdf = $pathSave.'/'.$nameFile;
        FileUtil::writeFileFull($fullPathHtml, $htmlPdfContent);
        $outArr = [];
        if(getenv("APP_IS_LOCAL")!==0){
            $pathRun = "/usr/bin/wkhtmltopdf/bin/wkhtmltopdf --margin-top {$marginTop} --margin-bottom {$marginBottom} --margin-left {$margin_left} --margin-right {$margin_right} --encoding utf-8 --custom-header 'meta' 'charset=utf-8'";
            exec("{$pathRun} {$fullPathHtml} {$fullPathPdf}", $outArr);
        }else{

        }


    }

    public static function isEmptyOrNull($source)
    {
        if ($source == null) {
            return true;
        }
        if (is_object($source)) {
            return false;
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
