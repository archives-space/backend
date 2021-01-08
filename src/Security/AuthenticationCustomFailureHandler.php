<?php

namespace App\Security;

use App\Model\ApiResponse\ApiResponse;
use App\Utils\Response\Errors;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationCustomFailureHandler extends AuthenticationFailureHandler
{

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $rawRes = parent::onAuthenticationFailure($request, $exception);
        $content = json_decode($rawRes->getContent(), true);
        $res = new ApiResponse();
        $hasCustomRes = false;
        if ($content['code'] === 401) {
            $res->addError(Errors::USER_INVALID_LOGIN);
            $hasCustomRes = true;
        }
        if ($content['code'] === 200) {
            $hasCustomRes = true;
        }
        if ($hasCustomRes) {
            return $res->getResponse();
        }
        return $rawRes;
    }
}
