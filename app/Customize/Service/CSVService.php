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

class CSVService
{
    use CurlPost;

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
}
