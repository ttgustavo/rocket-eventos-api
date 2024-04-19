<?php

namespace Tests\Feature;

use App\Domain\Model\UserPermissions;
use Database\Factories\UserFactory;
use Laravel\Sanctum\Sanctum;

trait AuthHelperTrait
{
    private function authAsUser(int $id = 1): void
    {
        Sanctum::actingAs(UserFactory::new(['id' => $id])->makeOne());
    }

    private function authAsAdmin(): void
    {
        Sanctum::actingAs(UserFactory::new()->makeOne(), [UserPermissions::Administrator]);
    }

    private function authAsSuper(): void
    {
        Sanctum::actingAs(UserFactory::new()->makeOne(), [UserPermissions::Super]);
    }

    private function authAsAdminAndSuper(): void
    {
        $permissionsString = UserPermissions::getStringPermissionForAdminAndSuper();
        $permissionsArray = explode(',', $permissionsString, 2);

        Sanctum::actingAs(UserFactory::new()->makeOne(), $permissionsArray);
    }
}
