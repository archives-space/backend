<?php

namespace App\ArrayGenerator;

interface ArrayGeneratorInterface
{
    public function toArray(object $object, bool $fullInfo): array;
}