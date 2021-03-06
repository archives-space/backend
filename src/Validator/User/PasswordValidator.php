<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use ZxcvbnPhp\Zxcvbn;

class PasswordValidator extends ConstraintValidator
{


    /**
     * @param mixed      $value
     * @param Constraint $constraint
     * @return mixed
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Password) {
            throw new UnexpectedTypeException($constraint, Password::class);
        }

        if (!$value) {
            return true;
        }

        if ((new Zxcvbn())->passwordStrength($value)['score'] > 1) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
                      ->setParameter('{{ string }}', $value)
                      ->addViolation()
        ;
    }
}