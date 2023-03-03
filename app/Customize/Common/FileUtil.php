<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 7/15/2021
 * Time: 10:51 AM
 */

namespace Customize\Common;


class FileUtil
{


    public  static function downloadImg($url,$saveFull){
        file_put_contents($saveFull, file_get_contents($url));
    }
    public  static function getFilesInDir($dir,$limitFile = -1){
        $ffs = scandir($dir);
        $fileList = [];

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        foreach($ffs as $ff){
            if($ff!='Thumbs.db'){
                if( $limitFile>-1){
                    if(count($limitFile)== $limitFile){
                        break;
                    }

                }
                $fileList[] = $ff;

            }
        }
        return $fileList;

    }
    public  static function getFilesInDirFullPath($dir,$limitFile = -1){
        $ffs = scandir($dir);
        $fileList = [];

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        foreach($ffs as $ff){

            if($ff!='Thumbs.db'){
                if( $limitFile>-1){
                    if(count($fileList)== $limitFile){
                        break;
                    }

                }
                $fullPath =$dir.DIRECTORY_SEPARATOR .$ff;
                if(!is_dir($fullPath)){
                    $fileList[] = $fullPath;
                }
            }
        }
        return $fileList;
    }

    public  static function getFileNameYmdHis(){
        return date('ymdHis');
    }
    public  static function getFileNameYmdHis_Mili(){
        return date('ymdHis').'_'.round(microtime(true) * 10000);
    }
    public  static function readFile($fullPath){
        $myContent = "";
        if (file_exists($fullPath)) {
            $myContent = file_get_contents($fullPath);
        }
        if (!mb_check_encoding($myContent, "UTF-8")) {

            $myContent = mb_convert_encoding($myContent, "UTF-8",
                "Shift-JIS, EUC-JP, JIS, SJIS, JIS-ms, eucJP-win, SJIS-win, ISO-2022-JP,
       ISO-2022-JP-MS, SJIS-mac, SJIS-Mobile#DOCOMO, SJIS-Mobile#KDDI,
       SJIS-Mobile#SOFTBANK, UTF-8-Mobile#DOCOMO, UTF-8-Mobile#KDDI-A,
       UTF-8-Mobile#KDDI-B, UTF-8-Mobile#SOFTBANK, ISO-2022-JP-MOBILE#KDDI");
        }
        return $myContent;

    }
    public  static function fileExist($fullPath){
        return file_exists($fullPath);;
    }
    public static function reNameDir($dirOld,$dirDir) {
        if (file_exists($dirOld)) {
            return rename($dirOld,$dirDir);
        }
        return false;
    }
    public static function makeDirectory($upload_dir) {
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
    }
    public static function CopyFile($fileFrom,$fileTo) {
        if (file_exists($fileFrom)) {
            if (!copy($fileFrom, $fileTo)) {
                return false;
            }
            chmod($fileTo, 0777);
            return true;
        }
        return false;
    }
    public static function moveFileInFolder($oldfolder,$newfolder) {
        $files = scandir($oldfolder);
        foreach($files as $fname) {
            if($fname != '.' && $fname != '..') {
                rename($oldfolder.$fname, $newfolder.$fname);
                chmod($newfolder.$fname, 0777);
            }
        }
    }
    public static function  deleteAndBackup($fileDelete,$fileBackup) {
        if (file_exists($fileDelete)) {
            if(\App\Helpers\FileUtil::CopyFile($fileDelete,$fileBackup)){
                unlink($fileDelete);
            }
        }
    }
    public static function  deleteAndBackupFolder($fileDelete,$folderBackup) {
        if (file_exists($fileDelete)) {
            $fileBackup =$folderBackup .DIRECTORY_SEPARATOR. pathinfo($fileDelete,PATHINFO_BASENAME);
            FileUtil::deleteAndBackup($fileDelete,$fileBackup);
        }
    }
    public static function  getExtensionDot($fileName) {
        return ".".pathinfo($fileName,FILEINFO_EXTENSION);
    }
    public static function  getExtensionNoDot($fileName) {
        return pathinfo($fileName,FILEINFO_EXTENSION);
    }
    public static function  getNameAndExtension($fileName) {
        return "".pathinfo($fileName,PATHINFO_BASENAME);
    }
    public static function  getNameNoExtension($fileName) {
        return "".pathinfo($fileName)['filename'];
    }
    public static function  deleteDirAuto($directory) {
        //print_r(glob($directory."/*")); die;
        foreach(glob($directory."/*") as $file)
        {
            if(is_dir($file)) {
                FileUtil::deleteDirAuto($file);
            } else {
                unlink($file);
            }
        }
        rmdir($directory);
    }
    public static function  deleteFileInDir($directory) {
        $allF = FileUtil::getFilesInDir($directory);

        foreach ($allF as $f){
            $fF= $directory."/".$f;
            FileUtil::deleteFile($fF);
        }
    }
    public static function deleteDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir"){
                        rmdir($dir."/".$object);
                    }else{
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    public static function deleteDirectoryAll($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!static::deleteDirectoryAll($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

    /***
     * //If mime type is Mmp,will convert to jpg
     * @param $filename
     */
    public static function convertBmpMimeToJpg($filename)
    {
        $myMi = mime_content_type($filename);
        $img  =null;
        if( false!==strpos($myMi,"bmp")){
            $img = FileUtil::imagecreatefrombmp1($filename);
            imagejpeg($img,$filename);
        }
    }
    public static function resizeImage($imageFrom,$imageConverted,$width=378,$height=213){
        if (file_exists($imageFrom)) {
            $img= Image::make($imageFrom);
            $img->resize($width,$height,function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->resizeCanvas($width, $height, 'center', false, '#ffffff');
            if (!file_exists($imageConverted)) {
                $img->save($imageConverted);
            }
        }
    }


    public static function deleteFile($file_pointer){
        if(is_file($file_pointer)==false)
        {
            return false;
        }
        if (!unlink($file_pointer))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    public static function writeFileAppend($fullPath,$msg){
        file_put_contents($fullPath, $msg );
    }
    public static function writeFileFull($fullPath,$msg){
        if(empty($msg)){
            return;
        }
        if(FileUtil::fileExist($fullPath)){
            FileUtil::deleteFile($fullPath);
        }
        $fullPath = str_replace(['\r\n'],"",$fullPath);
        $myfile = fopen($fullPath, "w") or die("Unable to open file!");
        $txt = $msg;
        fwrite($myfile, $txt);
        fclose($myfile);
        chmod($fullPath, 0777);
    }

}
