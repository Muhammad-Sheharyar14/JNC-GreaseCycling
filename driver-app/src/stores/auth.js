import { defineStore } from 'pinia';
import axios from 'axios';

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('driver_token') || null,
    user: JSON.parse(localStorage.getItem('driver_user')) || null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  
  actions: {
    async login(email, password) {
      try {
        const response = await axios.post(`${apiBaseUrl}/login`, { email, password });
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('driver_token', token);
        localStorage.setItem('driver_user', JSON.stringify(user));
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        return { success: true };
      } catch (error) {
        const message = error.response?.data?.message || 'Login failed. Please check your credentials.';
        return { success: false, message };
      }
    },
    
    async logout() {
      try {
        if (this.token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
          await axios.post(`${apiBaseUrl}/logout`);
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        
        localStorage.removeItem('driver_token');
        localStorage.removeItem('driver_user');
        
        delete axios.defaults.headers.common['Authorization'];
      }
    },
    
    initialize() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    }
  }
});
