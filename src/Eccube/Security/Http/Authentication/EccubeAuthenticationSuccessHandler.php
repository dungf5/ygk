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

namespace Eccube\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class EccubeAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        try {
            $response = parent::onAuthenticationSuccess($request, $token);
        } catch (RouteNotFoundException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
        }

        if (preg_match('/^https?:\\\\/i', $response->getTargetUrl())) {
            $response->setTargetUrl($request->getUriForPath('/'));
        }

        $customerId     = $_SESSION["customer_id"] ?? '';
        if (!empty($customerId)) {
            try {
                $loginType  = $_SESSION["usc_{$customerId}"]['login_type'] ?? '';

                if (!empty($loginType) && $loginType == "supper_user") {
                    $_SESSION["choose_represent"]    = true;
                    return new RedirectResponse('/mypage/login');
                }

                if (!empty($loginType) && $loginType == "represent_code") {
                    $_SESSION["choose_shipping"]    = true;
                    return new RedirectResponse('/mypage/login');
                }

            } catch (\Exception $e) {}
        }


        return $response;
    }
}
