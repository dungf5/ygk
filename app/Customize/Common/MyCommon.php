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



use Faker\Provider\Uuid;

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
    public static function getDayWeekend(){
        $numberofdays = 15;//find in 15 days
        $startdate1 = date("Y-m-d");//'2022-09-23';//
        $dayText = date('Y-m-d', strtotime($startdate1 . ' +1 day'));//ignore today
        $startdate = $dayText;//'2022-09-23';

        $d = new \DateTime( $startdate );
        $t = $d->getTimestamp();
        $arSatSun =[];
        // loop for X days
        for($i=0; $i<$numberofdays; $i++){

            // add 1 day to timestamp
            $addDay = 86400;

            // get what day it is next day
            $nextDay = date('w', ($t+$addDay));

            // if it's Saturday or Sunday get $i-1
            if($nextDay == 0 || $nextDay == 6) {
                $t1 = $t+$addDay;
                $arSatSun[]=  date('Y-m-d', $t1);
                $i--;
            }

            // modify timestamp, add 1 day
            $t = $t+$addDay;
        }

        $d->setTimestamp($t);
        return $arSatSun;
    }
    public static function get3DayAfterDayOff($dayOfAr){

        $startdate1 = date("Y-m-d");//'2022-09-23';//
        $dayText = $startdate1;//date('Y-m-d', strtotime($startdate1 . ' +1 day'));//ignore today
        $dayStart = new \DateTime( $dayText );
        $timeStart = $dayStart->getTimestamp();
        $numberDayWant = 3;
        $numberDayGet =0;
        $addDay = 86400;
        $dayTextOk="";
        while (true){

            // get what day it is next day
            $timeStart = $timeStart+$addDay;
            $dayText = date('Y-m-d', $timeStart);

            if(in_array($dayText,$dayOfAr)){

                continue;
            }else{
                $dayTextOk = date('Y-m-d', $timeStart);
                $numberDayGet ++;
            }
            if($numberDayGet == $numberDayWant){
                break;
            }
        }


        return $dayTextOk;

    }

    public static function getNextDayNoWeekend(){
        $startdate = date("Y-m-d");//'2022-09-23';

        $numberofdays = 1;

        $d = new \DateTime( $startdate );
        $t = $d->getTimestamp();

        // loop for X days
        for($i=0; $i<$numberofdays; $i++){

            // add 1 day to timestamp
            $addDay = 86400;

            // get what day it is next day
            $nextDay = date('w', ($t+$addDay));

            // if it's Saturday or Sunday get $i-1
            if($nextDay == 0 || $nextDay == 6) {
                $i--;
            }

            // modify timestamp, add 1 day
            $t = $t+$addDay;
        }

        $d->setTimestamp($t);

        return $d->format( 'Y-m-d' );
    }

    /***
     * @param $dayText
     * @param $dayAr orderby asc
     * @return false|string
     */
    public static function getDayAfterDayOff($dayText,$dayAr){


        if(count($dayAr)==0){
            return  $dayText;
        }
        foreach ($dayAr as $itemDay){
            if($itemDay==$dayText){
                $dayText = date('Y-m-d', strtotime($dayText . ' +1 day'));
            }else{
                break;
            }

        }

        return $dayText;

    }

    public static function getPara($key)
    {
        if (isset($_REQUEST[$key])) {
            return trim($_REQUEST[$key]);
        }

        return '';
    }
    public static function getSession($key)
    {

        if (isset($_SESSION[$key])) {
           return $_SESSION[$key];
        }

        return null;
    }
    public static function setSession($key,$val)
    {
        $_SESSION[$key] = $val;
    }

    public static function genRanDom(){
        return Uuid::uuid();
    }
    public static function getCarSession(){
        if(static::getSession("CART_SESSION")==null){
            static::setSession("CART_SESSION",static::genRanDom());
        }
        return static::getSession("CART_SESSION");

    }
    public static function deleteCarSession(){
        static::setSession("CART_SESSION",null);
        $_SESSION["CART_SESSION"]=null;
        unset($_SESSION["CART_SESSION"]);
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
