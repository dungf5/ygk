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

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\DtImportCSV;
use Customize\Service\Common\MyCommonService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

class FTPService
{
    private $host;
    private $port;
    private $userName;
    private $password;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $commonService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->host = getenv('FTP_CSV_HOST') ?? '';
        $this->port = getenv('FTP_CSV_PORT') ?? 21;
        $this->userName = getenv('FTP_CSV_USERNAME') ?? '';
        $this->password = getenv('FTP_CSV_PASSWORD') ?? '';

        $this->entityManager = $entityManager;
        $this->commonService = new MyCommonService($entityManager);
    }

    public function connect()
    {
        try {
            $connId = @ftp_connect($this->host, $this->port, 30);

            if (!$connId) {
                // Connect ftp fail
                log_error('Connect ftp fail');

                return null;
            } elseif (!@ftp_login($connId, $this->userName, $this->password)) {
                // Login ftp fail
                log_error('Login ftp fail');

                return null;
            } else {
                return $connId;
            }
        } catch (\Exception $e) {
            log_error('Connect ftp fail');
            log_error($e->getMessage());

            return null;
        }
    }

    public function getFiles($file, $path_local)
    {
        try {
            if ($conn = self::connect()) {
                @ftp_set_option($conn, FTP_USEPASVADDRESS, false);
                @ftp_pasv($conn, true);

                if (empty($file)) {
                    return [
                        'status' => 0,
                        'message' => 'File path is empty',
                    ];
                }

                // Create directory local if have'n
                $arr_path_local = explode('/', $path_local);
                $temp_path_local = '';

                if (getenv('APP_IS_LOCAL') == 1) {
                    $temp_path_local = '.';
                    $path_local = '.'.$path_local;
                }

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

                $yearDir = $path_local.date('Y');
                if (file_exists($yearDir) == false) {
                    mkdir($yearDir);
                }

                $monthDir = $path_local.date('Y/m');
                if (file_exists($monthDir) == false) {
                    mkdir($monthDir);
                }

                $local_file_name = date('YmdHis').'.csv';
                $local_file = $monthDir.'/'.$local_file_name;

                // open file to write to
                if (!$handle = fopen($local_file, 'w')) {
                    return [
                        'status' => 0,
                        'message' => "Cant' open local file {$local_file}",
                    ];
                }

                // try to download $remote_file and save it to $handle
                try {
                    if (ftp_fget($conn, $handle, $file, FTP_BINARY, 0)) {
                        // Save file information to DB
                        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
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

                        $message = "successfully written {$file} to {$local_file}";
                        fclose($handle);

                    } else {
                        $message = "There was a problem while downloading $file to $local_file";
                        fclose($handle);
                        unlink($local_file);
                    }

                    //close
                    @ftp_close($conn);

                } catch (\Exception $e) {
                    unlink($local_file);

                    return [
                        'status' => -1,
                        'message' => $e->getMessage(),
                    ];
                }

                return [
                    'status' => 1,
                    'message' => $message,
                ];
            }

            return [
                'status' => -1,
                'message' => 'Connect FTP server error',
            ];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function upFiles($remote_file, $path_local)
    {
        try {
            if ($conn = self::connect()) {
                @ftp_set_option($conn, FTP_USEPASVADDRESS, false);
                @ftp_pasv($conn, true);

                // upload a file
                if (ftp_put($conn, $remote_file, $path_local, FTP_ASCII)) {
                    $result = [
                            'status' => 1,
                            'message' => $remote_file,
                        ];
                    log_info("successfully uploaded {$remote_file}");
                } else {
                    $result = [
                            'status' => 0,
                            'message' => "There was a problem while uploading {$remote_file}",
                        ];
                    log_error("There was a problem while uploading {$remote_file}");
                }

                //close
                @ftp_close($conn);

                return $result;
            }

            return [
                'status' => -1,
                'message' => 'Connect FTP server error',
            ];
        } catch (\Exception $e) {
            return [
                'status' => -1,
                'message' => $e->getMessage(),
            ];
        }
    }
}
