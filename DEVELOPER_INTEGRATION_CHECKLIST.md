# Developer Integration Checklist

## Phase 1: Understanding the System (30 minutes)

### Roles & Permissions Review
- [ ] Understand 3 roles: Super Admin, Production House, Subscriber
- [ ] Understand 5 permissions: manage_users, manage_content, upload_video, view_analytics, stream_content
- [ ] Know which permissions belong to which roles

### Middleware Understanding
- [ ] Understand role middleware: `->middleware('role:Super Admin')`
- [ ] Understand permission middleware: `->middleware('permission:upload_video')`
- [ ] Know when to use each type

### Service Layer Understanding
- [ ] Know RoleAssignmentService exists
- [ ] Know its 6 methods
- [ ] Know how to inject it in controllers

---

## Phase 2: Protect Your Routes (1-2 hours)

### Step 1: List Routes to Protect
Create a matrix of:
- Endpoint URL
- HTTP Method
- Required Role/Permission
- Current Protection Status

### Step 2: Apply Middleware
```php
// Example 1: Single permission
Route::get('/content', ...)
    ->middleware('permission:stream_content');

// Example 2: Multiple roles
Route::post('/content', ...)
    ->middleware('role:Super Admin,Production House');

// Example 3: Chain middleware
Route::delete('/content/{id}', ...)
    ->middleware('role:Super Admin')
    ->middleware('permission:manage_content');
```

### Step 3: Test Each Protected Route
- [ ] Test with appropriate role/permission (should succeed)
- [ ] Test with inappropriate role/permission (should fail with 403)
- [ ] Verify middleware returns correct status codes

---

## Phase 3: Implement Controllers (2-3 hours)

### Step 1: Create/Update Controllers
- [ ] Review example controllers for patterns
- [ ] Implement controller methods
- [ ] Add error handling

### Step 2: Use RoleAssignmentService
```php
use App\Services\RoleAssignmentService;

class AdminController {
    public function assignRole(User $user, Request $request) {
        $service = app(RoleAssignmentService::class);
        $service->assignRole($user, $request->role);
    }
}
```

### Step 3: Add Logging
- [ ] Add logging for authorization failures
- [ ] Add logging for role assignments
- [ ] Add logging for permission checks

---

## Phase 4: Test Integration (1-2 hours)

### Test Scenarios
- [ ] Scenario 1: User registration assigns Subscriber role
- [ ] Scenario 2: Subscriber can access stream endpoints
- [ ] Scenario 3: Subscriber cannot access admin endpoints
- [ ] Scenario 4: Super Admin can access everything
- [ ] Scenario 5: Production House can create content
- [ ] Scenario 6: Production House cannot manage users
- [ ] Scenario 7: Missing authentication returns 401
- [ ] Scenario 8: Insufficient permission returns 403

### Test Tools
```bash
# Use curl, Postman, Insomnia, or Thunder Client
curl -H "Authorization: Bearer {token}" GET /api/endpoint
```

---

## Phase 5: Documentation (1 hour)

### Update API Documentation
- [ ] List all protected endpoints
- [ ] Document required roles/permissions
- [ ] Add examples for each role type

### Create Access Control Matrix
```
| Endpoint | Subscriber | Production House | Super Admin |
|----------|-----------|-----------------|------------|
| /content | GET ✅    | GET/POST ✅    | All ✅     |
```

---

## Phase 6: Deployment Preparation (30 minutes)

### Pre-Deployment
- [ ] All tests pass locally
- [ ] All middleware is applied
- [ ] Error handling is complete
- [ ] Documentation is complete

### Deployment Steps
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install

# 3. Run migrations
php artisan migrate --force

# 4. Seed permissions
php artisan db:seed --class=PermissionSeeder

# 5. Clear cache
php artisan cache:clear
php artisan cache:forget spatie.permission.cache

# 6. Run tests
php artisan test
```

### Post-Deployment Verification
```bash
# Test with different roles
curl -H "Authorization: Bearer {subscriber_token}" GET /api/content
# Should work

curl -H "Authorization: Bearer {subscriber_token}" GET /api/admin/users
# Should return 403
```

---

## Phase 7: Maintenance & Monitoring (Ongoing)

### Daily
- [ ] Monitor logs for authorization failures
- [ ] Check permission cache is working

### Weekly
- [ ] Review unauthorized access attempts
- [ ] Verify role assignments are correct

### Monthly
- [ ] Review user roles
- [ ] Audit permission assignments

### Quarterly
- [ ] Review access control matrix
- [ ] Update documentation as needed

---

## Success Indicators

✅ Can protect a route in 30 seconds  
✅ All 8 test scenarios pass  
✅ Different roles have different access  
✅ Middleware returns 401/403 correctly  
✅ Logs show authorization actions  
✅ Team understands the system  
✅ Deployment is successful  

---

## Key Contacts

For questions about:
- **RBAC System:** See RBAC_QUICK_REFERENCE.md
- **Testing:** See RBAC_TESTING_GUIDE.md
- **Technical Details:** See SPATIE_PERMISSIONS_GUIDE.md
- **Integration:** See DOCUMENTATION_INDEX.md

---

## Additional Resources

- Spatie Permissions: https://spatie.be/docs/laravel-permission
- Laravel Middleware: https://laravel.com/docs/11.x/middleware
- Laravel Authorization: https://laravel.com/docs/11.x/authorization
