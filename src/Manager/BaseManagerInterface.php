<?php

namespace App\Manager;

interface BaseManagerInterface
{
    /**
     * @return string[]
     */
    public function requiredField();
}