<template>
  <div class="page-container">
    <!-- Header -->
    <header class="app-header">
      <div class="app-logo-title">
        <img src="/logo.jpg" class="app-logo-inline" alt="JNC GreaseCycling Logo" />
        <span class="app-title">GreaseCycling</span>
      </div>
      <div class="header-actions">
        <!-- User Dropdown Menu -->
        <div class="user-dropdown-container">
          <button @click.stop="toggleDropdown" class="btn-user-trigger" aria-haspopup="true" :aria-expanded="showDropdown">
            <div class="avatar-circle-sm">
              <span>{{ authStore.user?.name.charAt(0).toUpperCase() }}</span>
            </div>
            <span class="driver-name-nav" v-if="authStore.user">{{ authStore.user.name }}</span>
            <span class="dropdown-chevron">▼</span>
          </button>
          
          <!-- Dropdown menu panel -->
          <transition name="slide-fade">
            <div v-if="showDropdown" class="user-dropdown-menu glass-card">
              <button @click="viewProfile" class="dropdown-item">
                <span class="item-icon">👤</span>
                <span>View Profile</span>
              </button>
              <button @click="handleLogout" class="dropdown-item dropdown-item-danger">
                <span class="item-icon">🚪</span>
                <span>Sign Out</span>
              </button>
            </div>
          </transition>
        </div>
      </div>
    </header>

    <div class="content-area">
      <!-- Title Banner -->
      <div class="date-banner">
        <h2>Your Assigned Routes</h2>
        <p>{{ formattedDate }}</p>
      </div>

      <!-- Loading / Error states -->
      <div v-if="routeStore.loading" class="center-state">
        <div class="spinner"></div>
        <p>Loading your routes...</p>
      </div>

      <div v-else-if="routeStore.error" class="center-state">
        <div class="error-icon">⚠️</div>
        <p class="text-error">{{ routeStore.error }}</p>
        <button @click="routeStore.fetchAssignedRoutes" class="btn-secondary btn-retry">Refresh</button>
      </div>

      <div v-else class="routes-active-area">
        <!-- Empty State -->
        <div v-if="!routeStore.routesList || routeStore.routesList.length === 0" class="center-state empty-card glass-card">
          <div class="empty-icon">📭</div>
          <h3>No routes assigned for today</h3>
          <p>You're all caught up! If you believe this is an error, please contact dispatch.</p>
          <button @click="routeStore.fetchAssignedRoutes" class="btn-secondary btn-retry">Check Again</button>
        </div>

        <div v-else class="routes-grid">
          <div
            v-for="route in routeStore.routesList"
            :key="route.id"
            class="glass-card route-selection-card"
            @click="selectRoute(route.id)"
          >
            <div class="route-card-top">
              <div class="route-info-left">
                <span class="route-tag">ROUTE</span>
                <h3>{{ route.name }}</h3>
              </div>
              <span
                class="status-badge"
                :class="{
                  'badge-not-started': route.status === 'not_started',
                  'badge-in-progress': route.status === 'in_progress',
                  'badge-completed': route.status === 'completed'
                }"
              >
                {{ formatStatus(route.status) }}
              </span>
            </div>

            <div class="route-card-middle">
              <span class="stops-count-text">
                {{ route.completed_stops + route.skipped_stops }} / {{ route.total_stops }} Stops Serviced
              </span>
              <div class="progress-container">
                <div
                  class="progress-bar"
                  :style="{ width: calculateProgress(route) + '%' }"
                ></div>
              </div>
            </div>

            <div class="route-card-bottom">
              <span class="btn-enter-text">View Route Detail</span>
              <span class="arrow-icon">➔</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useRouteStore } from '../stores/route';

const router = useRouter();
const authStore = useAuthStore();
const routeStore = useRouteStore();

const showDropdown = ref(false);

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const closeDropdown = () => {
  showDropdown.value = false;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};

const viewProfile = () => {
  router.push({ name: 'profile' });
};

const selectRoute = (routeId) => {
  // Clear any existing active route state, then set and go to detailed route view
  routeStore.routeId = routeId;
  router.push({ name: 'route', params: { id: routeId } });
};

const formattedDate = computed(() => {
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date().toLocaleDateString('en-US', options);
});

