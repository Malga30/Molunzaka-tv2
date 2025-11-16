# RBAC Testing Guide

## Overview
Complete testing procedures for the Role-Based Access Control system with 8 test scenarios.

---

## Test Scenario 1: User Registration & Default Role Assignment

**Objective:** Verify new users automatically get Subscriber role

**Steps:**
1. Register a user:
```bash
POST /api/auth/register
Content-Type: application/json

{
  "name": "Test Subscriber",
  "email": "subscriber@test.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

2. Verify role in tinker:
```bash
php artisan tinker
>>> User::where('email', 'subscriber@test.com')->first()->getRoleNames()
```

**Expected Result:**
- Response: 201 Created with user data and token
- User has roles: `["Subscriber"]`

---

## Test Scenario 2: Stream Content Permission (Allowed)

**Objective:** Subscriber can access stream endpoints

**Setup:**
- Get token from Scenario 1 registration or login

**Test:**
```bash
GET /api/content
Authorization: Bearer {subscriber_token}
```

**Expected Result:**
- Status: 200 OK
- User can access content

---

## Test Scenario 3: Upload Video Permission (Denied)

**Objective:** Subscriber cannot upload videos

**Test:**
```bash
POST /api/content/upload
Authorization: Bearer {subscriber_token}
Content-Type: multipart/form-data

video=<file>
```

**Expected Result:**
- Status: 403 Forbidden
- Message: "User does not have the required permission: upload_video"

---

## Test Scenario 4: Role-Based Access Control

**Objective:** Only Super Admin accesses admin endpoints

**Test 1: Subscriber tries admin endpoint**
```bash
GET /api/admin/users
Authorization: Bearer {subscriber_token}
```

**Expected Result:**
- Status: 403 Forbidden
- Message: "User does not have the required role: Super Admin"

**Test 2: Super Admin accesses admin endpoint**
```bash
# Create Super Admin user first in tinker:
php artisan tinker
>>> $admin = User::factory()->create(['email' => 'admin@test.com'])
>>> $admin->assignRole('Super Admin')

# Login and get token, then:
GET /api/admin/users
Authorization: Bearer {admin_token}
```

**Expected Result:**
- Status: 200 OK

---

## Test Scenario 5: Multiple Role Check

**Objective:** Endpoints with multiple roles work correctly

**Setup:**
- Create Production House user:
```bash
php artisan tinker
>>> $producer = User::factory()->create(['email' => 'producer@test.com'])
>>> $producer->assignRole('Production House')
```

**Test: Production House user creates content**
```bash
POST /api/content
Authorization: Bearer {producer_token}
Content-Type: application/json

{
  "title": "New Video",
  "description": "Test video"
}
```

**Expected Result:**
- Status: 201 Created
- Content created successfully

---

## Test Scenario 6: Unauthenticated Access

**Objective:** Missing token returns 401

**Test:**
```bash
GET /api/content
# No Authorization header
```

**Expected Result:**
- Status: 401 Unauthorized
- Message: "Unauthenticated"

---

## Test Scenario 7: Invalid Token

**Objective:** Invalid token returns 401

**Test:**
```bash
GET /api/content
Authorization: Bearer invalid_token_here
```

**Expected Result:**
- Status: 401 Unauthorized

---

## Test Scenario 8: Role Assignment Endpoint

**Objective:** Super Admin can assign roles to users

**Setup:**
- Have Super Admin token and a Subscriber user ID

**Test:**
```bash
POST /api/admin/users/{user_id}/assign-role
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "role": "Production House"
}
```

**Verify:**
```bash
php artisan tinker
>>> User::find({user_id})->getRoleNames()
```

**Expected Result:**
- Status: 200 OK
- User now has both Subscriber and Production House roles

---

## Quick Debugging Commands

### Check All Roles
```bash
php artisan tinker
>>> Role::all()
```

### Check User's Roles
```bash
>>> $user = User::find(1)
>>> $user->getRoleNames()
```

### Check User's All Permissions
```bash
>>> $user->getAllPermissions()->pluck('name')
```

### Test Permission Check
```bash
>>> $user->can('upload_video')
```

### Check Role Assignment
```bash
>>> $user->hasRole('Super Admin')
```

### Clear Permission Cache
```bash
php artisan cache:forget spatie.permission.cache
# Or outside tinker:
# php artisan cache:forget spatie.permission.cache
```

---

## Testing Checklist

- [ ] Scenario 1: User registration assigns Subscriber role
- [ ] Scenario 2: Subscriber can stream content (200)
- [ ] Scenario 3: Subscriber cannot upload (403)
- [ ] Scenario 4: Non-admin cannot access admin (403)
- [ ] Scenario 4: Admin can access admin (200)
- [ ] Scenario 5: Producer can create content (201)
- [ ] Scenario 6: No token returns 401
- [ ] Scenario 7: Invalid token returns 401
- [ ] Scenario 8: Role assignment works

---

## Common Issues & Solutions

### Issue: Middleware returns 401 instead of 403
**Cause:** No valid authentication token  
**Solution:** Include valid token in Authorization header

### Issue: Permission check always fails
**Cause:** Cache not cleared after role changes  
**Solution:** Run `php artisan cache:forget spatie.permission.cache`

### Issue: User doesn't have expected role after assignment
**Cause:** Role not properly assigned or cache issue  
**Solution:** Verify in tinker and clear cache

### Issue: "Role/Permission does not exist"
**Cause:** Role/permission not seeded  
**Solution:** Run `php artisan db:seed --class=PermissionSeeder`

---

## API Response Examples

### Success Response (200)
```json
{
  "message": "Content retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Video Title",
      "description": "Description"
    }
  ]
}
```

### Created Response (201)
```json
{
  "message": "Content created successfully",
  "data": {
    "id": 1,
    "title": "New Video",
    "created_by": "producer@test.com"
  }
}
```

### Permission Denied (403)
```json
{
  "message": "User does not have the required permission: upload_video",
  "required_permission": "upload_video",
  "user_permissions": ["stream_content"]
}
```

### Role Denied (403)
```json
{
  "message": "User does not have the required role: Super Admin",
  "required_role": "Super Admin",
  "user_roles": ["Subscriber"]
}
```

### Not Authenticated (401)
```json
{
  "message": "Unauthenticated"
}
```

---

## Test Tools

### Option 1: cURL
```bash
curl -H "Authorization: Bearer {token}" GET https://localhost:8000/api/endpoint
```

### Option 2: Postman
- Set Authorization header
- Set Bearer token
- Make request

### Option 3: Insomnia
- Set Auth type to Bearer
- Paste token
- Make request

### Option 4: Thunder Client (VS Code)
- Install Thunder Client extension
- Set Authorization
- Make request

---

## Next Steps

1. ✅ Run all 8 scenarios
2. ✅ Verify all pass
3. ✅ Test with your own endpoints
4. ✅ Document any custom scenarios
5. ✅ Deploy when ready

---

## Support

- For more help: See RBAC_QUICK_REFERENCE.md
- For implementation: See DEVELOPER_INTEGRATION_CHECKLIST.md
- For navigation: See DOCUMENTATION_INDEX.md
