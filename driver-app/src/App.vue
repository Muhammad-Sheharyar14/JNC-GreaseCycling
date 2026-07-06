<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useRouteStore } from './stores/route';
import { syncOfflinePickups } from './services/sync';

const authStore = useAuthStore();
const routeStore = useRouteStore();

const isOnline = ref(navigator.onLine);
const showSyncBanner = ref(false);
const syncCount = ref(0);

const handleOnline = async () => {
  isOnline.value = true;
  
  // Try to sync queued items when coming back online
  try {
    const count = await syncOfflinePickups();
    if (count > 0) {
      syncCount.value = count;
      showSyncBanner.value = true;
      
      // Reload route/stops state
      if (authStore.isAuthenticated) {
        await routeStore.fetchTodayRoute();
      }
      
      // Hide banner after 4 seconds
      setTimeout(() => {
        showSyncBanner.value = false;
      }, 4000);
    }
  } catch (error) {
    console.error('Auto sync error:', error);
  }
};

const handleOffline = () => {
  isOnline.value = false;
};

onMounted(() => {
  authStore.initialize();
  
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
  
  // Trigger initial check/sync on startup if online
  if (isOnline.value) {
    handleOnline();
  }
});

onUnmounted(() => {
  window.removeEventListener('online', handleOnline);
  window.removeEventListener('offline', handleOffline);
});
</script>

<template>
  <div class="app-layout">
    <!-- Offline status notification -->
    <div v-if="!isOnline" class="banner banner-offline">
      <span>📡</span>
      <span>Working Offline (Pickups will be queued)</span>
    </div>

    <!-- Sync notification banner -->
    <div v-if="showSyncBanner" class="banner banner-synced">
      <span>⚡</span>
      <span>Successfully synced {{ syncCount }} offline pickup logs!</span>
    </div>

    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<style>
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.banner {
  padding: 10px 16px;
  font-size: 13px;
  font-weight: 600;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  z-index: 1000;
  animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.banner-offline {
  background-color: #d97706; /* Darker Amber */
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.banner-synced {
  background-color: #059669; /* Darker Emerald */
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

@keyframes slideDown {
  from { transform: translateY(-100%); }
  to { transform: translateY(0); }
}
</style>
