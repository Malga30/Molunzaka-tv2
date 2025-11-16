# Molunzaka Web Frontend

A modern React + Vite web application for the Molunzaka TV streaming platform.

## 🚀 Tech Stack

- **React 18** - UI library
- **Vite** - Build tool and dev server
- **React Router** - Client-side routing
- **Tailwind CSS** - Utility-first CSS framework
- **Axios** - HTTP client
- **Context API** - State management
- **Video.js** - Video player
- **SweetAlert2** - Beautiful alerts
- **PostCSS** - CSS transformations

## 📁 Project Structure

```
src/
├── components/           # Reusable React components
│   ├── Layout.jsx       # Main layout wrapper
│   ├── Navbar.jsx       # Navigation bar
│   └── ProtectedRoute.jsx # Route protection wrapper
├── contexts/            # React Context files
│   └── AuthContext.jsx  # Authentication state
├── hooks/               # Custom React hooks
│   ├── useAuth.js       # Auth context hook
│   └── useAsync.js      # Async operations hook
├── pages/               # Page components
│   ├── HomePage.jsx     # Landing page
│   ├── LoginPage.jsx    # Login page
│   ├── RegisterPage.jsx # Registration page
│   ├── DashboardPage.jsx # User dashboard
│   ├── UnauthorizedPage.jsx # 403 error
│   └── NotFoundPage.jsx # 404 error
├── services/            # API services
│   ├── axios.js         # Axios configuration & interceptors
│   └── authService.js   # Authentication API calls
├── styles/              # Stylesheets
├── utils/               # Utility functions
├── App.jsx              # Main app component with routing
├── main.jsx             # Entry point
└── index.css            # Global styles
```

## 🔧 Setup Instructions

### Prerequisites
- Node.js 16+ and npm

### Installation

1. **Install dependencies:**
```bash
npm install
```

2. **Create environment file:**
```bash
cp .env.example .env
```

3. **Update API URL in `.env`:**
```env
VITE_API_URL=http://localhost:8000/api
```

### Development

**Start the development server:**
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

### Building

**Build for production:**
```bash
npm run build
```

**Preview production build:**
```bash
npm run preview
```

## 🔐 Authentication

### Auth Context (`src/contexts/AuthContext.jsx`)

The app uses React Context API for authentication state management:

```javascript
import { useAuth } from './hooks/useAuth';

function MyComponent() {
  const { user, isAuthenticated, login, logout } = useAuth();
  
  return (
    <>
      {isAuthenticated && <p>Welcome, {user.first_name}!</p>}
    </>
  );
}
```

### Protected Routes

Routes are protected using the `ProtectedRoute` component:

```javascript
<Route
  element={
    <ProtectedRoute requiredRoles={['Subscriber']}>
      <Layout />
    </ProtectedRoute>
  }
>
  <Route path="/dashboard" element={<DashboardPage />} />
</Route>
```

## 🌐 Axios Configuration

### Request Interceptor
- Automatically adds authorization token to request headers
- Token is retrieved from localStorage

### Response Interceptor
- Handles 401 (Unauthorized) responses
- Clears auth data and redirects to login page on 401 errors

### Using Axios

```javascript
import axiosInstance from './services/axios';

// GET request
const response = await axiosInstance.get('/videos');

// POST request with data
const response = await axiosInstance.post('/videos', { title: 'My Video' });
```

## 📚 API Services

### Authentication Service (`src/services/authService.js`)

```javascript
import authService from './services/authService';

// Register
await authService.register({
  first_name: 'John',
  last_name: 'Doe',
  email: 'john@example.com',
  password: 'password123',
  password_confirmation: 'password123'
});

// Login
const response = await authService.login('john@example.com', 'password123');
// Returns: { token, user }

// Get current user
await authService.getCurrentUser();

// Logout
await authService.logout();

// Change password
await authService.changePassword('old_password', 'new_password', 'new_password');
```

## 🎯 Custom Hooks

### `useAuth()`
Access authentication context and methods:

```javascript
const { user, isAuthenticated, login, logout, getToken } = useAuth();
```

### `useAsync()`
Handle async operations with loading/error states:

```javascript
const { execute, status, data, error } = useAsync(asyncFunction);

await execute(args);

if (status === 'pending') {
  // Loading...
}
if (status === 'error') {
  // Show error
}
```

## 🎨 Tailwind CSS

Primary color palette has been configured with custom shades:

```css
bg-primary-50 through bg-primary-900
text-primary-600
hover:bg-primary-700
```

Customize in `tailwind.config.js`

## 📦 Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| react-router-dom | ^6+ | Client-side routing |
| axios | ^1+ | HTTP requests |
| tailwindcss | ^3+ | CSS framework |
| sweetalert2 | ^11+ | Alert dialogs |
| video.js | ^8+ | Video player |

## 🚨 Error Handling

### Global Error Handling

SweetAlert2 is used for user-friendly error messages:

```javascript
import Swal from 'sweetalert2';

try {
  // API call
} catch (error) {
  await Swal.fire({
    icon: 'error',
    title: 'Error',
    text: error.response?.data?.message || 'Something went wrong'
  });
}
```

## 🔄 Token Management

Tokens are automatically managed:
- Stored in localStorage on login
- Automatically added to request headers
- Cleared on logout
- Cleared on 401 response

## 🌱 Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| VITE_API_URL | Backend API URL | http://localhost:8000/api |

## 📝 Creating New Pages

1. Create a component in `src/pages/`
2. Add a route in `src/App.jsx`
3. Use protected routes if authentication is required

Example:

```javascript
// src/pages/MyPage.jsx
export default function MyPage() {
  return <div>My Page Content</div>;
}

// src/App.jsx
<Route path="/my-page" element={<MyPage />} />
```

## 📝 Creating New Services

Create API service files in `src/services/`:

```javascript
// src/services/videoService.js
import axiosInstance from './axios';

const videoService = {
  list: () => axiosInstance.get('/videos'),
  get: (id) => axiosInstance.get(`/videos/${id}`),
  create: (data) => axiosInstance.post('/videos', data),
  update: (id, data) => axiosInstance.put(`/videos/${id}`, data),
  delete: (id) => axiosInstance.delete(`/videos/${id}`),
};

export default videoService;
```

## 🐛 Debugging

### Enable Request/Response Logging

Edit `src/services/axios.js`:

```javascript
axiosInstance.interceptors.request.use((config) => {
  console.log('Request:', config);
  return config;
});

axiosInstance.interceptors.response.use((response) => {
  console.log('Response:', response);
  return response;
});
```

## 📱 Responsive Design

The app uses Tailwind's responsive utilities:

```jsx
<div className="grid md:grid-cols-2 lg:grid-cols-3">
  {/* Responsive grid */}
</div>
```

## 🔗 Useful Links

- [React Documentation](https://react.dev)
- [Vite Documentation](https://vite.dev)
- [Tailwind CSS](https://tailwindcss.com)
- [React Router](https://reactrouter.com)
- [Axios Documentation](https://axios-http.com)
- [Video.js Documentation](https://docs.videojs.com)
- [SweetAlert2](https://sweetalert2.github.io)

## 📄 License

This project is part of the Molunzaka TV platform.

## 🤝 Contributing

When adding new features:
1. Follow the existing folder structure
2. Use semantic naming conventions
3. Add error handling with SweetAlert2
4. Test protected routes with different user roles
5. Keep components small and reusable

---

**Happy coding! 🎉**
