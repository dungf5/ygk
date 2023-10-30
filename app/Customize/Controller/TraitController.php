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

namespace Customize\Controller;

trait TraitController
{
    public function traitRedirect()
    {
        if ($this->globalService->getLoginType() == 'approve_user') {
            return '/mypage/approve/1';
        }

        if ($this->globalService->getLoginType() == 'stock_user') {
            return '/mypage/approve/2';
        }

        return '';
    }

    public function traitRedirectStockApprove()
    {
        if ($this->globalService->getLoginType() != 'stock_user') {
            return '/';
        }

        return '';
    }

    public function traitRedirectApprove()
    {
        if ($this->globalService->getLoginType() != 'approve_user') {
            return '/';
        }

        return '';
    }

    public function traitRedirectApproveExport()
    {
        if ($this->globalService->getLoginType() != 'approve_user' && $this->globalService->getLoginType() != 'stock_user') {
            return '/';
        }

        return '';
    }
}
