<?php


namespace Customize\Service;



class FTPService
{
    private $host;
    private $port;
    private $userName;
    private $password;

    public function __construct()
    {
        $this->host = getenv("FTP_CSV_HOST") ?? "";
        $this->port = getenv("FTP_CSV_PORT") ?? 21;
        $this->userName = getenv("FTP_CSV_USERNAME") ?? '';
        $this->password = getenv("FTP_CSV_PASSWORD") ?? '';
    }

    function connect()
    {
        try {
            $connId = @ftp_connect($this->host, $this->port, 30);

            if (!$connId) {
                // Connect ftp fail
                var_dump('Connect ftp fail');
                return null;
            }

            elseif (!@ftp_login($connId, $this->userName, $this->password)) {
                // Login ftp fail
                var_dump('Login ftp fail');
                return null;
            }

            else {
                return $connId;
            }

        } catch (\Exception $e) {
            return null;
        }
    }

    function getData ()
    {
        try {
            if ($conn = self::connect()) {
                @ftp_pasv($conn, true);
                $path   = "/var/www/html/HACHU";

                if (@ftp_chdir($conn, $path)) {
                    $file_list  = ftp_nlist($conn, $path);

                    foreach ($file_list as $file)
                    {
                        var_dump($file);

                        return [
                            'status'    => 1,
                            'message'   => '',
                        ];
                    }

                    //close
                    @ftp_close($conn);
                }
            }
        } catch (\Exception $e) {
            return [
                'status'    => 0,
                'message'   => $e->getMessage(),
            ];
        }
    }
}
