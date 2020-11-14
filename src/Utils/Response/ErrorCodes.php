<?php

namespace App\Utils\Response;

class ErrorCodes
{
    // query error
    const MISSING_FIELD = 100;

    // user error
    const NO_USER         = 200;
    const USER_EXISTS     = 201;
    const USERNAME_EXIST  = 202;
    const EMAIL_EXIST     = 203;
    const EMAIL_NOT_VALID = 204;
    const PASSWORD_WEAK   = 205;
}