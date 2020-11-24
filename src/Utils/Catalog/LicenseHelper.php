<?php

namespace App\Utils\Catalog;

class LicenseHelper
{
    const CC_BY               = 'CC BY';
    const CC_BY_SA            = 'CC BY-SA';
    const CC_BY_ND            = 'CC BY-ND';
    const CC_BY_NC            = 'CC BY-NC';
    const CC_BY_NC_SA         = 'CC BY-NC-SA';
    const CC_BY_NC_ND         = 'CC BY-NC-ND';
    const CC_BY_SA_30_IGO     = 'CC BY-SA 3.0 IGO';
    const ALL_RIGHTS_RESERVED = 'All rights reserved';
    const PUBLIC_DOMAIN       = 'Public Domain';

    /**
     * @return string[]
     */
    public static function getLicenses()
    {
        return [
            self::CC_BY,
            self::CC_BY_SA,
            self::CC_BY_ND,
            self::CC_BY_NC,
            self::CC_BY_NC_SA,
            self::CC_BY_NC_ND,
            self::CC_BY_SA_30_IGO,
            self::ALL_RIGHTS_RESERVED,
            self::PUBLIC_DOMAIN,
        ];
    }

    /**
     * @param string|null $license
     * @return bool
     */
    public static function isValidLicense(?string $license): bool
    {
        if (!$license) {
            return true;
        }
        return in_array($license, self::getLicenses());
    }
}