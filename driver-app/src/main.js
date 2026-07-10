import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import './style.css';
import App from './App.vue';
import axios from 'axios';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Configure global Axios interceptor to catch 401 Unauthorized errors and force log out
axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response && error.response.status === 401) {
      const authStore = useAuthStore();
      console.warn('Session expired or unauthorized (401). Logging out...');
      await authStore.logout();
      router.push({ name: 'login' });
    }
    return Promise.reject(error);
  }
);

app.mount('#app');
