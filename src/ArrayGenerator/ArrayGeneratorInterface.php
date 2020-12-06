<?php

namespace App\ArrayGenerator;

interface ArrayGeneratorInterface
{
    public function toArray($object, bool $fullinfo): array;
}