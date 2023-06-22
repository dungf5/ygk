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

namespace Customize\Config;

trait CSVHeader
{
    public function getWSEOSCsvOrderHeader()
    {
        return [
            'order_type',
            'web_order_type',
            'order_date',
            'order_no',
            'system_code',
            'order_company_code',
            'order_shop_code',
            'order_staff_code',
            'sales_company_code',
            'sales_staff_code',
            'order_company_name',
            'delivery_flag',
            'shipping_company_code',
            'shipping_shop_code',
            'shipping_name',
            'shipping_address1',
            'shipping_address2',
            'shipping_post_code',
            'shipping_tel',
            'shipping_fax',
            'delivery_date',
            'export_type',
            'aprove_type',
            'order_cancel',
            'delete_flag',
            'order_voucher_type',
            'order_line_no',
            'order_flag',
            'order_system_code',
            'order_staff_name',
            'order_shop_name',
            'product_maker_code',
            'product_name',
            'order_num',
            'order_price',
            'order_amount',
            'tax_type',
            'remarks_line_no',
            'jan_code',
            'cash_type_code',
            'order_create_day',
            'order_update_day',
        ];
    }

    public function getNatExportStockHeader()
    {
        return [
            'JANコード',
            '品番(メーカー品番)',
            'グレード',
            '在庫数',
            '次回入荷日',
            '次回入荷数',
            '発注ロット',
            '定価',
            '仕入先品番',
            'メーカーコード',
            'カラーコード',
            'サイズコード',
            'メーカー在庫数',
            '原価',
        ];
    }

    public function getNatEOSCsvOrderHeader()
    {
        return [
            'reqcd',
            'jan',
            'mkrcd',
            'natcd',
            'qty',
            'cost',
            'delivery_day',
        ];
    }

    public function getNatExportShippingHeader()
    {
        return [
            '納品書番号',
            'JANコード',
            '品番(メーカー品番)',
            'ナチュラム商品番号',
            '納品数',
            '納品単価',
            '到着予定日',
        ];
    }

    public function getNatSortExportHeader()
    {
        return [
            '発注番号',
            'JANコード',
            '品番',
            'ナチュラム商品番号',
            '発注数',
            '発注単価',
            '納期',
        ];
    }
}
