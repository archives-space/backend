<?php

namespace App\Security;

use App\Model\ApiResponse\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationCustomSuccessHandler extends AuthenticationSuccessHandler
{
    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return JWTAuthenticationSuccessResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $rawRes = parent::onAuthenticationSuccess($request, $token);

        $content = json_decode($rawRes->getContent(), true);
        $token = $content['token'];
        $res = new ApiResponse();
        $res->setData(['token' => $token]);

        return $res->getResponse();
    }
}
