<template>
  <div class="page-container">
    <!-- Header -->
    <header class="app-header">
      <button @click="goBack" class="btn-back">⬅ Dashboard</button>
      <span class="header-title">Driver Profile</span>
    </header>

    <div class="content-area">
      <!-- Loading State -->
      <div v-if="loading" class="center-state">
        <div class="spinner"></div>
        <p>Loading profile information...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="center-state">
        <div class="error-icon">⚠️</div>
        <p>{{ error }}</p>
        <button @click="loadProfile" class="btn-secondary btn-retry">Retry</button>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profileData" class="profile-layout grid-layout">
        <!-- Left: User Info and Stats -->
        <div class="col-main">
          <!-- User Details Card -->
          <div class="glass-card user-card">
            <div class="avatar-section">
              <div class="avatar-circle">
                <span class="avatar-initial">{{ profileData.driver.name.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="user-meta">
                <h2>{{ profileData.driver.name }}</h2>
                <p class="email">{{ profileData.driver.email }}</p>
                <div class="role-badge">
                  <span class="role-dot"></span>
                  <span>Active Driver</span>
                </div>
              </div>
            </div>
            
            <hr class="profile-divider" />
            
            <div class="info-row">
              <span class="info-label">Member Since</span>
              <span class="info-value">{{ formatDate(profileData.driver.joined_at) }}</span>
            </div>
          </div>

          <!-- Stats Overview Card -->
          <div class="stats-grid">
            <div class="glass-card stat-metric-card">
              <span class="stat-icon">🚚</span>
              <div class="stat-details">
                <span class="metric-label">Completed Routes</span>
                <h3 class="metric-val">{{ profileData.stats.total_routes_completed }}</h3>
              </div>
            </div>

            <div class="glass-card stat-metric-card">
              <span class="stat-icon">📍</span>
              <div class="stat-details">
                <span class="metric-label">Stops Serviced</span>
                <h3 class="metric-val">{{ profileData.stats.total_stops_serviced }}</h3>
              </div>
            </div>

            <div class="glass-card stat-metric-card accent-metric">
              <span class="stat-icon">♻️</span>
              <div class="stat-details">
                <span class="metric-label">Grease Recycled</span>
                <h3 class="metric-val">{{ profileData.stats.total_pounds_collected }} lbs</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Recent Activity Log -->
        <div class="col-aside">
          <div class="glass-card history-card">
            <h3>📋 Recent Activity History</h3>
            <p class="history-subtitle">Your last 10 logged route pickups</p>

            <div v-if="profileData.recent_pickups.length === 0" class="empty-history">
              <span>📭</span>
              <p>No recent activity logged yet.</p>
            </div>

            <div v-else class="history-list">
              <div 
                v-for="event in profileData.recent_pickups" 
                :key="event.id" 
                class="history-item"
              >
                <div class="history-item-header">
                  <div class="location-info">
                    <h4>{{ event.location.name }}</h4>
                    <p class="customer">{{ event.location.customer.name }}</p>
                  </div>
                  <span :class="['badge', `badge-${event.status}`]">
                    {{ event.status }}
                  </span>
                </div>

                <div class="history-item-details">
                  <div class="detail-cell">
                    <span class="cell-lbl">Date</span>
                    <span class="cell-val">{{ formatDate(event.occurred_at) }}</span>
                  </div>
                  <div class="detail-cell text-right">
                    <span class="cell-lbl">Result</span>
                    <span class="cell-val font-highlight" v-if="event.status === 'completed'">
                      {{ event.pounds_collected }} lbs
                    </span>
                    <span class="cell-val text-skip-reason" v-else>
                      {{ event.skip_reason }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useRouteStore } from '../stores/route';

const router = useRouter();
const routeStore = useRouteStore();

const loading = ref(true);
const error = ref('');
const profileData = ref(null);

const goBack = () => {
  router.push({ name: 'route' });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const loadProfile = async () => {
  loading.value = true;
  error.value = '';
  try {
    profileData.value = await routeStore.fetchDriverProfile();
  } catch (err) {
    error.value = 'Failed to load driver profile details.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadProfile();
});
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.btn-back {
  background: transparent;
  border: none;
  color: var(--accent-color);
  font-family: var(--font-sans);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: var(--border-radius-sm);
  transition: background-color 0.2s;
}

.btn-back:hover {
  background-color: rgba(224, 79, 38, 0.08);
}

.header-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
}

.content-area {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.center-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 40px;
  text-align: center;
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

/* Profiles Grid */
@media (min-width: 992px) {
  .grid-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 24px;
    align-items: start;
  }
}

.col-main, .col-aside {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* User Card styles */
.user-card {
  padding: 24px;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-circle {
  width: 72px;
  height: 72px;
  background: var(--accent-gradient);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(224, 79, 38, 0.25);
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.avatar-initial {
  font-size: 28px;
  font-weight: 700;
  color: white;
}

.user-meta h2 {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

.user-meta .email {
  font-size: 14px;
  color: var(--text-secondary);
  margin-top: 4px;
  word-break: break-all;
}

.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(16, 185, 129, 0.1);
  color: var(--status-completed);
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 9999px;
  margin-top: 8px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.role-dot {
  width: 6px;
  height: 6px;
  background-color: var(--status-completed);
  border-radius: 50%;
}

.profile-divider {
  border: 0;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin: 20px 0;
}

.info-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.info-label {
  color: var(--text-secondary);
}

.info-value {
  font-weight: 600;
  color: var(--text-primary);
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
}

.stat-metric-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px;
}

.stat-icon {
  font-size: 24px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-glass);
}

.stat-details {
  display: flex;
  flex-direction: column;
}

.metric-label {
  font-size: 12px;
  color: var(--text-secondary);
  font-weight: 500;
}

.metric-val {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-primary);
  margin-top: 2px;
}

.accent-metric {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
  border-color: rgba(16, 185, 129, 0.15);
}

.accent-metric .metric-val {
  color: var(--status-completed);
}

/* History Card */
.history-card {
  padding: 24px;
  height: 100%;
}

.history-card h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
}

.history-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  margin-top: 2px;
  margin-bottom: 20px;
}

.empty-history {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: var(--text-muted);
  font-size: 14px;
  text-align: center;
  gap: 8px;
}

.empty-history span {
  font-size: 32px;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.history-item {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border-glass);
  border-radius: var(--border-radius-md);
  padding: 14px;
  transition: background-color 0.2s;
}

.history-item:hover {
  background: rgba(255, 255, 255, 0.04);
}

.history-item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.location-info h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1.3;
}

.location-info .customer {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: 2px;
}

.history-item-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.detail-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.cell-lbl {
  font-size: 10px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cell-val {
  font-size: 13px;
  color: var(--text-secondary);
  font-weight: 500;
}

.font-highlight {
  color: var(--status-completed);
  font-weight: 600;
}

.text-skip-reason {
  color: var(--status-skipped);
  text-transform: capitalize;
}

.text-right {
  text-align: right;
}
</style>
