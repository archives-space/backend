<?php

namespace App\Security;

use App\Model\ApiResponse\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationCustomSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var AuthenticationSuccessHandler
     */
    private $instance;

    /**
     * @param JWTTokenManagerInterface $jwtManager
     * @param EventDispatcherInterface $dispatcher
     * @param iterable|JWTCookieProvider[] $cookieProviders
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $dispatcher, $cookieProviders = [])
    {
        $this->instance = new AuthenticationSuccessHandler($jwtManager, $dispatcher, $cookieProviders);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return JWTAuthenticationSuccessResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $rawRes = $this->instance->onAuthenticationSuccess($request, $token);

        $content = json_decode($rawRes->getContent(), true);
        $token = $content['token'];
        $res = new ApiResponse();
        $res->setData(['token' => $token]);

        return $res->getResponse();
    }

    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null) {
        $this->instance->handleAuthenticationSuccess($user, $jwt);
    }
}
