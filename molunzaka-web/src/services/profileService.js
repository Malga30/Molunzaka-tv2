import axiosInstance from './axios';

const profileService = {
  // Get all profiles
  list: () =>
    axiosInstance.get('/profiles'),

  // Get single profile
  get: (id) =>
    axiosInstance.get(`/profiles/${id}`),

  // Create profile
  create: (data) =>
    axiosInstance.post('/profiles', data),

  // Update profile
  update: (id, data) =>
    axiosInstance.put(`/profiles/${id}`, data),

  // Delete profile
  delete: (id) =>
    axiosInstance.delete(`/profiles/${id}`),

  // Set active profile
  setActive: (id) =>
    axiosInstance.post(`/profiles/${id}/set-active`),

  // Get active profile
  getActive: () =>
    axiosInstance.get('/profiles/active'),

  // Check profile limit
  checkLimit: () =>
    axiosInstance.get('/profiles/limit-check'),
};

export default profileService;
