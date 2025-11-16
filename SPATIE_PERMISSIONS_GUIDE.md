# Spatie Permissions Integration Guide

## Overview
This guide covers the implementation of role-based access control (RBAC) using Spatie Permissions in the MOLUNZAKA application.

## Installation & Setup

### 1. Package Installation
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 2. Run Seeders
```bash
# Create all roles and permissions
php artisan db:seed --class=PermissionSeeder
```

## Roles & Permissions Structure

### Roles
- **Super Admin** - Full system access
- **Production House** - Content creators and managers
- **Subscriber** - End users with streaming access

### Permissions
| Permission | Description |
|-----------|-------------|
| manage_users | Ability to manage user accounts |
| manage_content | Ability to manage content library |
| upload_video | Ability to upload videos |
| view_analytics | Ability to view analytics dashboard |
| stream_content | Ability to stream/watch content |

### Role-Permission Mapping
```
Super Admin → [manage_users, manage_content, upload_video, view_analytics, stream_content]
Production House → [manage_content, upload_video, view_analytics]
Subscriber → [stream_content]
```

## Files Created

### 1. Seeders
- **PermissionSeeder** (`database/seeders/PermissionSeeder.php`)
  - Creates all roles and permissions
  - Assigns permissions to roles
  - Run with: `php artisan db:seed --class=PermissionSeeder`

### 2. Middleware
- **RoleMiddleware** (`app/Http/Middleware/RoleMiddleware.php`)
  - Validates user role(s)
  - Returns 403 Forbidden if unauthorized
  - Usage: `->middleware('role:Super Admin')`

- **PermissionMiddleware** (`app/Http/Middleware/PermissionMiddleware.php`)
  - Validates user permission(s)
  - Returns 403 Forbidden if unauthorized
  - Usage: `->middleware('permission:upload_video')`

### 3. Services
- **RoleAssignmentService** (`app/Services/RoleAssignmentService.php`)
  - Helper methods for role management
  - `assignDefaultRole($user, 'Subscriber')` - Assign default role
  - `assignRole($user, 'Production House')` - Assign specific role
  - `syncRoles($user, ['Super Admin'])` - Replace existing roles

### 4. Model Updates
- **User Model** - Added `HasRoles` trait from Spatie

### 5. Service Updates
- **AuthenticationService** - Auto-assigns 'Subscriber' role to new users on registration

### 6. HTTP Kernel
- Registered `role` and `permission` middleware aliases

## Usage Examples

### 1. Checking Roles (In Controller)
```php
// Check if user has specific role
if ($request->user()->hasRole('Super Admin')) {
    // Allow action
}

// Check if user has any role
if ($request->user()->hasAnyRole(['Super Admin', 'Production House'])) {
    // Allow action
}

// Check if user has all roles
if ($request->user()->hasAllRoles(['Super Admin', 'Editor'])) {
    // Allow action
}
```

### 2. Checking Permissions (In Controller)
```php
// Check if user has specific permission
if ($request->user()->hasPermissionTo('upload_video')) {
    // Allow action
}

// Check if user has any permission
if ($request->user()->hasAnyPermission(['upload_video', 'manage_content'])) {
    // Allow action
}

// Check if user has all permissions
if ($request->user()->hasAllPermissions(['upload_video', 'manage_content'])) {
    // Allow action
}
```

### 3. Route Protection (In routes/api.php)
```php
// Protect route by role
Route::post('/admin/users', [UserController::class, 'store'])
    ->middleware('auth:sanctum', 'role:Super Admin');

// Protect route by permission
Route::post('/content/upload', [ContentController::class, 'upload'])
    ->middleware('auth:sanctum', 'permission:upload_video');

// Protect route by multiple roles (user must have ANY role)
Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->middleware('auth:sanctum', 'role:Super Admin,Production House');

// Protect route by multiple permissions
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('auth:sanctum', 'permission:view_analytics,manage_users');
```

### 4. Using RoleAssignmentService
```php
use App\Services\RoleAssignmentService;

// Assign default role (Subscriber)
RoleAssignmentService::assignDefaultRole($user);

// Assign specific role
RoleAssignmentService::assignRole($user, 'Production House');

// Assign multiple roles
RoleAssignmentService::assignRoles($user, ['Production House', 'Subscriber']);

// Replace all roles
RoleAssignmentService::syncRoles($user, ['Super Admin']);

// Remove specific role
RoleAssignmentService::removeRole($user, 'Subscriber');
```

### 5. In Blade Templates
```blade
@if(auth()->user()->hasRole('Super Admin'))
    <a href="/admin">Admin Dashboard</a>
@endif

@if(auth()->user()->can('upload_video'))
    <button>Upload Video</button>
@endif
```

## API Response Examples

### Unauthorized Role Access
```json
{
    "message": "Unauthorized. Required role: Super Admin",
    "status": 403
}
```

### Unauthorized Permission Access
```json
{
    "message": "Unauthorized. Required permission: upload_video",
    "status": 403
}
```

## Database Tables Created by Spatie

The migration creates the following tables:
- `roles` - Stores role definitions
- `permissions` - Stores permission definitions
- `model_has_roles` - Associates users with roles
- `model_has_permissions` - Associates users with permissions
- `role_has_permissions` - Associates roles with permissions

## Cache Management

Spatie caches permissions by default. To refresh cache after making changes:

```php
// Manually in code
app()['cache']->forget('spatie.permission.cache');

// Or via artisan
php artisan cache:forget spatie.permission.cache
```

## Testing Role Assignment

Test if roles are assigned correctly on user registration:

```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "password": "SecurePass123!@",
    "password_confirmation": "SecurePass123!@",
    "phone": "+1234567890",
    "date_of_birth": "1995-05-20"
  }'
```

Then verify the user has the 'Subscriber' role by querying the database.

## Advanced: Custom Role Logic

To add custom logic to role assignment:

1. Create a custom event when user registers
2. Listen to the event and assign roles based on business logic
3. Example: Auto-assign 'Production House' role if user signs up with company email

```php
// In your event listener
if (str_ends_with($user->email, '@company.com')) {
    RoleAssignmentService::assignRole($user, 'Production House');
}
```

## Troubleshooting

### Issue: Middleware returns 401 instead of 403
- Ensure user is authenticated first: `->middleware('auth:sanctum')`
- Middleware checks `$request->user()` first

### Issue: Roles not applying after assignment
- Clear the Spatie permission cache
- Use `php artisan cache:clear`

### Issue: New permissions not working
- Re-run seeder or manually create permissions
- Ensure role-permission associations are correct

## Summary

✅ Spatie Permissions installed and configured  
✅ Roles created: Super Admin, Production House, Subscriber  
✅ Permissions created: manage_users, manage_content, upload_video, view_analytics, stream_content  
✅ RoleMiddleware implemented for role-based route protection  
✅ PermissionMiddleware implemented for permission-based route protection  
✅ RoleAssignmentService created for role management  
✅ Default 'Subscriber' role assigned to new users on registration  
✅ Kernel middleware aliases configured  
