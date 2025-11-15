# MOLUNZAKA - Video Streaming Platform

A production-ready video-streaming platform (similar to Showmax/Netflix) built with **PHP + Laravel 10**.

## Overview

This is a complete Laravel backend implementation with a comprehensive authentication module. It provides secure user management, email verification, password reset functionality, and API token-based authentication using Laravel Sanctum.

## Features

### Authentication Module ✅

- **User Registration** - Form validation with secure password policies
- **Email Verification** - Notification-based email verification system
- **Login/Logout** - Sanctum token-based API authentication
- **Password Reset** - Secure forgot password and reset flows
- **Multi-Device Support** - Logout from single device or all devices
- **Rate Limiting** - 60 requests/minute on all auth endpoints
- **Password Policy** - 8+ chars, mixed case, numbers, symbols, breach checking

### API Endpoints

```
POST   /api/register                  - Register new user
POST   /api/login                     - Login and get token
POST   /api/logout                    - Logout current device
POST   /api/logout-all                - Logout all devices
GET    /api/me                        - Get user profile
POST   /api/forgot-password           - Request password reset
POST   /api/reset-password            - Reset password with token
POST   /api/email/verify/{id}/{hash}  - Verify email address
POST   /api/email/resend-verification - Resend verification email
```

## Project Structure

```
Molunzaka-tv2/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── AuthController.php              (All auth endpoints)
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── ForgotPasswordRequest.php
│   │   │   └── ResetPasswordRequest.php
│   │   └── Middleware/
│   ├── Models/
│   │   └── User.php                            (User model with Sanctum)
│   ├── Services/
│   │   └── AuthenticationService.php           (Core auth logic)
│   └── Notifications/
│       ├── VerifyEmailNotification.php
│       └── ResetPasswordNotification.php
├── routes/
│   └── api.php                                 (API routes with rate limiting)
├── database/
│   └── migrations/
│       ├── 2024_11_15_000001_create_users_table.php
│       ├── 2024_11_15_000002_create_personal_access_tokens_table.php
│       └── 2024_11_15_000003_create_password_reset_tokens_table.php
├── config/
│   ├── app.php
│   └── database.php
├── composer.json                               (Dependencies)
├── .env.example                                (Environment template)
├── AUTHENTICATION_MODULE.md                    (Detailed auth documentation)
└── AUTH_CONTROLLER_REFERENCE.md               (Code reference)
```

## Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- SQLite/MySQL/PostgreSQL
- Git

### Installation

1. **Clone/Setup Repository**
   ```bash
   cd /workspaces/Molunzaka-tv2
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

4. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

5. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

6. **Publish Sanctum Configuration** (if needed)
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

   The API will be available at: `http://localhost:8000/api`

## API Usage Examples

### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "phone": "+1234567890",
    "date_of_birth": "1990-01-15"
  }'
```

**Response:**
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

### Login User

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'
```

### Get User Profile

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Logout

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Forgot Password

```bash
curl -X POST http://localhost:8000/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

### Reset Password

```bash
curl -X POST http://localhost:8000/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "token": "reset-token-from-email",
    "password": "NewSecurePass123!",
    "password_confirmation": "NewSecurePass123!"
  }'
```

## Database Schema

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
    updated_at TIMESTAMP
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
    updated_at TIMESTAMP
);
```

### Password Reset Tokens Table
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

## Environment Configuration

Key variables in `.env`:

```env
# Application
APP_NAME=MOLUNZAKA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
API_URL=http://localhost/api
FRONTEND_URL=http://localhost:3000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@molunzaka.local

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
```

## Password Requirements

All passwords must meet these criteria:
- ✅ Minimum 8 characters
- ✅ Mixed case (uppercase + lowercase)
- ✅ At least one number
- ✅ At least one special symbol
- ✅ Not previously compromised (checked against HaveIBeenPwned)

## Rate Limiting

All authentication endpoints are rate-limited to:
- **60 requests per minute** per IP address

This protects against brute force attacks.

## Security Features

