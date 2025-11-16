# 🏗️ Molunzaka Web - Architecture Overview

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      MOLUNZAKA WEB FRONTEND                      │
│                      (React + Vite + Tailwind)                   │
└─────────────────────────────────────────────────────────────────┘
                                 │
                ┌────────────────┼────────────────┐
                │                │                │
        ┌───────▼──────┐  ┌──────▼──────┐  ┌────▼─────────┐
        │    PAGES     │  │ COMPONENTS  │  │   CONTEXTS   │
        ├──────────────┤  ├─────────────┤  ├──────────────┤
        │ HomePage     │  │ Layout      │  │ AuthContext  │
        │ LoginPage    │  │ Navbar      │  │              │
        │ RegisterPage │  │ Protected   │  │ Global State:│
        │ Dashboard    │  │   Route     │  │ • User       │
        │ Error Pages  │  │             │  │ • Token      │
        └──────────────┘  └─────────────┘  └──────────────┘
                                 │
                ┌────────────────┼────────────────┐
                │                │                │
        ┌───────▼──────┐  ┌──────▼──────┐  ┌────▼─────────┐
        │    HOOKS     │  │   SERVICES  │  │    UTILS     │
        ├──────────────┤  ├─────────────┤  ├──────────────┤
        │ useAuth()    │  │ axios.js    │  │ formatDate() │
        │ useAsync()   │  │ authServ.   │  │ validateEmail│
        │              │  │ videoServ.  │  │ formatBytes()│
        │              │  │ profileServ.│  │ debounce()   │
        └──────────────┘  └─────────────┘  └──────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   AXIOS INTERCEPTORS    │
                    ├─────────────────────────┤
                    │ Request: Add token      │
                    │ Response: Handle 401    │
                    └────────────▼────────────┘
                                 │
        ┌────────────────────────▼─────────────────────────┐
        │         BACKEND API (Laravel)                     │
        ├───────────────────────────────────────────────────┤
        │ /api/auth/login       /api/videos                │
        │ /api/auth/register    /api/profiles              │
        │ /api/auth/me          /api/comments              │
        │ /api/auth/logout      /api/ratings               │
        └───────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
USER ACTION
    │
    ▼
REACT COMPONENT
    │
    ├─► Render UI (Tailwind CSS)
    │
    ├─► Handle Event (onClick, onChange, onSubmit)
    │
    ▼
CUSTOM HOOK (useAuth, useAsync)
    │
    ├─► Access Context/State
    │
    ├─► Call Service Function
    │
    ▼
SERVICE LAYER
    │
    ├─► Build Request
    │
    ├─► Call Axios
    │
    ▼
AXIOS INTERCEPTOR
    │
    ├─► REQUEST: Add Authorization Header
    │
    ├─► RESPONSE: Handle Errors
    │
    ▼
BACKEND API
    │
    ├─► Process Request
    │
    ├─► Return Response
    │
    ▼
AXIOS RESPONSE
    │
    ├─► Parse JSON
    │
    ├─► Handle Errors
    │
    ▼
SERVICE RETURNS DATA
    │
    ▼
COMPONENT STATE UPDATES
    │
    ▼
COMPONENT RE-RENDERS
    │
    ▼
USER SEES RESULT
```

## Component Tree

```
App (with Router)
├── AuthProvider
│   ├── Routes
│   │   ├── /login → LoginPage (public)
│   │   ├── /register → RegisterPage (public)
│   │   ├── /unauthorized → UnauthorizedPage (public)
│   │   │
│   │   └── ProtectedRoute (requires auth)
│   │       └── Layout
│   │           ├── Navbar
│   │           │   └── useAuth()
│   │           │
│   │           └── Outlet
│   │               ├── / → HomePage
│   │               └── /dashboard → DashboardPage
│   │
│   └── [404 fallback]
```

## Authentication State Machine

```
┌────────────────┐
│  App Mounts    │
└────────┬───────┘
         │
         ▼
    Check localStorage
         │
    ┌────┴────┐
    │          │
    ▼          ▼
