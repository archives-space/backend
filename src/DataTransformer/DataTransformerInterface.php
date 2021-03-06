<?php

namespace App\DataTransformer;

interface DataTransformerInterface
{
    public function toArray($object, bool $fullInfo = true);

    public function toObject($array);
}