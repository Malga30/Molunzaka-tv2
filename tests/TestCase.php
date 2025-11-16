<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed permissions and roles for tests
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    /**
     * Create a user with a specific role.
     *
     * @param string $roleName
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createUserWithRole(string $roleName, array $attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        $user->assignRole($roleName);
        return $user;
    }

    /**
     * Create multiple users with different roles.
     *
     * @param array $roles
     * @return array
     */
    protected function createUsersWithRoles(array $roles)
    {
        $users = [];
        foreach ($roles as $role) {
            $users[$role] = $this->createUserWithRole($role);
        }
        return $users;
    }
}
