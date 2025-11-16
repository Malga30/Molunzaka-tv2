<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleAssignmentService
{
    /**
     * Assign default role to new user
     *
     * @param User $user
     * @param string $roleName
     * @return void
     */
    public static function assignDefaultRole(User $user, string $roleName = 'Subscriber'): void
    {
        // Get or create the role
        $role = Role::findByName($roleName, 'web');

        // Assign role to user
        $user->assignRole($role);
    }

    /**
     * Assign role by name
     *
     * @param User $user
     * @param string $roleName
     * @return void
     */
    public static function assignRole(User $user, string $roleName): void
    {
        $role = Role::findByName($roleName, 'web');
        $user->assignRole($role);
    }

    /**
     * Assign multiple roles
     *
     * @param User $user
     * @param array $roleNames
     * @return void
     */
    public static function assignRoles(User $user, array $roleNames): void
    {
        foreach ($roleNames as $roleName) {
            $user->assignRole($roleName);
        }
    }

    /**
     * Remove role
     *
     * @param User $user
     * @param string $roleName
     * @return void
     */
    public static function removeRole(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
    }

    /**
     * Sync roles (replace existing roles)
     *
     * @param User $user
     * @param array $roleNames
     * @return void
     */
    public static function syncRoles(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);
    }
}
