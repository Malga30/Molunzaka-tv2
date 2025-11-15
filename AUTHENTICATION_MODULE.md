# MOLUNZAKA Authentication Module - Complete Implementation

## Overview

This is a complete, production-ready authentication module for the MOLUNZAKA video-streaming platform built with Laravel 10 and Laravel Sanctum. It provides secure user registration, login, email verification, password reset, and token-based API authentication.

## Features

✅ **User Registration** - Secure registration with form validation
✅ **Email Verification** - Email-based account verification with notifications
✅ **User Login** - Secure login with Sanctum token generation
✅ **Password Reset** - Forgot password and reset password flows
✅ **Logout** - Single device and multi-device logout support
✅ **API Tokens** - Sanctum personal access tokens
✅ **Rate Limiting** - 60 requests per minute for all auth endpoints
✅ **Form Validation** - Comprehensive form request validation
✅ **Password Policies** - Enforced password requirements (8+ chars, mixed case, numbers, symbols)
✅ **Email Notifications** - Queue-able email notifications
✅ **User Profile** - Retrieve authenticated user information

## Project Structure

```
/workspaces/Molunzaka-tv2/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── AuthController.php
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── ForgotPasswordRequest.php
│   │   │   └── ResetPasswordRequest.php
│   │   └── Middleware/
│   ├── Models/
│   │   └── User.php
│   ├── Services/
│   │   └── AuthenticationService.php
│   └── Notifications/
│       ├── VerifyEmailNotification.php
│       └── ResetPasswordNotification.php
├── routes/
│   └── api.php
├── database/
│   └── migrations/
│       ├── 2024_11_15_000001_create_users_table.php
│       ├── 2024_11_15_000002_create_personal_access_tokens_table.php
│       └── 2024_11_15_000003_create_password_reset_tokens_table.php
├── config/
│   ├── app.php
│   └── database.php
├── composer.json
└── .env.example
```

## API Endpoints

### Public Endpoints (Rate Limited: 60 req/min)

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "phone": "+1234567890",
    "date_of_birth": "1990-01-15"
}
```

**Response (201 Created):**
```json
{
    "message": "User registered successfully. Please check your email to verify your account.",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "date_of_birth": "1990-01-15",
            "email_verified_at": null,
            "created_at": "2024-11-15T10:30:00Z"
        },
        "token": "1|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "token_type": "Bearer"
    }
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "SecurePass123!",
    "remember_me": false
}
```

**Response (200 OK):**
```json
{
    "message": "Login successful.",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "email_verified_at": "2024-11-15T11:00:00Z"
        },
        "token": "2|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "token_type": "Bearer",
        "remember_me": false
    }
}
```

#### Forgot Password
```http
POST /api/forgot-password
Content-Type: application/json

{
    "email": "john@example.com"
}
```

**Response (200 OK):**
```json
{
    "message": "Password reset link has been sent to your email."
}
```

#### Reset Password
```http
POST /api/reset-password
Content-Type: application/json

{
    "email": "john@example.com",
    "token": "reset-token-from-email",
    "password": "NewSecurePass123!",
    "password_confirmation": "NewSecurePass123!"
}
```

**Response (200 OK):**
```json
{
    "message": "Password reset successful. Please log in with your new password."
}
```

#### Verify Email
```http
POST /api/email/verify/{id}/{hash}
```

**Response (200 OK):**
```json
{
    "message": "Email verified successfully!",
    "data": {
        "user_id": 1,
        "verified": true,
        "email": "john@example.com"
    }
}
```

### Protected Endpoints (Requires Authentication)

#### Get Current User
```http
GET /api/me
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "message": "User profile retrieved successfully.",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "date_of_birth": "1990-01-15",
            "email_verified_at": "2024-11-15T11:00:00Z",
            "created_at": "2024-11-15T10:30:00Z"
        }
    }
}
```

#### Logout (Current Device)
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "message": "Logged out successfully."
}
```

#### Logout All Devices
```http
POST /api/logout-all
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "message": "Logged out from all devices successfully."
}
```

#### Resend Verification Email
```http
POST /api/email/resend-verification
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "message": "Verification email sent successfully."
}
```

## Form Request Classes

### RegisterRequest
**File:** `app/Http/Requests/RegisterRequest.php`

Validates:
- `first_name` - Required, string, max 255, letters/spaces/apostrophes/hyphens only
- `last_name` - Required, string, max 255, letters/spaces/apostrophes/hyphens only
- `email` - Required, valid email, unique in users table
- `password` - Required, min 8 chars, mixed case, numbers, symbols, not compromised
- `password_confirmation` - Required, must match password
- `phone` - Optional, valid phone number format
- `date_of_birth` - Optional, valid date (YYYY-MM-DD), must be in the past

### LoginRequest
**File:** `app/Http/Requests/LoginRequest.php`

Validates:
- `email` - Required, valid email format
- `password` - Required, min 6 chars
- `remember_me` - Optional, boolean

### ForgotPasswordRequest
**File:** `app/Http/Requests/ForgotPasswordRequest.php`

Validates:
- `email` - Required, valid email, must exist in users table

### ResetPasswordRequest
**File:** `app/Http/Requests/ResetPasswordRequest.php`

