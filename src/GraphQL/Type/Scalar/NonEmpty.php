<?php

namespace App\GraphQL\Type\Scalar;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Utils\Utils;
use Illuminate\Support\Carbon;

class NonEmpty extends CustomScalarType
{
    public $name = 'NonEmpty';

    /**
     * @param Carbon $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed|string
     * @throws Error
     */
    public function parseValue($value)
    {
        // we only considered a value as incorrect if it is not NULL and empty
        if ($value !== null && (empty($value) || $value = "")) {
            throw new Error("Cannot represent following value as Non Empty: " . Utils::printSafeJson($value));
        } else {
            return $value;
        }
    }
}