const formatStatus = (status) => {
  if (status === 'not_started') return 'Not Started';
  if (status === 'in_progress') return 'In Progress';
  if (status === 'completed') return 'Completed';
  return status;
};

const calculateProgress = (route) => {
  if (!route.total_stops) return 0;
  const processed = (route.completed_stops || 0) + (route.skipped_stops || 0);
  return Math.min(Math.round((processed / route.total_stops) * 100), 100);
};

onMounted(() => {
  routeStore.fetchAssignedRoutes();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.content-area {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.date-banner {
  margin-bottom: 24px;
}

.date-banner h2 {
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.date-banner p {
  color: var(--text-secondary);
  font-size: 14px;
  margin-top: 4px;
}

.routes-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

@media (min-width: 768px) {
  .routes-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .routes-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.route-selection-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 24px;
  cursor: pointer;
  height: 200px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid var(--border-glass);
}

.route-selection-card:hover {
  transform: translateY(-4px);
  border-color: rgba(224, 79, 38, 0.3);
  box-shadow: 0 12px 40px rgba(224, 79, 38, 0.08);
}

.route-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.route-info-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.route-tag {
  font-size: 10px;
  font-weight: 800;
  color: var(--accent-color);
  letter-spacing: 1.5px;
}

.route-info-left h3 {
  font-size: 20px;
  font-weight: 700;
}

.status-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 9999px;
  border: 1px solid transparent;
}

.badge-not-started {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  color: var(--text-secondary);
}

.badge-in-progress {
  background: rgba(245, 158, 11, 0.08);
  border-color: rgba(245, 158, 11, 0.2);
  color: var(--status-pending);
}

.badge-completed {
  background: rgba(16, 185, 129, 0.08);
  border-color: rgba(16, 185, 129, 0.2);
  color: var(--status-completed);
}

.route-card-middle {
  margin: 16px 0;
}

.stops-count-text {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-secondary);
}

.progress-container {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 9999px;
  height: 6px;
  overflow: hidden;
  margin-top: 10px;
}

.progress-bar {
  background: var(--accent-gradient);
  height: 100%;
  border-radius: 9999px;
  transition: width 0.3s ease;
}

.route-card-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border-glass);
  padding-top: 14px;
}

.btn-enter-text {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
  transition: color 0.2s ease;
}

.route-selection-card:hover .btn-enter-text {
  color: var(--accent-color);
}

.arrow-icon {
  font-size: 14px;
  color: var(--text-muted);
  transition: transform 0.2s ease, color 0.2s ease;
}

.route-selection-card:hover .arrow-icon {
  transform: translateX(4px);
  color: var(--accent-color);
}

.center-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  text-align: center;
  padding: 40px 20px;
}

.empty-card {
  margin-top: 20px;
  padding: 40px;
  border: 1px dashed var(--border-glass);
  background: rgba(255, 255, 255, 0.01);
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: var(--accent-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-retry {
  margin-top: 16px;
  width: auto;
  padding: 10px 20px;
}

.text-error {
  color: var(--status-skipped);
  margin-bottom: 12px;
}

/* User Dropdown styling */
.user-dropdown-container {
  position: relative;
  display: inline-block;
}

.btn-user-trigger {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-glass);
  border-radius: 9999px;
  padding: 6px 14px 6px 6px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s ease;
}

.btn-user-trigger:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.15);
}

.avatar-circle-sm {
  width: 32px;
  height: 32px;
  background: var(--accent-gradient);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.driver-name-nav {
  display: none;
}

@media (min-width: 640px) {
  .driver-name-nav {
    display: inline;
  }
}

.dropdown-chevron {
  font-size: 8px;
  color: var(--text-muted);
}

.user-dropdown-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  width: 180px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  z-index: 200;
  transform-origin: top right;
}

.dropdown-item {
  background: transparent;
  border: none;
  border-radius: var(--border-radius-sm);
  padding: 10px 12px;
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  text-align: left;
  transition: all 0.2s ease;
  width: 100%;
}

.dropdown-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--accent-color);
}

.dropdown-item-danger {
  color: var(--status-skipped);
}

.dropdown-item-danger:hover {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
}

/* Transitions */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-8px) scale(0.95);
  opacity: 0;
}
</style>
