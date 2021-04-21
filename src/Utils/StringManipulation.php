<?php

namespace App\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;

class StringManipulation
{
    /**
     * @param string $string
     * @return string
     */
    public static function slugify(string $string): string
    {
        return strtolower((new AsciiSlugger())->slug($string));
    }
}