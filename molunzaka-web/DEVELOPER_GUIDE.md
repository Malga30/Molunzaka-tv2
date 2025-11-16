# Molunzaka Web Frontend - Developer Guide

## Quick Start

```bash
# Install dependencies
npm install

# Create environment file
cp .env.example .env

# Start development server
npm run dev
```

Visit `http://localhost:5173`

## Project Architecture

### Context API Store

The app uses React Context for global state management:

```
AuthContext
├── user (current user object)
├── isAuthenticated (boolean)
├── loading (boolean)
├── login() - function
├── logout() - function
├── updateUser() - function
└── getToken() - function
```

### Axios Interceptors

**Request Interceptor:**
- Automatically adds `Authorization: Bearer {token}` header
- Reads token from `localStorage.getItem('token')`

**Response Interceptor:**
- Catches 401 responses
- Clears auth data and redirects to `/login`

### Protected Routes

Routes are wrapped with `ProtectedRoute` component for authentication:

```jsx
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

## API Integration

### Adding New Endpoints

1. **Create service file** in `src/services/`:

```javascript
// src/services/categoryService.js
import axiosInstance from './axios';

const categoryService = {
  list: () => axiosInstance.get('/categories'),
  get: (id) => axiosInstance.get(`/categories/${id}`),
  create: (data) => axiosInstance.post('/categories', data),
  update: (id, data) => axiosInstance.put(`/categories/${id}`, data),
  delete: (id) => axiosInstance.delete(`/categories/${id}`),
};

export default categoryService;
```

2. **Use in component**:

```javascript
import useAsync from '../hooks/useAsync';
import categoryService from '../services/categoryService';

function MyComponent() {
  const { execute, data, status } = useAsync(categoryService.list);

  useEffect(() => {
    execute();
  }, []);

  if (status === 'pending') return <div>Loading...</div>;

  return <div>{data?.map(cat => <div key={cat.id}>{cat.name}</div>)}</div>;
}
```

## Common Patterns

### Form Handling with Validation

```javascript
import { useState } from 'react';
import Swal from 'sweetalert2';

function MyForm() {
  const [formData, setFormData] = useState({ name: '', email: '' });

  const handleChange = (e) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      // API call
      await Swal.fire({ icon: 'success', title: 'Saved!' });
    } catch (error) {
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Something went wrong'
      });
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input name="name" value={formData.name} onChange={handleChange} />
      <button type="submit">Submit</button>
    </form>
  );
}
```

### Conditional Rendering Based on Role

```javascript
import useAuth from '../hooks/useAuth';

function AdminPanel() {
  const { user } = useAuth();

  if (!user?.roles?.includes('Admin')) {
    return <div>Access Denied</div>;
  }

  return <div>Admin Content</div>;
}
```

### Loader Component

```javascript
function Loader() {
  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>
  );
}
```

## File Organization Guidelines

### Components (`src/components/`)

- **Layout components** - Page wrappers (Layout, Sidebar, Navbar)
- **Feature components** - Components for specific features (VideoCard, ProfileForm)
- **Shared components** - Reusable UI elements (Button, Input, Modal)
- **Routes** - ProtectedRoute, PrivateRoute

### Pages (`src/pages/`)

- One component per page
- Named with `Page` suffix: `HomePage`, `LoginPage`, `DashboardPage`
- Should use layout components

### Services (`src/services/`)

- One service per API resource
- Group related endpoints
- Keep functions pure and focused
- Use consistent naming: `list`, `get`, `create`, `update`, `delete`

### Hooks (`src/hooks/`)

- Custom React hooks
- Start with `use` prefix
- Encapsulate complex logic
- Example: `useAuth`, `useAsync`, `useLocalStorage`, `useFetch`

### Utils (`src/utils/`)

- Pure utility functions
- No React dependencies
- Reusable across components
- Example: `formatDate`, `validateEmail`, `truncateText`

## Styling with Tailwind

### Color System

```jsx
// Primary colors (configured in tailwind.config.js)
<div className="bg-primary-600 text-white">Primary</div>

// Responsive classes
<div className="text-sm md:text-base lg:text-lg">Responsive text</div>

// Dark mode
<div className="dark:bg-gray-900">Dark mode support</div>
```

### Common Patterns

```jsx
// Button
<button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition">

// Input
<input className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />

// Card
<div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">

// Grid
<div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
```

## Performance Tips

### Code Splitting

Use React Router's lazy loading for pages:

```javascript
import { lazy, Suspense } from 'react';

const DashboardPage = lazy(() => import('../pages/DashboardPage'));

<Suspense fallback={<Loader />}>
  <Route path="/dashboard" element={<DashboardPage />} />
</Suspense>
```

### Memoization

Use `useMemo` for expensive computations:

```javascript
const memoizedValue = useMemo(() => expensiveFunction(), [dependency]);
```

### Lazy Image Loading

```jsx
<img loading="lazy" src={url} alt={alt} />
```

## Testing

### Running Tests

```bash
npm test
```

### Example Test

```javascript
import { render, screen } from '@testing-library/react';
import LoginPage from '../pages/LoginPage';

test('renders login form', () => {
  render(<LoginPage />);
  expect(screen.getByText(/email/i)).toBeInTheDocument();
});
```

## Debugging

### Browser DevTools

1. React Developer Tools (Chrome Extension)
2. Inspect Context values
3. Check network requests in Network tab
4. Check localStorage in Application tab

### Console Logging

```javascript
// Log API requests (in src/services/axios.js)
axiosInstance.interceptors.request.use((config) => {
  console.log('Request:', config);
  return config;
});
```

## Common Issues & Solutions

### Issue: 401 Unauthorized on first load
**Solution:** Check if token is stored in localStorage. Verify API URL in `.env`

### Issue: CORS errors
**Solution:** Configure backend to allow CORS. Check `VITE_API_URL` in `.env`

### Issue: Components not updating
**Solution:** Ensure you're using Context properly. Check if state updates are triggering re-renders

### Issue: Images not loading
**Solution:** Check image paths. For public images, use `/public/path`. For relative paths, use `import`

## Deployment

### Build for Production

```bash
npm run build
```

### Environment Variables for Production

Create `.env.production`:
```env
VITE_API_URL=https://api.production.com/api
```

### Static Hosting

The `dist/` folder contains all production files. Upload to:
- Netlify
- Vercel
- GitHub Pages
- Any static hosting service

## Environment Setup

### Development

```env
VITE_API_URL=http://localhost:8000/api
```

### Production

```env
VITE_API_URL=https://api.molunzaka.com/api
```

## Resources

- [React Docs](https://react.dev)
- [Vite Docs](https://vite.dev)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [React Router](https://reactrouter.com)
- [Axios](https://axios-http.com/docs/intro)
- [SweetAlert2](https://sweetalert2.github.io)
- [Video.js](https://docs.videojs.com)

---

Happy coding! 🚀