Validates:
- `email` - Required, valid email, must exist in users table
- `token` - Required, string
- `password` - Required, min 8 chars, mixed case, numbers, symbols, not compromised
- `password_confirmation` - Required, must match password

## Models & Services

### User Model
**File:** `app/Models/User.php`

Features:
- Uses Laravel Sanctum for API token authentication
- Email verification methods
- Notifiable for email notifications
- Timestamps and proper attribute casting
- Full name accessor

### AuthenticationService
**File:** `app/Services/AuthenticationService.php`

Methods:
- `register(array $data)` - Create new user
- `login(User $user, string $deviceName)` - Issue API token
- `logout(User $user)` - Revoke all user tokens
- `logoutToken(string $tokenId)` - Revoke single token
- `generatePasswordResetToken(User $user)` - Generate reset token
- `verifyEmailToken(User $user, string $hash)` - Verify email hash

### Email Notifications

#### VerifyEmailNotification
**File:** `app/Notifications/VerifyEmailNotification.php`

Sends verification email with:
- Personalized greeting
- Verification button link
- 24-hour expiration notice

#### ResetPasswordNotification
**File:** `app/Notifications/ResetPasswordNotification.php`

Sends password reset email with:
- Reset button link
- 1-hour expiration notice

## Authentication Controller

**File:** `app/Http/Controllers/Api/AuthController.php`

Implements all authentication endpoints with:
- Comprehensive error handling
- Validation feedback
- Security best practices
- Proper HTTP status codes

Methods:
- `register(RegisterRequest $request)` - POST /api/register
- `login(LoginRequest $request)` - POST /api/login
- `logout(Request $request)` - POST /api/logout
- `logoutAll(Request $request)` - POST /api/logout-all
- `forgotPassword(ForgotPasswordRequest $request)` - POST /api/forgot-password
- `resetPassword(ResetPasswordRequest $request)` - POST /api/reset-password
- `verifyEmail(int $id, string $hash)` - POST /api/email/verify/{id}/{hash}
- `resendVerificationEmail(Request $request)` - POST /api/email/resend-verification
- `me(Request $request)` - GET /api/me

## Routes Configuration

**File:** `routes/api.php`

Features:
- Rate limiting: `throttle:60,1` (60 requests per minute)
- Protected routes middleware: `auth:sanctum`
- All authentication endpoints grouped with rate limiting
- Proper route naming for artisan route:list command

## Database Migrations

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX email_idx (email),
    INDEX created_at_idx (created_at)
);
```

### Personal Access Tokens Table
```sql
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(80) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX tokenable_type_tokenable_id_idx (tokenable_type, tokenable_id)
);
```

### Password Reset Tokens Table
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    INDEX created_at_idx (created_at)
);
```

## Password Policy

All passwords must meet these requirements:
- **Minimum 8 characters**
- **Mixed case** (uppercase and lowercase letters)
- **At least one number** (0-9)
- **At least one symbol** (!@#$%^&*)
- **Not compromised** (checked against HaveIBeenPwned database)

## Rate Limiting

All authentication endpoints are rate limited to:
- **60 requests per minute** per IP address

This prevents brute force attacks on login and registration endpoints.

## Setup Instructions

### 1. Install Dependencies
```bash
cd /workspaces/Molunzaka-tv2
composer install
```

### 2. Generate Application Key
```bash
php artisan key:generate
```

### 3. Configure Environment
```bash
cp .env.example .env
# Edit .env with your database and mail configuration
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Install Sanctum (if not already installed)
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6. Start the Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Testing the API

### Using cURL

**Register:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'
```

**Get User Profile:**
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer {token}"
```

**Logout:**
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {token}"
```

### Using Postman

1. Import the API endpoints into Postman
2. Set up a variable for `{{token}}` from login response
3. Use Bearer authentication with `{{token}}`
4. Test all endpoints

## Security Considerations

✅ **Password Hashing** - Uses Laravel's bcrypt hashing
✅ **Token Security** - Sanctum tokens are cryptographically secure
✅ **CORS Ready** - Can be configured for frontend integration
✅ **Rate Limiting** - Prevents brute force attacks
✅ **Email Verification** - Prevents fake email registrations
✅ **Password Policies** - Enforces strong passwords
✅ **Validation** - Comprehensive input validation
✅ **Error Handling** - Secure error messages

## Environment Variables

Key configuration variables in `.env`:

```env
APP_NAME=MOLUNZAKA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
API_URL=http://localhost/api
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@molunzaka.local

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
```

## Error Handling

### Validation Errors (422)
```json
{
    "message": "Validation failed.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must contain at least one symbol."]
    }
}
```

### Authentication Errors (401)
```json
{
    "message": "Invalid credentials."
}
```

### Not Found (404)
```json
{
    "message": "User not found."
}
```

### Server Errors (500)
```json
{
    "message": "Failed to register user.",
    "error": "Error details (in debug mode only)"
}
```

## License

This authentication module is part of the MOLUNZAKA project.

## Support

For issues, feature requests, or improvements, please refer to the main MOLUNZAKA project repository.

---

**Created:** November 15, 2024
**Framework:** Laravel 10
**Authentication:** Laravel Sanctum
**Database:** SQLite (configurable)
