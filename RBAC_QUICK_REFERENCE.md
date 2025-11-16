# RBAC Quick Reference Guide

## ⚡ Quick Start - 5 Most Common Tasks

### Task 1: Protect Route with Role (30 seconds)
```php
Route::post('/admin/users', [AdminController::class, 'store'])
    ->middleware('role:Super Admin');
```

### Task 2: Protect Route with Permission (30 seconds)
```php
Route::post('/content/upload', [ContentController::class, 'upload'])
    ->middleware('permission:upload_video');
```

### Task 3: Check Permission in Controller (1 minute)
```php
public function upload(Request $request) {
    if ($request->user()->can('upload_video')) {
        // Proceed with upload
    }
}
```

### Task 4: Assign Role to User (1 minute)
```php
use App\Services\RoleAssignmentService;

$service = app(RoleAssignmentService::class);
$service->assignRole($user, 'Production House');
```

### Task 5: Get User's Roles & Permissions (30 seconds)
```php
$roles = $user->getRoleNames();           // ["Subscriber", "Producer"]
$permissions = $user->getAllPermissions(); // All permissions for all roles
```

---

## 🎯 Available Roles

| Role | Permissions | Who Gets This |
|------|------------|---------------|
| **Super Admin** | manage_users, manage_content, upload_video, view_analytics, stream_content | Platform administrators |
| **Production House** | manage_content, upload_video, view_analytics | Content creators/producers |
| **Subscriber** | stream_content | Regular users |

---

## 🔐 Available Permissions

| Permission | Use Case | Who Has It |
|-----------|----------|-----------|
| **manage_users** | Create/delete users, assign roles | Super Admin only |
| **manage_content** | Approve/reject content, moderation | Super Admin, Production House |
| **upload_video** | Upload video files | Super Admin, Production House |
| **view_analytics** | View analytics dashboard | Super Admin, Production House |
| **stream_content** | Watch/stream content | Everyone (including Subscribers) |

---

## 🛣️ Middleware Patterns

### Pattern 1: Single Role
```php
->middleware('role:Super Admin')
```

### Pattern 2: Multiple Roles (ANY matches)
```php
->middleware('role:Super Admin,Production House')
```

### Pattern 3: Single Permission
```php
->middleware('permission:upload_video')
```

### Pattern 4: Multiple Permissions (ANY matches)
```php
->middleware('permission:upload_video,manage_content')
```

### Pattern 5: Chain Multiple Middleware (ALL required)
```php
->middleware('role:Super Admin')
->middleware('permission:manage_content')
```

---

## 📋 Complete Examples

### Example 1: Simple Content Endpoint
```php
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/content', [ContentController::class, 'index'])
        ->middleware('permission:stream_content');
    
    Route::post('/content', [ContentController::class, 'store'])
        ->middleware('role:Super Admin,Production House');
});
```

### Example 2: Admin Routes
```php
Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'role:Super Admin']], function () {
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:manage_users');
    
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])
        ->middleware('permission:manage_users');
});
```

### Example 3: Multiple Protection
```php
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('content')->group(function () {
        // Accessible to anyone with stream_content permission
        Route::get('/', [ContentController::class, 'index'])
            ->middleware('permission:stream_content');
        
        // Only Production House and Super Admin
        Route::post('/', [ContentController::class, 'store'])
            ->middleware('role:Production House,Super Admin');
        
        // Super Admin only
        Route::delete('/{id}', [ContentController::class, 'destroy'])
            ->middleware('role:Super Admin');
    });
});
```

---

## 🎮 Service Usage Examples

### Example 1: Assign Single Role
```php
use App\Services\RoleAssignmentService;

public function assignRole(User $user, Request $request) {
    $service = app(RoleAssignmentService::class);
    $service->assignRole($user, 'Production House');
    
    return response()->json(['message' => 'Role assigned']);
}
```

### Example 2: Assign Multiple Roles
```php
$service = app(RoleAssignmentService::class);
$service->assignRoles($user, ['Production House', 'Subscriber']);
```

