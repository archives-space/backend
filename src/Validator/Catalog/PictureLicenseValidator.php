<?php

namespace App\Validator\Catalog;

use App\Utils\Catalog\LicenseHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PictureLicenseValidator extends ConstraintValidator
{

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     * @return mixed
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PictureLicense) {
            throw new UnexpectedTypeException($constraint, PictureLicense::class);
        }

        if (!$value) {
            return true;
        }

        if(in_array($value, LicenseHelper::getLicenses())){
            return true;
        }

        $this->context->buildViolation($constraint->message)
                      ->setParameter('{{ string }}', $value)
                      ->addViolation();
    }
}