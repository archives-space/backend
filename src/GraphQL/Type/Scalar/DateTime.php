<?php

namespace App\GraphQL\Type\Scalar;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Utils\Utils;

class DateTime extends CustomScalarType
{

    public $name = 'datetime';

    /**
     * @param \DateTime $value
     * @return mixed
     */
    public function serialize($value)
    {
        $date = (array) $value;
        $date['date'] = explode('.', $date['date'])[0]; // remove sub seconds units
        return $date;
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws Error
     */
    public function parseValue($value)
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        $errors = \DateTime::getLastErrors();
        if ($errors['error_count'] > 0 || $errors['warning_count'] > 0 || $date == false) {
            throw new Error("Cannot represent following value as datetime: " . Utils::printSafeJson($value));
        } else {
            return $value;
        }
    }
}
