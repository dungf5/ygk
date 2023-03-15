<?php


namespace Customize\Service;


class CSVService
{
    /***
     * @param string $path
     * @return array
     */
    public function readFile($path = "") {
        if (empty($path) || !str_ends_with(strtolower(trim($path)),".csv"))
            return [
                'status'    => 0,
                'message'   => "path error",
            ];

        try {
            $data   = [];
            $col    = 0;
            $index  = 1;

            if (($fp = fopen($path, "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 0, ",")) !== FALSE) {
                    if ($index == 1) {
                        $col = count($row);
                        $index++;
                    }

                    if (count($row) == $col)
                        $data[] = mb_convert_encoding($row, 'UTF-8', 'Shift-JIS');
                }
                fclose($fp);
            }

            return [
                'status'    => 1,
                'message'   => $data,
            ];

        } catch (\Exception $e) {
            return [
                'status'    => 0,
                'message'   => $e->getMessage(),
            ];
        }
    }
}