✅ **bcrypt password hashing** - Industry standard password hashing
✅ **Sanctum tokens** - Cryptographically secure API tokens
✅ **Email verification** - Prevents fake email registrations
✅ **CORS ready** - Can be configured for any frontend domain
✅ **Rate limiting** - Prevents brute force attacks
✅ **Comprehensive validation** - Input validation on all endpoints
✅ **Secure error handling** - No sensitive info in error messages
✅ **Logout all devices** - User can invalidate all tokens at once

## Form Validation

### RegisterRequest
- First Name: Required, letters/spaces/apostrophes/hyphens only
- Last Name: Required, letters/spaces/apostrophes/hyphens only
- Email: Required, unique, valid email format (RFC + DNS check)
- Password: Required, 8+ chars, mixed case, numbers, symbols, not compromised
- Phone: Optional, valid phone number format
- Date of Birth: Optional, valid date (YYYY-MM-DD), in past

### LoginRequest
- Email: Required, valid email format
- Password: Required, minimum 6 characters
- Remember Me: Optional, boolean

### ForgotPasswordRequest
- Email: Required, must exist in database, valid email format

### ResetPasswordRequest
- Email: Required, must exist in database
- Token: Required, string
- Password: Required, 8+ chars, mixed case, numbers, symbols, not compromised

## File Locations

| Component | File |
|-----------|------|
| Auth Controller | `app/Http/Controllers/Api/AuthController.php` |
| User Model | `app/Models/User.php` |
| Authentication Service | `app/Services/AuthenticationService.php` |
| Register Request | `app/Http/Requests/RegisterRequest.php` |
| Login Request | `app/Http/Requests/LoginRequest.php` |
| Forgot Password Request | `app/Http/Requests/ForgotPasswordRequest.php` |
| Reset Password Request | `app/Http/Requests/ResetPasswordRequest.php` |
| Email Verification Notification | `app/Notifications/VerifyEmailNotification.php` |
| Password Reset Notification | `app/Notifications/ResetPasswordNotification.php` |
| API Routes | `routes/api.php` |
| Users Migration | `database/migrations/2024_11_15_000001_create_users_table.php` |
| Tokens Migration | `database/migrations/2024_11_15_000002_create_personal_access_tokens_table.php` |
| Password Reset Migration | `database/migrations/2024_11_15_000003_create_password_reset_tokens_table.php` |

## Documentation Files

- **AUTHENTICATION_MODULE.md** - Comprehensive authentication module documentation with full API reference
- **AUTH_CONTROLLER_REFERENCE.md** - Quick reference with complete code snippets for all classes

## Testing

Use Postman, cURL, or any HTTP client to test the API:

1. **Register** a new account
2. **Check email** for verification link (in mail log)
3. **Verify email** by clicking the link or calling the endpoint
4. **Login** with credentials to get API token
5. **Use token** to access protected endpoints
6. **Test logout** to invalidate tokens

## Available Commands

```bash
# Show all routes
php artisan route:list

# Show auth routes only
php artisan route:list --name=auth

# Run migrations
php artisan migrate

# Reset database
php artisan migrate:reset

# Fresh migration (reset + migrate)
php artisan migrate:fresh

# Create new model with migration
php artisan make:model ModelName -m

# Create new controller
php artisan make:controller ControllerName

# Create new request
php artisan make:request RequestName

# Create new notification
php artisan make:notification NotificationName

# Start development server
php artisan serve

# Tinker shell (interactive)
php artisan tinker
```

## Next Steps

1. **Frontend Integration** - Connect with React/Vue frontend
2. **Video Upload** - Implement video storage and streaming
3. **User Profiles** - Add user profile management
4. **Subscriptions** - Add payment and subscription handling
5. **Content Management** - Build content upload and management
6. **Search & Filtering** - Add full-text search for videos
7. **Recommendations** - Implement recommendation engine
8. **Admin Panel** - Create admin dashboard

## Technologies Used

- **Framework**: Laravel 10
- **Authentication**: Laravel Sanctum
- **Database**: SQLite/MySQL/PostgreSQL
- **PHP**: 8.1+
- **Composer**: Dependency management
- **Email**: Laravel Mail + Queue support

## License

This project is part of the MOLUNZAKA platform.

## Support & Contribution

For issues, feature requests, or contributions, please refer to the main project repository.

---

**Last Updated:** November 15, 2024
**Version:** 1.0.0
**Status:** Production Ready ✅
