<?php

namespace App\Domain\Model;

class UserPermissions
{
    /** @var string Administrator can administrate users, events and attendees, */
    const Administrator = 'admin';

    /** @var string Super can administrate as admin, plus can administrate administrators. */
    const Super = 'super';

    public static function getStringPermissionForAdminAndSuper(): string
    {
        return self::Administrator .  ',' . self::Super;
    }
}
