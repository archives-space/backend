<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\RequestStack;

class BaseUrl
{
    /**
     * @param RequestStack $requestStack
     * @return string
     */
    public static function fromRequestStack(RequestStack $requestStack): string
    {
        $request = $requestStack->getMainRequest();
        return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
    }
}