<?php

namespace App\Utils\Response;

class Errors
{
    // query error
    const QUERY_MISSING_FIELD = [100, 'Field missing'];
    const QUERY_INT_EXPECTED  = [101, 'Integer expected'];

    // user error
    const USER_NOT_FOUND                        = [200, 'User not found'];
    const USER_USERNAME_EXIST                   = [201, 'Username already exist'];
    const USER_EMAIL_EXIST                      = [203, 'Email already taken'];
    const USER_EMAIL_NOT_VALID                  = [204, 'Email not valid'];
    const USER_PASSWORD_WEAK                    = [205, 'Password is too weak'];
    const USER_INVALID_LOGIN                    = [210, 'Invalid login username or password'];

    const RECOVERY_INVALID_TOKEN = [212, 'Invalid account recovery token'];
    const RECOVERY_EXPIRED_TOKEN = [213, 'Expired recovery token'];

    // picture error
    const PICTURE_NOT_FOUND = [300, 'Picture not found'];

    // catalog error
    const CATALOG_NOT_FOUND        = [400, 'Catalog not found'];
    const CATALOG_PARENT_NOT_FOUND = [401, 'Catalog parent not found'];

    // license error
    const LICENSE_NOT_VALID = [500, 'License not valid'];

    // place error
    const PLACE_NOT_FOUND = [600, 'Place not found'];

    const UNKNOWN_ERROR = [000, 'Unknown error'];

    private static ?array $parsed = null;

    /**
     * Parse all errors and return a pretty array, only use to list errors, not actually used to make api error
     * response working
     * @return array
     */
    public static function parseConstants(): array
    {
        if (self::$parsed === null) {
            $class        = new \ReflectionClass(self::class);
            $errors       = $class->getConstants();
            $errorsParsed = [];
            foreach ($errors as $key => $error) {
                $errorsParsed[] = [
                    'code'    => $error[0],
                    'key'     => $key,
                    'message' => $error[1],
                ];
            }
            self::$parsed = $errorsParsed;
        }
        return self::$parsed;
    }

    /**
     * Get message of a error
     * @param string $key
     * @return string
     */
    public static function getMessage(string $key): string
    {
        if (($err = self::get($key)) !== null) {
            return $err[1];
        }

        return 'Error';
    }

    public static function get(string $key): ?array
    {
        $key = 'self::' . $key;
        if (defined($key)) {
            return constant($key);
        }
        return null;
    }

    public static function getKeyFromCode(int $code)
    {
        $parsed = self::parseConstants();
        $errors = array_filter($parsed, fn($e) => $e['code'] == $code);
        return reset($errors)['key'];
    }
}