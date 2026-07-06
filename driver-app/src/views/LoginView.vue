<template>
  <div class="login-container">
    <div class="glass-card login-card">
      <div class="logo-area">
        <img src="/logo.jpg" class="app-login-logo" alt="JNC GreaseCycling Logo" />
        <h1>JNC GreaseCycling</h1>
        <p>Driver Portal</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input
            id="email"
            v-model="email"
            type="email"
            class="form-input"
            placeholder="driver@greasecycling.com"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            class="form-input"
            placeholder="••••••••"
            required
            :disabled="loading"
          />
        </div>

        <div v-if="error" class="error-msg">
          {{ error }}
        </div>

        <button type="submit" class="btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          <span>{{ loading ? 'Signing In...' : 'Sign In' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const router = useRouter();
const authStore = useAuthStore();

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  const result = await authStore.login(email.value, password.value);
  loading.value = false;
  
  if (result.success) {
    router.push({ name: 'route' });
  } else {
    error.value = result.message;
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 440px;
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.logo-area {
  text-align: center;
  margin-bottom: 32px;
}

.logo-icon {
  font-size: 48px;
  color: #10b981;
  margin-bottom: 12px;
  display: inline-block;
  animation: rotateLogo 8s linear infinite;
}

.logo-area h1 {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.5px;
}

.logo-area p {
  font-size: 14px;
  color: var(--text-secondary);
  margin-top: 4px;
}

.error-msg {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  padding: 12px;
  border-radius: var(--border-radius-md);
  font-size: 14px;
  margin-bottom: 20px;
  text-align: center;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes rotateLogo {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
