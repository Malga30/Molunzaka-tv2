<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\RoleAssignmentService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAssignmentServiceTest extends TestCase
{
    private RoleAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleAssignmentService();
    }

    /**
     * Test assign basic subscriber role.
     *
     * @test
     */
    public function assign_basic_subscriber_role(): void
    {
        $user = User::factory()->create();

        $result = $this->service->assignBasicSubscriberRole($user);

        $this->assertTrue($result);
        $this->assertTrue($user->hasRole('Subscriber'));
    }

    /**
     * Test assign production house role.
     *
     * @test
     */
    public function assign_production_house_role(): void
    {
        $user = User::factory()->create();

        $result = $this->service->assignProductionHouseRole($user);

        $this->assertTrue($result);
        $this->assertTrue($user->hasRole('Production House'));
    }

    /**
     * Test assign multiple roles.
     *
     * @test
     */
    public function assign_multiple_roles(): void
    {
        $user = User::factory()->create();

        $result = $this->service->assignRoles($user, ['Subscriber', 'Production House']);

        $this->assertTrue($result);
        $this->assertTrue($user->hasRole('Subscriber'));
        $this->assertTrue($user->hasRole('Production House'));
    }

    /**
     * Test revoke role.
     *
     * @test
     */
    public function revoke_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $result = $this->service->revokeRole($user, 'Subscriber');

        $this->assertTrue($result);
        $this->assertFalse($user->hasRole('Subscriber'));
    }

    /**
     * Test revoke all roles.
     *
     * @test
     */
    public function revoke_all_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Subscriber', 'Production House']);

        $result = $this->service->revokeAllRoles($user);

        $this->assertTrue($result);
        $this->assertFalse($user->hasRole('Subscriber'));
        $this->assertFalse($user->hasRole('Production House'));
    }

    /**
     * Test sync roles.
     *
     * @test
     */
    public function sync_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Subscriber', 'Production House']);

        $result = $this->service->syncRoles($user, ['Super Admin']);

        $this->assertTrue($result);
        $this->assertFalse($user->hasRole('Subscriber'));
        $this->assertFalse($user->hasRole('Production House'));
        $this->assertTrue($user->hasRole('Super Admin'));
    }

    /**
     * Test get user roles.
     *
     * @test
     */
    public function get_user_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Subscriber', 'Production House']);

        $roles = $this->service->getUserRoles($user);

        $this->assertCount(2, $roles);
        $this->assertContains('Subscriber', $roles);
        $this->assertContains('Production House', $roles);
    }

    /**
     * Test check if user has role.
     *
     * @test
     */
    public function check_if_user_has_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $result = $this->service->userHasRole($user, 'Subscriber');

        $this->assertTrue($result);
        $this->assertFalse($this->service->userHasRole($user, 'Super Admin'));
    }

    /**
     * Test check if user has any role.
     *
     * @test
     */
    public function check_if_user_has_any_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $result = $this->service->userHasAnyRole($user, ['Subscriber', 'Super Admin']);

        $this->assertTrue($result);
        $this->assertFalse($this->service->userHasAnyRole($user, ['Super Admin', 'Production House']));
    }

    /**
     * Test check if user has all roles.
     *
     * @test
     */
    public function check_if_user_has_all_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Subscriber', 'Production House']);

        $result = $this->service->userHasAllRoles($user, ['Subscriber', 'Production House']);

        $this->assertTrue($result);
        $this->assertFalse($this->service->userHasAllRoles($user, ['Subscriber', 'Production House', 'Super Admin']));
    }

    /**
     * Test assign permission to role.
     *
     * @test
     */
    public function assign_permission_to_role(): void
    {
        $role = Role::create(['name' => 'Custom Role']);

        $result = $this->service->assignPermissionToRole($role, 'stream_content');

        $this->assertTrue($result);
        $this->assertTrue($role->hasPermissionTo('stream_content'));
    }

    /**
     * Test revoke permission from role.
     *
     * @test
     */
    public function revoke_permission_from_role(): void
    {
        $role = Role::create(['name' => 'Custom Role']);
        $role->givePermissionTo('stream_content');

        $result = $this->service->revokePermissionFromRole($role, 'stream_content');

        $this->assertTrue($result);
        $this->assertFalse($role->hasPermissionTo('stream_content'));
    }
}
