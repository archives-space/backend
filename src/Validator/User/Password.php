<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Password extends Constraint
{
    public $message = 'Password too weak';

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}