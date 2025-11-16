# Molunzaka Web Frontend - Project Structure

## Complete Directory Layout

```
molunzaka-web/
├── public/                      # Static assets
│   └── vite.svg
├── src/
│   ├── components/              # Reusable React components
│   │   ├── Layout.jsx          # Main layout wrapper with Outlet
│   │   ├── Navbar.jsx          # Navigation bar with auth state
│   │   └── ProtectedRoute.jsx  # Route protection wrapper
│   │
│   ├── contexts/                # React Context stores
│   │   └── AuthContext.jsx      # Authentication state (user, login, logout, token)
│   │
│   ├── hooks/                   # Custom React hooks
│   │   ├── useAuth.js           # Hook to access AuthContext
│   │   └── useAsync.js          # Hook for async operations (loading, error handling)
│   │
│   ├── pages/                   # Page components (one per page)
│   │   ├── HomePage.jsx         # Landing page with features
│   │   ├── LoginPage.jsx        # User login form
│   │   ├── RegisterPage.jsx     # User registration form
│   │   ├── DashboardPage.jsx    # Protected user dashboard
│   │   ├── UnauthorizedPage.jsx # 403 error page
│   │   └── NotFoundPage.jsx     # 404 error page
│   │
│   ├── services/                # API service layer
│   │   ├── axios.js             # Axios instance with interceptors
│   │   ├── authService.js       # Authentication API calls
│   │   ├── videoService.js      # Video API calls
│   │   └── profileService.js    # Profile API calls
│   │
│   ├── styles/                  # Stylesheet folder
│   │
│   ├── utils/                   # Utility functions
│   │   └── helpers.js           # Helper functions (format, validate, etc.)
│   │
│   ├── App.jsx                  # Main app component with routes
│   ├── main.jsx                 # React DOM entry point
│   ├── index.css                # Global Tailwind styles
│   └── App.css                  # App component styles
│
├── .env.example                 # Environment variables template
├── index.html                   # HTML entry point
├── package.json                 # Project dependencies
├── postcss.config.js            # PostCSS configuration (Tailwind)
├── tailwind.config.js           # Tailwind CSS configuration
├── vite.config.js               # Vite configuration
├── README.md                    # Project readme with setup instructions
├── DEVELOPER_GUIDE.md           # Developer guide and best practices
└── PROJECT_STRUCTURE.md         # This file
```

## File Descriptions

### Components

| File | Purpose |
|------|---------|
| `Layout.jsx` | Main layout wrapper that includes Navbar and renders nested routes via Outlet |
| `Navbar.jsx` | Navigation bar showing user info and logout button |
| `ProtectedRoute.jsx` | Wrapper component for protected routes with role-based access control |

### Contexts

| File | Purpose |
|------|---------|
| `AuthContext.jsx` | React Context for global authentication state |

### Hooks

| File | Purpose |
|------|---------|
| `useAuth.js` | Custom hook to access AuthContext from any component |
| `useAsync.js` | Custom hook for managing async operations (pending, success, error states) |

### Pages

| File | Purpose | Auth Required |
|------|---------|---------------|
| `HomePage.jsx` | Landing page with features overview | No |
| `LoginPage.jsx` | User login form with email/password | No |
| `RegisterPage.jsx` | User registration form | No |
| `DashboardPage.jsx` | Protected user dashboard | Yes |
| `UnauthorizedPage.jsx` | 403 error for insufficient permissions | No |
| `NotFoundPage.jsx` | 404 error for missing routes | No |

### Services

| File | Purpose |
|------|---------|
| `axios.js` | Axios instance with request/response interceptors and token management |
| `authService.js` | Authentication API endpoints (login, register, logout, etc.) |
| `videoService.js` | Video management API endpoints |
| `profileService.js` | Profile management API endpoints |

### Utils

| File | Purpose |
|------|---------|
| `helpers.js` | Utility functions (formatDate, validateEmail, debounce, etc.) |

## Dependencies

### Production Dependencies

```json
{
  "react": "18+",
  "react-dom": "18+",
  "react-router-dom": "6+",
  "axios": "1+",
  "sweetalert2": "11+",
  "video.js": "8+"
}
```

### Development Dependencies

```json
{
  "vite": "5+",
  "@vitejs/plugin-react": "4+",
  "tailwindcss": "3+",
  "postcss": "8+",
  "autoprefixer": "10+"
}
```

## Routing Structure

```
/                          → HomePage (public)
/login                     → LoginPage (public)
/register                  → RegisterPage (public)
/unauthorized              → UnauthorizedPage (public)
/dashboard                 → DashboardPage (protected, authenticated)
/[other routes]            → NotFoundPage (404)
```

## Authentication Flow

```
1. User visits app → AuthContext checks localStorage for token
2. User logs in → credentials sent to /auth/login endpoint
3. Backend returns token & user data → stored in localStorage
4. Token automatically added to subsequent API requests via axios interceptor
5. If request gets 401 → token cleared, user redirected to /login
6. Protected routes check isAuthenticated before rendering
```

## State Management

### Global State (AuthContext)

```javascript
{
  user: {
    id: 1,
    email: 'user@example.com',
    first_name: 'John',
    last_name: 'Doe',
    roles: ['Subscriber']
  },
  isAuthenticated: true,
  loading: false,
  error: null,
  token: 'jwt_token_string'
}
```

### Component Local State

Each component manages its own local state for:
- Form inputs
- UI toggles
- Component-specific data

## Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `VITE_API_URL` | Backend API base URL | `http://localhost:8000/api` |

## Build Output

When running `npm run build`, the following structure is created:

```
dist/
├── index.html
├── assets/
│   ├── [file-hash].js     # JavaScript bundles
│   └── [file-hash].css    # CSS bundles
└── vite.svg
```

This can be deployed to any static hosting service.

## Key Features

### ✅ Implemented

- React Router v6 with nested routes
- Protected routes with authentication
- Context API for state management
- Axios with automatic token injection
- Tailwind CSS with custom color palette
- Global error handling with SweetAlert2
- Responsive design
- Custom hooks (useAuth, useAsync)
- Form validation and submission
- Error pages (404, 403)
- API service layer architecture

### 🎬 Ready to Add

- Video player integration (Video.js)
- Video upload functionality
- Video search and filtering
- User profiles
- Comments and ratings
- Watchlist/Favorites
- User settings
- Admin dashboard
- Analytics

## Quick Commands

```bash
# Development
npm run dev              # Start dev server on http://localhost:5173

# Production
npm run build            # Build for production
npm run preview          # Preview production build locally

# Linting (if configured)
npm run lint            # Check code quality
npm run lint:fix        # Fix linting issues
```

## Performance Optimizations

- Code splitting with React Router lazy loading
- Image lazy loading
- Memoization of expensive computations
- Debouncing and throttling of events
- Production build optimization with Vite

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Notes

- Token is stored in localStorage for persistent sessions
- CORS must be configured on backend
- API URL can be changed via `.env` file
- Tailwind CSS is fully configured with custom colors
- All API calls should be made through the service layer
- Custom hooks should be used for shared logic

---

**Last Updated:** November 16, 2025
