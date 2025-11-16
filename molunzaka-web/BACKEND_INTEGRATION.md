# Molunzaka Web - Backend Integration Guide

## Backend Requirements

The frontend expects a Laravel API with the following endpoints and structure.

## Authentication Endpoints

### POST `/api/auth/register`

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

**Response (Success):**
```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "roles": ["Subscriber"]
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### POST `/api/auth/login`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

**Response (Success):**
```json
{
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-11-16T10:00:00Z",
    "roles": ["Subscriber"],
    "profiles": [
      {
        "id": 1,
        "name": "John's Profile",
        "avatar": "avatar-1.png"
      }
    ]
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### POST `/api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Logout successful"
}
```

### POST `/api/auth/verify-email`

**Request:**
```json
{
  "token": "verification_token_from_email"
}
```

**Response:**
```json
{
  "message": "Email verified successfully"
}
```

### POST `/api/auth/resend-verification`

**Request:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "message": "Verification email sent"
}
```

### POST `/api/auth/forgot-password`

**Request:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "message": "Password reset link sent to email"
}
```

### POST `/api/auth/reset-password`

**Request:**
```json
{
  "token": "reset_token_from_email",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Response:**
```json
{
  "message": "Password reset successful"
}
```

### GET `/api/auth/me`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "email_verified_at": "2024-11-16T10:00:00Z",
  "roles": ["Subscriber"],
  "profiles": [...]
}
```

### PUT `/api/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "first_name": "Jane",
  "last_name": "Doe"
}
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "user": {...}
}
```

### POST `/api/auth/change-password`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "current_password": "OldPassword123!",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Response:**
```json
{
  "message": "Password changed successfully"
}
```

## Video Endpoints

### GET `/api/videos`

**Query Parameters:**
- `page`: Pagination page (default: 1)
- `per_page`: Items per page (default: 20)
- `search`: Search query

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Video Title",
      "description": "Video description",
      "url": "path/to/video.mp4",
      "thumbnail": "path/to/thumbnail.jpg",
      "duration": 3600,
      "views": 1500,
      "created_at": "2024-11-16T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1
  }
}
```

### GET `/api/videos/{id}`

**Response:**
```json
{
  "id": 1,
  "title": "Video Title",
  "description": "Description",
  "url": "path/to/video.mp4",
  "thumbnail": "path/to/thumbnail.jpg",
  "duration": 3600,
  "views": 1500,
  "rating": 4.5,
  "comments_count": 25,
  "created_at": "2024-11-16T10:00:00Z"
}
```

### POST `/api/videos` (Upload)

**Headers:** 
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Form Data:**
- `title`: string
- `description`: string
- `video`: file (required)
- `thumbnail`: file (optional)

**Response:**
```json
{
  "message": "Video uploaded successfully",
  "video": {...}
}
```

### PUT `/api/videos/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "title": "Updated Title",
  "description": "Updated description"
}
```

**Response:**
```json
{
  "message": "Video updated successfully",
  "video": {...}
}
```

### DELETE `/api/videos/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Video deleted successfully"
}
```

## Profile Endpoints

### GET `/api/profiles`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "name": "Main Profile",
      "avatar": "avatar.png",
      "created_at": "2024-11-16T10:00:00Z"
    }
  ]
}
```

### POST `/api/profiles`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "name": "New Profile",
  "avatar": "avatar_file.png"
}
```

**Response:**
```json
{
  "message": "Profile created successfully",
  "profile": {...}
}
```

### PUT `/api/profiles/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "name": "Updated Profile",
  "avatar": "new_avatar.png"
}
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "profile": {...}
}
```

### DELETE `/api/profiles/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Profile deleted successfully"
}
```

### POST `/api/profiles/{id}/set-active`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Profile set as active"
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized access"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

### 500 Server Error
```json
{
  "message": "Internal server error"
}
```

## CORS Configuration

The backend must allow CORS from the frontend domain:

```php
// config/cors.php
'allowed_origins' => ['http://localhost:5173', 'https://yourdomain.com'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['Content-Type', 'Authorization'],
'exposed_headers' => ['Content-Length'],
'max_age' => 86400,
'supports_credentials' => true,
```

## Token Format

The API must return JWT tokens. Example implementation:

```php
// AuthController.php
$token = auth()->attempt($credentials);

return response()->json([
    'user' => auth()->user(),
    'token' => $token
]);
```

## Frontend Token Usage

The frontend automatically:
1. Stores token in `localStorage.getItem('token')`
2. Adds token to all API requests: `Authorization: Bearer {token}`
3. Handles 401 responses by clearing storage and redirecting to login

## Environment Setup

### Backend (.env)
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
DB_DATABASE=molunzaka_db
JWT_SECRET=your-secret-key
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
```

## Testing Integration

1. **Register a new user:**
   ```bash
   curl -X POST http://localhost:8000/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{
       "first_name": "Test",
       "last_name": "User",
       "email": "test@example.com",
       "password": "TestPassword123!",
       "password_confirmation": "TestPassword123!"
     }'
   ```

2. **Login:**
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "test@example.com",
       "password": "TestPassword123!"
     }'
   ```

3. **Access protected endpoint:**
   ```bash
   curl -X GET http://localhost:8000/api/auth/me \
     -H "Authorization: Bearer {token_from_login}"
   ```

## Troubleshooting

### CORS Error
- Ensure backend CORS headers are set correctly
- Check browser console for specific CORS errors
- Verify frontend URL is in allowed origins

### 401 Unauthorized
- Check if token is being sent in Authorization header
- Verify token hasn't expired
- Ensure backend validates token correctly

### Invalid JSON Response
- Check backend is returning proper JSON
- Verify Content-Type header is `application/json`
- Look for PHP errors in backend logs

### Token Not Stored
- Check if browser allows localStorage
- Verify login response includes token
- Check browser's privacy/incognito mode restrictions

---

**Last Updated:** November 16, 2025
