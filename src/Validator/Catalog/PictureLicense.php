<?php

namespace App\Validator\Catalog;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PictureLicense extends Constraint
{
    public $message = 'License "{{ string }}" not valid';

    public function validatedBy()
    {
        return static::class.'Validator';
    }
}