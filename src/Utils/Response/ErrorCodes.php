<?php

namespace App\Utils\Response;

class ErrorCodes
{
    // Codes

    // query error
    const QUERY_MISSING_FIELD = 100;
    const QUERY_INT_EXPECTED  = 101;

    // user error
    const USER_NOT_FOUND       = 200;
    const USER_EXISTS          = 201;
    const USER_USERNAME_EXIST  = 202;
    const USER_EMAIL_EXIST     = 203;
    const USER_EMAIL_NOT_VALID = 204;
    const USER_PASSWORD_WEAK   = 205;

    // picture error
    const PICTURE_NOT_FOUND = 300;

    // catalog error
    const CATALOG_NOT_FOUND        = 400;
    const CATALOG_PARENT_NOT_FOUND = 401;

    // license error
    const LICENSE_NOT_VALID = 500;

    // place error
    const PLACE_NOT_FOUND = 600;

    // Messages
    const LABELS = [
        self::QUERY_MISSING_FIELD      => 'Field missing',
        self::QUERY_INT_EXPECTED       => 'Integer expected',
        self::USER_NOT_FOUND           => 'User not found',
        self::USER_EXISTS              => 'User already exist',
        self::USER_USERNAME_EXIST      => 'Username already taken',
        self::USER_EMAIL_EXIST         => 'Email already taken',
        self::USER_EMAIL_NOT_VALID     => 'Email not valid',
        self::USER_PASSWORD_WEAK       => 'Password weak',
        self::PICTURE_NOT_FOUND        => 'Image not found',
        self::CATALOG_NOT_FOUND        => 'Catalog not found',
        self::CATALOG_PARENT_NOT_FOUND => 'Parent not found',
        self::LICENSE_NOT_VALID        => 'License not valid',
        self::PLACE_NOT_FOUND          => 'Place not found',
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