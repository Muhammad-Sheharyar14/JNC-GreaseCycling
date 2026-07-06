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
      <!-- Date Banner -->
      <div class="date-banner">
        <h2>Today's Route</h2>
        <p>{{ formattedDate }}</p>
      </div>

      <!-- Loading / Error states -->
      <div v-if="routeStore.loading" class="center-state">
        <div class="spinner"></div>
        <p>Loading your route...</p>
      </div>

      <div v-else-if="routeStore.error" class="center-state">
        <div class="error-icon">⚠️</div>
        <p>{{ routeStore.error }}</p>
        <button @click="routeStore.fetchTodayRoute" class="btn-secondary btn-retry">Retry</button>
      </div>

      <div v-else-if="!routeStore.routeId" class="center-state">
        <div class="empty-icon">☕</div>
        <h3>No Route Today</h3>
        <p>You have no scheduled routes or stops assigned for today.</p>
        <button @click="routeStore.fetchTodayRoute" class="btn-secondary btn-retry">Refresh</button>
      </div>

      <!-- Active Route View -->
      <div v-else class="route-active-area">
        <!-- Start Route Button (if not started) -->
        <div v-if="!routeRunStatus || routeRunStatus === 'not_started'" class="glass-card start-card">
          <h3>Your Route is Ready</h3>
          <p>Tap below to start tracking your route execution and timestamps.</p>
          <button @click="startRouteRun" class="btn-primary btn-start-route" :disabled="actionLoading">
            <span>🚀</span>
            <span>{{ actionLoading ? 'Starting...' : 'Start Route Run' }}</span>
          </button>
        </div>

        <!-- Route Run Status Banner -->
        <div v-else-if="routeRunStatus === 'completed'" class="glass-card completed-banner-card">
          <div class="success-icon">🎉</div>
          <h3>Route Run Completed</h3>
          <p>All stops have been logged. Your completion timestamp has been recorded.</p>
          <div class="stats-row">
            <div class="stat-col">
              <span class="stat-val">{{ completedCount }}</span>
              <span class="stat-lbl">Completed</span>
            </div>
            <div class="stat-col">
              <span class="stat-val">{{ skippedCount }}</span>
              <span class="stat-lbl">Skipped</span>
            </div>
            <div class="stat-col">
              <span class="stat-val">{{ pendingCount }}</span>
              <span class="stat-lbl">Pending</span>
            </div>
          </div>
        </div>

        <!-- Stops List -->
        <div class="stops-section">
          <div class="section-title-row">
            <h3>STOPS IN ORDER ({{ routeStore.stops.length }})</h3>
            <button @click="routeStore.fetchTodayRoute" class="btn-refresh-icon">🔄</button>
          </div>

          <div class="stops-list">
            <div
              v-for="stop in routeStore.stops"
              :key="stop.id"
              class="glass-card stop-card"
              :class="{
                'stop-disabled': !isRouteActive,
                'border-completed': stop.status === 'completed',
                'border-skipped': stop.status === 'skipped'
              }"
              @click="goToStop(stop)"
            >
              <div class="stop-header">
                <div class="stop-number">#{{ stop.position }}</div>
                <span :class="['badge', `badge-${stop.status}`]">{{ stop.status }}</span>
              </div>
              <div class="stop-details">
                <h4>{{ stop.location.name }}</h4>
                <p class="customer-name">{{ stop.location.customer.name }}</p>
                <p class="address">{{ stop.location.service_address }}</p>
              </div>
              <div class="chevron">➔</div>
            </div>
          </div>
        </div>

        <!-- Mark Complete Button (if in progress) -->
        <div v-if="routeRunStatus === 'in_progress'" class="floating-complete-bar">
          <button @click="completeRouteRun" class="btn-primary btn-complete-route" :disabled="actionLoading">
            <span>🏁</span>
            <span>{{ actionLoading ? 'Completing...' : 'Mark Route Complete' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Custom Dialog Modal -->
    <CustomModal
      :show="modalState.show"
      :title="modalState.title"
      :message="modalState.message"
      :confirm-text="modalState.confirmText"
      :cancel-text="modalState.cancelText"
      :is-alert="modalState.isAlert"
      @confirm="modalState.onConfirm"
      @cancel="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useRouteStore } from '../stores/route';
import CustomModal from '../components/CustomModal.vue';

const router = useRouter();
const authStore = useAuthStore();
const routeStore = useRouteStore();

// Dropdown Menu State
const showDropdown = ref(false);
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};
const closeDropdown = (e) => {
  const container = document.querySelector('.user-dropdown-container');
  if (container && !container.contains(e.target)) {
    showDropdown.value = false;
  }
};
const viewProfile = () => {
  showDropdown.value = false;
  router.push({ name: 'profile' });
};

const actionLoading = ref(false);

// Modal state
const modalState = ref({
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  isAlert: false,
  onConfirm: () => {},
});

const showModal = (options) => {
  modalState.value = {
    show: true,
    title: options.title || 'Are you sure?',
    message: options.message,
    confirmText: options.confirmText || 'Confirm',
    cancelText: options.cancelText || 'Cancel',
    isAlert: options.isAlert || false,
    onConfirm: () => {
      modalState.value.show = false;
      if (options.onConfirm) options.onConfirm();
    }
  };
};

const closeModal = () => {
  modalState.value.show = false;
};

const formattedDate = computed(() => {
  return new Date().toLocaleDateString(undefined, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const routeRunStatus = computed(() => {
  return routeStore.routeRun?.status || 'not_started';
});

const isRouteActive = computed(() => {
  return routeRunStatus.value === 'in_progress';
});

const completedCount = computed(() => routeStore.stops.filter(s => s.status === 'completed').length);
const skippedCount = computed(() => routeStore.stops.filter(s => s.status === 'skipped').length);
const pendingCount = computed(() => routeStore.stops.filter(s => s.status === 'pending').length);

const handleLogout = () => {
  showModal({
    title: 'Sign Out',
    message: 'Are you sure you want to sign out of the Driver Portal?',
    confirmText: 'Sign Out',
    onConfirm: async () => {
      await authStore.logout();
      router.push({ name: 'login' });
    }
  });
};

const startRouteRun = async () => {
  actionLoading.value = true;
  await routeStore.startRoute();
  actionLoading.value = false;
};

const completeRouteRun = () => {
  const message = pendingCount.value > 0
    ? `You still have ${pendingCount.value} pending stops. Are you sure you want to complete this route run?`
    : 'Mark route run as complete?';

  showModal({
    title: 'Complete Route Run',
    message,
    confirmText: 'Complete',
    onConfirm: async () => {
      actionLoading.value = true;
      await routeStore.completeRoute();
      actionLoading.value = false;
    }
  });
};

const goToStop = (stop) => {
  if (!isRouteActive.value && routeRunStatus.value !== 'completed') {
    showModal({
      title: 'Route Not Started',
      message: 'Please start the route run first before viewing stop details.',
      confirmText: 'OK',
      isAlert: true
    });
    return;
  }
  router.push({ name: 'stop-detail', params: { id: stop.id } });
};

onMounted(() => {
  routeStore.fetchTodayRoute();
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
  padding-bottom: 90px; /* Space for floating complete button */
}

.driver-name {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 500;
}

.btn-logout {
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  margin-left: 12px;
  transition: transform 0.2s;
}

.btn-logout:active {
  transform: scale(0.85);
}

.date-banner {
  margin-bottom: 20px;
}

.date-banner h2 {
  font-size: 22px;
  font-weight: 700;
}

.date-banner p {
  color: var(--text-secondary);
  font-size: 14px;
  margin-top: 2px;
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

.error-icon, .empty-icon, .success-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.btn-retry {
  margin-top: 16px;
  width: auto;
  padding: 10px 20px;
}

.start-card {
  text-align: center;
  margin-bottom: 24px;
}

.start-card h3 {
  margin-bottom: 8px;
}

.start-card p {
  font-size: 14px;
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.completed-banner-card {
  text-align: center;
  margin-bottom: 24px;
  border-color: rgba(16, 185, 129, 0.3);
  background: rgba(16, 185, 129, 0.05);
}

.completed-banner-card h3 {
  color: var(--status-completed);
  margin-bottom: 8px;
}

.completed-banner-card p {
  font-size: 14px;
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.stats-row {
  display: flex;
  justify-content: space-around;
  border-top: 1px solid var(--border-glass);
  padding-top: 16px;
}

.stat-col {
  display: flex;
  flex-direction: column;
}

.stat-val {
  font-size: 20px;
  font-weight: 700;
}

.stat-lbl {
  font-size: 12px;
  color: var(--text-muted);
}

.stops-section {
  flex: 1;
}

.section-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.section-title-row h3 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  letter-spacing: 0.5px;
}

.btn-refresh-icon {
  background: transparent;
  border: none;
  font-size: 16px;
  cursor: pointer;
}

.stops-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

@media (min-width: 640px) {
  .stops-list {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 960px) {
  .stops-list {
    grid-template-columns: repeat(3, 1fr);
  }
}

.stop-card {
  display: flex;
  align-items: center;
  padding: 16px;
  cursor: pointer;
  position: relative;
}

.stop-disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.border-completed {
  border-left: 4px solid var(--status-completed);
}

.border-skipped {
  border-left: 4px solid var(--status-skipped);
}

.stop-header {
  margin-right: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.stop-number {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

.stop-details {
  flex: 1;
}

.stop-details h4 {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
}

.customer-name {
  font-size: 13px;
  color: var(--text-secondary);
  margin-top: 2px;
}

.address {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 4px;
}

.chevron {
  color: var(--text-muted);
  font-size: 18px;
  margin-left: 12px;
}

.floating-complete-bar {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 440px;
  padding: 0 20px;
  z-index: 10;
}

.btn-complete-route {
  background: linear-gradient(135deg, #10b981 0%, #047857 100%);
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
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
