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
    //9:クロース(出荷済)
     */
    const ARR_ORDER_STATUS_TEXT = [
        '1' => '未引当', '2' => '一部引当', '3' => '引当済', '4' => 'キャンセル', '9' => 'クロース(出荷済)',
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
