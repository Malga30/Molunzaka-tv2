import axiosInstance from './axios';

const videoService = {
  // Get all videos
  list: (params = {}) =>
    axiosInstance.get('/videos', { params }),

  // Get single video
  get: (id) =>
    axiosInstance.get(`/videos/${id}`),

  // Upload video
  create: (formData) =>
    axiosInstance.post('/videos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    }),

  // Update video
  update: (id, data) =>
    axiosInstance.put(`/videos/${id}`, data),

  // Delete video
  delete: (id) =>
    axiosInstance.delete(`/videos/${id}`),

  // Get video by category
  getByCategory: (category) =>
    axiosInstance.get(`/videos/category/${category}`),

  // Search videos
  search: (query) =>
    axiosInstance.get('/videos/search', {
      params: { q: query },
    }),

  // Get trending videos
  getTrending: () =>
    axiosInstance.get('/videos/trending'),

  // Rate video
  rate: (id, rating) =>
    axiosInstance.post(`/videos/${id}/rate`, { rating }),

  // Add comment
  addComment: (id, comment) =>
    axiosInstance.post(`/videos/${id}/comments`, { text: comment }),

  // Get comments
  getComments: (id) =>
    axiosInstance.get(`/videos/${id}/comments`),
};

export default videoService;
