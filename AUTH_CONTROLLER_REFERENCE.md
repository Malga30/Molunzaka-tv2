# MOLUNZAKA Authentication Module - Quick Reference

## Complete AuthController Code

**Location:** `app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct(private AuthenticationService $authService)
    {
    }

    /**
     * Register a new user.
     * POST /api/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $user->notify(new VerifyEmailNotification($user));
            $tokenResponse = $this->authService->login($user, $request->header('User-Agent') ?? 'web');

            return response()->json([
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $tokenResponse['token'],
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register user.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Login user and issue token.
     * POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Please verify your email address before logging in.',
                    'data' => ['requires_verification' => true, 'user_id' => $user->id],
                ], 403);
            }

            $tokenResponse = $this->authService->login($user, $request->header('User-Agent') ?? 'web');

            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'token' => $tokenResponse['token'],
                    'token_type' => 'Bearer',
                    'remember_me' => $request->boolean('remember_me', false),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Logout user and revoke token.
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $this->authService->logoutToken($request->user()->currentAccessToken()->id);

            return response()->json(['message' => 'Logged out successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Logout user from all devices.
     * POST /api/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $this->authService->logout($user);

            return response()->json(['message' => 'Logged out from all devices successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Request password reset.
     * POST /api/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'If an account exists with this email, you will receive a password reset link.',
                ], 200);
            }

            $token = Password::createToken($user);
            $user->notify(new ResetPasswordNotification($token));

            return response()->json([
                'message' => 'Password reset link has been sent to your email.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process password reset request.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Reset password with token.
     * POST /api/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $status = Password::verify($user, $request->token, $request->password);

            if ($status !== Password::PASSWORD_RESET) {
                return response()->json(['message' => 'Invalid or expired reset token.'], 422);
            }

            $user->update(['password' => Hash::make($request->password)]);
            $this->authService->logout($user);

            return response()->json([
                'message' => 'Password reset successful. Please log in with your new password.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Verify user email.
     * POST /api/email/verify/{id}/{hash}
     */
    public function verifyEmail(int $id, string $hash): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified.',
                    'data' => ['user_id' => $user->id, 'verified' => true],
                ], 200);
            }

            if (!$this->authService->verifyEmailToken($user, $hash)) {
                return response()->json(['message' => 'Invalid verification link.'], 422);
            }

            $user->markEmailAsVerified();

            return response()->json([
                'message' => 'Email verified successfully!',
                'data' => ['user_id' => $user->id, 'verified' => true, 'email' => $user->email],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Resend verification email.
     * POST /api/email/resend-verification
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.'], 200);
            }

            $user->notify(new VerifyEmailNotification($user));

            return response()->json(['message' => 'Verification email sent successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resend verification email.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get current user profile.
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 'User profile retrieved successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user profile.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
```

## Complete Routes Configuration

**Location:** `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public authentication routes with rate limiting (60 requests per minute)
Route::middleware('throttle:60,1')->group(function () {
    // Registration
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    // Login
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Forgot password
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');

    // Reset password
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

    // Email verification
    Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');

    // Resend verification email (for unauthenticated users)
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('auth:sanctum')
        ->name('auth.resend-verification');
});

// Protected routes (authenticated users only)
Route::middleware('auth:sanctum')->group(function () {
    // Get current user
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');

    // Logout (current device)
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Logout from all devices
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');

    // Resend verification email
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail'])
        ->name('auth.resend-verification-authenticated');
});
```

## All Form Request Classes

### RegisterRequest
**Location:** `app/Http/Requests/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\'-]*$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\'-]*$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required'],
            'phone' => ['nullable', 'phone:AUTO', 'max:20'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today', 'after:1900-01-01'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name may only contain letters, spaces, apostrophes, and hyphens.',
            'last_name.required' => 'Last name is required.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.mixed_case' => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character.',
            'password.uncompromised' => 'This password has been compromised. Please choose a different password.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422)
        );
    }
}
```

### LoginRequest
**Location:** `app/Http/Requests/LoginRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'remember_me' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422)
        );
    }
}
```

### ForgotPasswordRequest
**Location:** `app/Http/Requests/ForgotPasswordRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc,dns', 'max:255', 'exists:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.exists' => 'We could not find a user with this email address.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422)
        );
    }
}
```

### ResetPasswordRequest
**Location:** `app/Http/Requests/ResetPasswordRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc,dns', 'max:255', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.exists' => 'We could not find a user with this email address.',
            'token.required' => 'Reset token is required.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422)
        );
    }
}
```

## Installation Summary

```bash
# 1. Install composer dependencies
composer install

# 2. Generate app key
php artisan key:generate

# 3. Configure environment
cp .env.example .env
# Edit .env with database configuration

# 4. Run migrations
php artisan migrate

# 5. Publish Sanctum configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 6. Start development server
php artisan serve

# API available at: http://localhost:8000/api
```

## Testing Endpoints with cURL

```bash
# Register user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'

# Get profile (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer TOKEN"

# Logout
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer TOKEN"
```

---

**Framework:** Laravel 10
**Authentication:** Laravel Sanctum
**Rate Limiting:** 60 requests/minute
**Password Policy:** 8+ chars, mixed case, numbers, symbols
