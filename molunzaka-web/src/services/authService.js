import axiosInstance from './axios';

const authService = {
  register: (data) =>
    axiosInstance.post('/auth/register', data),

  login: (email, password) =>
    axiosInstance.post('/auth/login', { email, password }),

  logout: () =>
    axiosInstance.post('/auth/logout'),

  verifyEmail: (token) =>
    axiosInstance.post('/auth/verify-email', { token }),

  resendVerificationEmail: (email) =>
    axiosInstance.post('/auth/resend-verification', { email }),

  requestPasswordReset: (email) =>
    axiosInstance.post('/auth/forgot-password', { email }),

  resetPassword: (token, password, password_confirmation) =>
    axiosInstance.post('/auth/reset-password', {
      token,
      password,
      password_confirmation,
    }),

  getCurrentUser: () =>
    axiosInstance.get('/auth/me'),

  updateProfile: (data) =>
    axiosInstance.put('/auth/profile', data),

  changePassword: (current_password, password, password_confirmation) =>
    axiosInstance.post('/auth/change-password', {
      current_password,
      password,
      password_confirmation,
    }),
};

export default authService;
