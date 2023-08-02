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

class MyConstant
{
    /**
     * status
     * 受注ステータス order status
     */
    const ARR_ORDER_STATUS_TEXT = [
        '0'     => '調査要',
        '1'     => '未確保',
        '2'     => '一部確保済',
        '3'     => '確保済',
        '4'     => 'キャンセル',
        '9'     => '注文完了',
    ];

    /**
     * status
     *  shipping_status
      2:出荷済
     */
    const ARR_SHIPPING_STATUS_TEXT = [
        '1' => '出荷指示済', '2' => '出荷済',
    ];
    const MY_WEB ="https://staging.xbraid.net";
}
