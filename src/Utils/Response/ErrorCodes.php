<?php

namespace App\Utils\Response;

class ErrorCodes
{
    // Codes

    // query error
    const MISSING_FIELD = 100;

    // user error
    const NO_USER         = 200;
    const USER_EXISTS     = 201;
    const USERNAME_EXIST  = 202;
    const EMAIL_EXIST     = 203;
    const EMAIL_NOT_VALID = 204;
    const PASSWORD_WEAK   = 205;

    // picture error
    const NO_PICTURE = 300;

    // catalog error
    const NO_CATALOG = 400;
    const NO_PARENT  = 401;

    // Messages
    const LABELS = [
        self::MISSING_FIELD   => 'Field missing',
        self::NO_USER         => 'User not found',
        self::USER_EXISTS     => 'User already exist',
        self::USERNAME_EXIST  => 'Username already taken',
        self::EMAIL_EXIST     => 'Email already taken',
        self::EMAIL_NOT_VALID => 'Email not valid',
        self::PASSWORD_WEAK   => 'Password weak',
        self::NO_PICTURE      => 'Image not found',
        self::NO_CATALOG      => 'Catalog not found',
        self::NO_PARENT       => 'Parent not found',
    ];

    /**
     * @param mixed $code
     * @return string
     */
    public static function getMessage($code)
    {
        if (array_key_exists($code, self::LABELS)) {
            return self::LABELS[$code];
        }

        return 'Error';
    }
}