Token  No Token
Found  Found
    │          │
    │    ┌─────▼──────────┐
    │    │ UNAUTHENTICATED│
    │    ├─────────────────┤
    │    │ • User: null    │
    │    │ • isAuth: false │
    │    │ • Redirect:/login
    │    └─────────────────┘
    │
    ▼
┌─────────────────────────────────┐
│    AUTHENTICATED                │
├─────────────────────────────────┤
│ • User: {id, email, name...}    │
│ • Token: stored in localStorage │
│ • isAuth: true                  │
│ • Access: All protected routes  │
└──────────┬───────────────────────┘
           │
    ┌──────┴────────┐
    │               │
    ▼               ▼
User Logout   Token Expires (401)
    │               │
    └───────┬───────┘
            │
            ▼
    ┌──────────────────┐
    │ Clear localStorage│
    │ Redirect to login│
    │ Reset state      │
    └──────────────────┘
```

## API Integration Flow

```
FRONTEND              AXIOS              BACKEND
   │                   │                   │
   │ POST /login       │                   │
   ├──────────────────►│ Add token         │
   │                   │ header            │
   │                   │                   │
   │                   ├──────────────────►│
   │                   │ POST /api/auth    │
   │                   │                   │
   │                   │                   │ Validate
   │                   │                   │ Generate JWT
   │                   │                   │
   │                   │◄──────────────────┤
   │                   │ 200 OK            │
   │                   │ {token, user}     │
   │                   │                   │
   │◄──────────────────┤                   │
   │ {token, user}     │                   │
   │                   │                   │
   ▼                   ▼                   ▼
Store in          Ready to
localStorage      add to requests


AUTHENTICATED REQUEST
   │
   │ GET /videos
   │ + Header: Authorization: Bearer {token}
   │
   ├──────────────────►│
   │                   ├──────────────────►│
   │                   │ GET /api/videos   │
   │                   │                   │
   │                   │                   │ Verify token
   │                   │                   │ Process request
   │                   │                   │
   │                   │◄──────────────────┤
   │                   │ 200 OK            │
   │                   │ [video array]     │
   │                   │                   │
   │◄──────────────────┤                   │
   │ [video array]     │                   │
   │                   │                   │
   ▼                   ▼                   ▼
Update              Ready for
component state    next request


ERROR RESPONSE (401)
   │
   │ GET /dashboard
   │ + Header: Authorization: Bearer {invalid_token}
   │
   ├──────────────────►│
   │                   ├──────────────────►│
   │                   │                   │ Invalid token
   │                   │◄──────────────────┤
   │                   │ 401 Unauthorized  │
   │                   │                   │
   │◄──────────────────┤                   │
   │ 401 Error         │                   │
   │                   │                   │
   ▼                   ▼                   ▼
Clear localStorage
Redirect to /login
Reset auth state
```

## Project Layers

```
┌─────────────────────────────────────────┐
│        PRESENTATION LAYER               │
│        (React Components)               │
├─────────────────────────────────────────┤
│ • HomePage, LoginPage, Dashboard, etc   │
│ • Tailwind CSS Styling                  │
│ • User Interactions (onClick, onChange) │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│        LOGIC LAYER                      │
│        (Hooks & Context)                │
├─────────────────────────────────────────┤
│ • useAuth() - Access auth state         │
│ • useAsync() - Async operations         │
│ • AuthContext - Global state            │
│ • Component Local State                 │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│        SERVICE LAYER                    │
│        (API Integration)                │
├─────────────────────────────────────────┤
│ • authService - Auth endpoints          │
│ • videoService - Video endpoints        │
│ • profileService - Profile endpoints    │
│ • axios - HTTP client                   │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│        UTILITY LAYER                    │
│        (Helper Functions)               │
├─────────────────────────────────────────┤
│ • formatDate, formatBytes               │
│ • validateEmail, checkPassword          │
│ • debounce, throttle                    │
│ • deepClone, isEmpty                    │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│        BACKEND API (Laravel)            │
│        (Express/Node alternative)       │
├─────────────────────────────────────────┤
│ • Database Layer                        │
│ • Business Logic                        │
│ • Authentication                        │
└─────────────────────────────────────────┘
```

## Routing Architecture

```
App (Router)
│
├─ /                          [PUBLIC]
│  └─ HomePage
│     ├─ Landing Content
│     ├─ Features Section
│     └─ CTA Buttons
│
├─ /login                     [PUBLIC]
│  └─ LoginPage
│     ├─ Email Input
│     ├─ Password Input
│     └─ Submit Button
│
├─ /register                  [PUBLIC]
│  └─ RegisterPage
│     ├─ Name Inputs
│     ├─ Email Input
│     ├─ Password Inputs
│     └─ Submit Button
│
├─ /                          [PROTECTED]
│  └─ Layout
│     ├─ Navbar (with user info)
│     │
│     ├─ /dashboard          [PROTECTED]
│     │  └─ DashboardPage
│     │     ├─ Profile Card
│     │     ├─ Videos Section
│     │     ├─ Stats Section
│     │     └─ Settings Button
│     │
│     └─ [Add more routes here]
│
├─ /unauthorized             [PUBLIC]
│  └─ UnauthorizedPage (403)
│
└─ *                          [PUBLIC]
   └─ NotFoundPage (404)
