<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()['cache']->forget('spatie.permission.cache');

        // Define permissions
        $permissions = [
            'manage_users',
            'manage_content',
            'upload_video',
            'view_analytics',
            'stream_content',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Define roles
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $productionHouseRole = Role::findOrCreate('Production House', 'web');
        $subscriberRole = Role::findOrCreate('Subscriber', 'web');

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to Production House
        $productionHouseRole->syncPermissions([
            'manage_content',
            'upload_video',
            'view_analytics',
        ]);

        // Assign permissions to Subscriber
        $subscriberRole->syncPermissions([
            'stream_content',
        ]);
    }
}
