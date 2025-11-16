import React, { createContext, useCallback, useEffect, useState } from 'react';

export const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Initialize auth state from localStorage
  useEffect(() => {
    const initializeAuth = () => {
      try {
        const token = localStorage.getItem('token');
        const storedUser = localStorage.getItem('user');

        if (token && storedUser) {
          setUser(JSON.parse(storedUser));
          setIsAuthenticated(true);
        }
      } catch (err) {
        console.error('Failed to initialize auth:', err);
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      } finally {
        setLoading(false);
      }
    };

    initializeAuth();
  }, []);

  const login = useCallback((userData, token) => {
    try {
      setUser(userData);
      setIsAuthenticated(true);
      setError(null);

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));

      return true;
    } catch (err) {
      setError(err.message);
      return false;
    }
  }, []);

  const logout = useCallback(() => {
    try {
      setUser(null);
      setIsAuthenticated(false);
      setError(null);

      localStorage.removeItem('token');
      localStorage.removeItem('user');

      return true;
    } catch (err) {
      setError(err.message);
      return false;
    }
  }, []);

  const updateUser = useCallback((userData) => {
    try {
      setUser(userData);
      localStorage.setItem('user', JSON.stringify(userData));
      return true;
    } catch (err) {
      setError(err.message);
      return false;
    }
  }, []);

  const getToken = useCallback(() => {
    return localStorage.getItem('token');
  }, []);

  const value = {
    user,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    updateUser,
    getToken,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
