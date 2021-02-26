<?php

namespace App\Utils\Response;

use App\Model\ApiResponse\Error;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * This class contain a static method to adapt a violation list to a
 */
class ViolationAdapter
{
    /**
     * @param ConstraintViolation $violation
     * @param int $defaultCode
     * @return Error
     */
    public static function adapt(ConstraintViolation $violation, int $defaultCode = 20): Error
    {
        $compo = explode('\\', get_class($violation->getConstraint()));
        $raw = $compo[count($compo) - 1];
        $path = strtoupper($violation->getPropertyPath());
        $slug = $path . '_INVALID_' . strtoupper($raw);
        $error = new Error($slug, $defaultCode, $violation->getMessage());
        $error->setPropertyPath($violation->getPropertyPath());
        return $error;
    }

}