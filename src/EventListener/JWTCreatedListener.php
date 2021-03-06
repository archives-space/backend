<?php

namespace App\EventListener;

use App\Repository\User\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener {

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @param RequestStack $requestStack
     * @param UserRepository $userRepository
     */
    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload       = $event->getData();

        $user = $this->userRepository->getUserByUsernameOrEmail($payload['username']);

        $payload['user'] = [
          'id' => $user->getId(),
          'email' => $user->getEmail(),
          'username' => $user->getUsername(),
          'roles' => $user->getRoles()
        ];
        unset($payload['roles']);
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);

        $header        = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}