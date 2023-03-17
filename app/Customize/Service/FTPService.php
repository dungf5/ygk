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

use _PHPStan_76800bfb5\Nette\Utils\DateTime;
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

    public function getFiles($path, $path_local)
    {
        try {
            if ($conn = self::connect()) {
                @ftp_set_option($conn, FTP_USEPASVADDRESS, false);
                @ftp_pasv($conn, false);

                if (@ftp_chdir($conn, $path)) {
                    $file_list = @ftp_nlist($conn, $path);

                    if (!empty($file_list) && is_array($file_list) && count($file_list)) {
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

                        foreach ($file_list as $remote_file) {
                            $file_name = explode('/', $remote_file);
                            $file_name = $file_name[count($file_name) - 1] ?? '';

                            if (empty($file_name)) {
                                continue;
                            }

                            $file_name = strtolower(trim($file_name));

                            if (!str_ends_with($file_name, '.csv')) {
                                continue;
                            }

                            //Check file in DB
                            $fileExist = $this->entityManager->getRepository(DtImportCSV::class)->findOneBy(['file_name' => $file_name]);
                            if (!empty($fileExist)) {
                                continue;
                            }

                            $local_file = $monthDir.'/'.$file_name;

                            // open file to write to
                            $handle = fopen($local_file, 'w');

                            // try to download $remote_file and save it to $handle
                            if (ftp_fget($conn, $handle, $remote_file, FTP_ASCII, 0)) {
                                // Save file information to DB
                                Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                                $insertDate = [
                                    'file_name' => $file_name,
                                    'directory' => $monthDir,
                                    'message' => null,
                                    'is_sync' => 0,
                                    'is_error' => 0,
                                    'is_send_mail' => 0,
                                    'in_date' => new DateTime(),
                                    'up_date' => null,
                                ];

                                $this->entityManager->getRepository(DtImportCSV::class)->insertData($insertDate);

                                $message = "successfully written to $local_file";
                            } else {
                                $message = "There was a problem while downloading $remote_file to $local_file";
                            }

                            //close
                            @ftp_close($conn);

                            return [
                                'status' => 1,
                                'message' => $message,
                            ];
                        }
                    }

                    //close
                    @ftp_close($conn);

                    return [
                        'status' => 0,
                        'message' => 'No files',
                    ];
                }
            }

            return [
                'status' => 0,
                'message' => 'Connect FTP server error',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }
}