```

## State Management Flow

```
GLOBAL STATE (AuthContext)
├─ user
│  ├─ id
│  ├─ email
│  ├─ first_name
│  ├─ last_name
│  └─ roles: [Subscriber, Admin, etc]
│
├─ isAuthenticated: boolean
├─ loading: boolean
├─ error: string | null
│
└─ Functions
   ├─ login(userData, token)
   ├─ logout()
   ├─ updateUser(userData)
   └─ getToken()


LOCAL STATE (Component)
├─ Form Data
│  ├─ email: string
│  ├─ password: string
│  └─ first_name: string
│
├─ UI State
│  ├─ loading: boolean
│  ├─ error: string
│  └─ isOpen: boolean
│
└─ Data State
   ├─ videos: array
   ├─ profiles: array
   └─ comments: array
```

## Error Handling Flow

```
API Request
    │
    ├─ SUCCESS (200, 201, etc)
    │  └─ Return Data
    │     └─ Update Component State
    │
├─ CLIENT ERROR (4xx)
│  │
│  ├─ 401 Unauthorized
│  │  └─ Clear Auth State
│  │     └─ Redirect to /login
│  │
│  ├─ 403 Forbidden
│  │  └─ Show "Access Denied"
│  │     └─ Redirect to /unauthorized
│  │
│  ├─ 404 Not Found
│  │  └─ Show "Not Found"
│  │
│  └─ 422 Validation Error
│     └─ Show Form Errors
│        └─ Display with SweetAlert2
│
└─ SERVER ERROR (5xx)
   └─ Show Generic Error
      └─ Log to Console
         └─ Retry Option
```

## Performance Optimizations

```
┌─────────────────────────────────────────┐
│      OPTIMIZATION STRATEGIES            │
├─────────────────────────────────────────┤
│                                         │
│ 1. CODE SPLITTING                       │
│    └─ Lazy load pages with React Router │
│                                         │
│ 2. BUNDLE OPTIMIZATION                  │
│    ├─ Vite's tree-shaking               │
│    ├─ CSS minification                  │
│    └─ JS minification                   │
│                                         │
│ 3. LAZY LOADING                         │
│    ├─ Images: loading="lazy"            │
│    ├─ Routes: React.lazy()              │
│    └─ Components: Suspense              │
│                                         │
│ 4. MEMOIZATION                          │
│    ├─ useMemo() for expensive calcs     │
│    ├─ useCallback() for functions       │
│    └─ React.memo() for components       │
│                                         │
│ 5. DEBOUNCING/THROTTLING                │
│    ├─ Search input debounce             │
│    ├─ Scroll events throttle            │
│    └─ API calls optimization            │
│                                         │
│ 6. STATE OPTIMIZATION                   │
│    ├─ Proper Context usage              │
│    ├─ Local state when possible         │
│    └─ Avoid unnecessary re-renders      │
│                                         │
└─────────────────────────────────────────┘
```

---

**This architecture provides a solid foundation for a scalable, maintainable React application with proper separation of concerns, authentication, and API integration.**
