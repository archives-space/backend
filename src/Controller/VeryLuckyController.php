<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class VeryLuckyController
{
    public function number(): JsonResponse
    {
        $number = random_int(0, 100);

        return new JsonResponse([
          'name' => 'wikiarchive.space',
          'message' => "This is your very lucky number: $number"
        ]);
    }
}