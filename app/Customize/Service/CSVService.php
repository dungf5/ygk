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

namespace Customize\Service;

use Customize\Entity\DtImportCSV;
use Doctrine\ORM\EntityManagerInterface;

class CSVService
{
    use CurlPost;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /***
     * @param string $path
     * @return array
     */
    public function readFile($path = '', $col)
    {
        if (empty($path) || !str_ends_with(strtolower(trim($path)), '.csv')) {
            return [
                'status' => 0,
                'message' => "File {$path} is invalid.",
            ];
        }

        try {
            $data = [];

            if (($fp = fopen($path, 'r')) !== false) {
                while (($row = fgetcsv($fp, 0, ',')) !== false) {
                    if (!empty($row) && count($row) == $col) {
                        $data[] = str_replace('?', '㈱', mb_convert_encoding($row, 'UTF-8', 'Shift-JIS'));
                    }
                }
                fclose($fp);
            }

            return [
                'status' => 1,
                'message' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => "File {$path} ".$e->getMessage(),
            ];
        }
    }

    public function transferFile($path_from, $path_to, $file)
    {
        try {
            if (empty($file)) {
                $message = "copy file FROM {$path_from} TO {$path_to}";
                $message .= "\nFile is empty";
                $this->pushGoogleChat($message);

                return [
                    'status' => 0,
                    'message' => 'File is empty',
                ];
            }

            $temp_path_local = '';

            if (getenv('APP_IS_LOCAL') == 1) {
                $temp_path_local = '.';
                $path_from = '.'.$path_from;
                $path_to = '.'.$path_to;
            }

            if (file_exists($path_from.$file) == false) {
                $this->pushGoogleChat('path: '.$path_from.$file.' is invalid');

                return [
                    'status' => -1,
                    'message' => 'File '.$file.' is not existed',
                ];
            }

            // Create directory local if have'n
            $arr_path_local = explode('/', $path_to);

            foreach ($arr_path_local as $subDir) {
                if (empty($subDir)) {
                    continue;
                }
                $temp_path_local .= '/'.$subDir;

                if (file_exists($temp_path_local) == false) {
                    mkdir($temp_path_local);
                }
            }
            $temp_path_local = null;
            // End - Create directory local if have'n

            $yearDir = $path_to.date('Y');
            if (file_exists($yearDir) == false) {
                mkdir($yearDir);
            }

            $monthDir = $path_to.date('Y/m');
            if (file_exists($monthDir) == false) {
                mkdir($monthDir);
            }

            $local_file_name = date('YmdHis').'.csv';
            $local_file = $monthDir.'/'.$local_file_name;

            // try to copy file from path_from to path_to
            if (copy($path_from.$file, $local_file)) {
                // Save file information to DB
                $insertDate = [
                    'file_name' => $local_file_name,
                    'directory' => $monthDir,
                    'message' => null,
                    'is_sync' => 0,
                    'is_error' => 0,
                    'is_send_mail' => 0,
                    'in_date' => new \DateTime(),
                    'up_date' => null,
                ];

                $this->entityManager->getRepository(DtImportCSV::class)->insertData($insertDate);

                $status = 1;
                $message = "successfully written {$file} to {$local_file}";
                //unlink($path_from.$file);
                $this->pushGoogleChat("successfully written {$file} to {$local_file}");

            } else {
                $status = 0;
                $message = "There was a problem while downloading {$file} to {$local_file}";

                $this->pushGoogleChat("There was a problem while downloading {$file} to {$local_file}");
            }

            return [
                'status' => $status,
                'message' => $message,
            ];

        } catch (\Exception $e) {
            $this->pushGoogleChat($e->getMessage());

            return [
                'status' => -1,
                'message' => $e->getMessage(),
            ];
        }
    }
}