### Example 3: Replace All Roles
```php
$service = app(RoleAssignmentService::class);
$service->syncRoles($user, ['Production House']); // Removes other roles
```

### Example 4: Remove Role
```php
$service = app(RoleAssignmentService::class);
$service->removeRole($user, 'Subscriber');
```

---

## ✅ Response Status Codes

| Scenario | Status | Example Response |
|----------|--------|-----------------|
| Success | 200 | `{"message": "Success", "data": {...}}` |
| Created | 201 | `{"message": "Created", "data": {...}}` |
| Not Authenticated | 401 | `{"message": "Unauthenticated"}` |
| Missing Role | 403 | `{"message": "User does not have required role: X"}` |
| Missing Permission | 403 | `{"message": "User does not have required permission: X"}` |
| Server Error | 500 | `{"message": "Error", "error": "..."}` |

---

## 🧪 Quick Testing

### Test 1: Verify User Gets Subscriber Role on Registration
```bash
# Register user
POST /api/auth/register
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}

# Then in tinker, verify:
php artisan tinker
>>> User::where('email', 'test@example.com')->first()->getRoleNames()
# Should output: ["Subscriber"]
```

### Test 2: Verify Permission Works
```bash
# Login and get token
POST /api/auth/login
{
  "email": "test@example.com",
  "password": "Password123!"
}

# Use token to access content
GET /api/content
Authorization: Bearer {token}
# Should return 200 OK with content

# Try to access admin
GET /api/admin/users
Authorization: Bearer {token}
# Should return 403 Forbidden
```

### Test 3: Verify Role Assignment
```bash
# In tinker, assign Production House role
>>> $user = User::find(1)
>>> $user->assignRole('Production House')

# Then test access
GET /api/content
Authorization: Bearer {token}
# Should now be able to create content
```

---

## 🔧 Troubleshooting

### Issue: Middleware returns 401 instead of 403
**Cause:** Authentication token is missing or invalid  
**Solution:** Ensure Authorization header is present with valid token
```bash
curl -H "Authorization: Bearer {valid_token}" GET /api/endpoint
```

### Issue: Permission check fails after assignment
**Cause:** Permission cache hasn't been cleared  
**Solution:** Clear the cache
```bash
php artisan cache:forget spatie.permission.cache
```

### Issue: User doesn't have expected permission
**Cause:** Role wasn't properly assigned or permission isn't in role  
**Solution:** Verify in tinker
```bash
php artisan tinker
>>> $user->getRoleNames()
>>> $user->can('permission_name')
```

### Issue: "Role does not exist" error
**Cause:** Role not created in database  
**Solution:** Reseed permissions
```bash
php artisan db:seed --class=PermissionSeeder
php artisan cache:forget spatie.permission.cache
```

---

## 📚 File Locations

| Component | File |
|-----------|------|
| RoleMiddleware | `app/Http/Middleware/RoleMiddleware.php` |
| PermissionMiddleware | `app/Http/Middleware/PermissionMiddleware.php` |
| RoleAssignmentService | `app/Services/RoleAssignmentService.php` |
| PermissionSeeder | `database/seeders/PermissionSeeder.php` |
| Example Routes | `routes/api-roles-example.php` |
| Example Controllers | `app/Http/Controllers/Api/` |

---

## 🚀 Next Steps

1. **Protect your routes** - Use patterns from above
2. **Test scenarios** - See RBAC_TESTING_GUIDE.md
3. **Deploy** - See DEVELOPER_INTEGRATION_CHECKLIST.md
4. **Monitor** - Watch logs for authorization issues

---

## 📞 More Help

- **Testing:** See RBAC_TESTING_GUIDE.md
- **Integration:** See DEVELOPER_INTEGRATION_CHECKLIST.md  
- **Deep Dive:** See SPATIE_PERMISSIONS_GUIDE.md
- **Navigation:** See DOCUMENTATION_INDEX.md
