<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class IdGenerator extends AbstractIdGenerator
{
    public static function generateHex(int $len): string
    {
        $base = hash('sha256', random_bytes(8));
        return strtoupper(substr($base, 0, $len));
    }

    public static function generateStr(int $len): string
    {
        $letters = 'abcdefghijklmnopqrstABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $letters[random_int(0, strlen($letters) - 1)];
        }
        return $out;
    }

    public function generate(EntityManager $em, $entity): string
    {
        return (string)self::generateStr(12);
    }
}