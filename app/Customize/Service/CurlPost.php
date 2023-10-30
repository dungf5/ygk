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

trait CurlPost
{
    /**
     * push message to google chat
     *
     * @param string $content
     * @param null $thread
     *
     * @return string
     */
    public function pushGoogleChat($content = null, $thread = null)
    {
        try {
            $webhook = getenv('WEBHOOK_GOOGLE_CHAT');

            if (empty($webhook)) {
                return;
            }

            $data = json_encode([
                'text' => $content,
                'thread' => [
                    'name' => $thread,
                ],
            ], JSON_UNESCAPED_UNICODE);

            $appEnv = getenv('APP_ENV') ?? 'local';
            $curl = curl_init();

            if (getenv('APP_IS_LOCAL') == 1) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }

            curl_setopt($curl, CURLOPT_URL, $webhook);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                log_info('Push Google Chat Error : '.$err);
            }

            return $response;
        } catch (\Exception $e) {
            log_info('Push Google Chat Error : '.$e->getMessage());

            return false;
        }
    }
}
