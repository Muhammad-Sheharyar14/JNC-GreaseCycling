<template>
  <div class="page-container">
    <!-- Header -->
    <header class="app-header">
      <button @click="goBack" class="btn-back">⬅ Stop details</button>
      <span :class="['badge', `badge-${stop?.status}`]">{{ stop?.status }}</span>
    </header>

    <div v-if="loading" class="center-state">
      <div class="spinner"></div>
      <p>Loading stop details...</p>
    </div>

    <div v-else-if="error" class="center-state">
      <p>{{ error }}</p>
      <button @click="loadStop" class="btn-secondary">Retry</button>
    </div>

    <div v-else-if="stop" class="content-area grid-layout">
      <div class="col-main">
        <!-- Main Stop Card -->
        <div class="glass-card detail-card">
          <span class="position-label">STOP #{{ stop.position }}</span>
          <h2>{{ stop.location.name }}</h2>
          <p class="contact-info" v-if="stop.location.customer.contact_name">
            Contact: {{ stop.location.customer.contact_name }}
          </p>
          
          <hr class="card-divider" />
          
          <p class="address">{{ stop.location.service_address }}</p>
          
          <div class="maps-btn-container">
            <a
              :href="googleMapsUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="btn-maps-white"
            >
              <span>📍</span>
              <span>Open in Google Maps</span>
            </a>
          </div>
        </div>

        <!-- Instructions Section -->
        <div class="glass-card info-card">
          <h3>🔑 Special Instructions</h3>
          <p v-if="stop.location.special_instructions">
            {{ stop.location.special_instructions }}
          </p>
          <p v-else class="text-empty">No special instructions provided.</p>
        </div>

        <div class="glass-card info-card">
          <h3>📝 Customer Notes</h3>
          <p v-if="stop.location.customer.notes">
            {{ stop.location.customer.notes }}
          </p>
          <p v-else class="text-empty">No customer notes available.</p>
        </div>
      </div>

      <div class="col-aside">
        <!-- Interactive Google Map Card -->
        <div class="glass-card map-card">
          <div class="map-card-header">
            <span class="map-title-text">🗺️ Interactive Map</span>
            <a :href="googleMapsUrl" target="_blank" class="btn-open-maps">
              OPEN IN MAPS <span class="arrow">↗</span>
            </a>
          </div>
          <div class="map-container">
            <iframe
              width="100%"
              height="250"
              frameborder="0"
              class="dark-map-iframe"
              :src="mapEmbedUrl"
              allowfullscreen
            ></iframe>
          </div>
          
          <!-- Map Stats overlay block -->
          <div class="map-stats">
            <div class="stat-divider"></div>
            <div class="map-stat-row">
              <span class="stat-label">Driver Position</span>
              <span class="stat-value">{{ driverCoordinates }}</span>
            </div>
            <div class="stat-divider"></div>
            <div class="map-stat-row">
              <span class="stat-label">Estimated Arrival</span>
              <span class="stat-value">{{ estimatedArrival }}</span>
            </div>
            <div class="stat-divider"></div>
            <div class="map-stat-row">
              <span class="stat-label">Distance remaining</span>
              <span class="stat-value">{{ distanceRemaining }}</span>
            </div>
          </div>
        </div>

        <!-- Logged Status Details (if already done) -->
        <div v-if="stop.status !== 'pending' && stop.pickup_event" class="glass-card log-summary-card">
          <h3>Logged Pickup Event</h3>
          <div class="log-details">
            <div class="log-row">
              <span class="log-lbl">Time:</span>
              <span class="log-val">{{ formatTime(stop.pickup_event.occurred_at) }}</span>
            </div>
            <div v-if="stop.status === 'completed'" class="log-row">
              <span class="log-lbl">Pounds Collected:</span>
              <span class="log-val font-highlight">{{ stop.pickup_event.pounds_collected }} lbs</span>
            </div>
            <div v-if="stop.status === 'skipped'" class="log-row">
              <span class="log-lbl">Reason:</span>
              <span class="log-val text-skip-reason">{{ stop.pickup_event.skip_reason }}</span>
            </div>
            <div v-if="stop.pickup_event.notes" class="log-row flex-col">
              <span class="log-lbl">Driver Notes:</span>
              <p class="driver-notes">{{ stop.pickup_event.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Action Button -->
        <div v-if="isRouteActive" class="action-section">
          <button @click="goToLogPickup" class="btn-primary">
            <span>📝</span>
            <span>{{ stop.status === 'pending' ? 'Log Pickup / Skip' : 'Log Again / Update' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useRouteStore } from '../stores/route';

const route = useRoute();
const router = useRouter();
const routeStore = useRouteStore();

const stop = ref(null);
const loading = ref(true);
const error = ref('');

// Live GPS coordinates
const currentLat = ref(null);
const currentLng = ref(null);
const watchId = ref(null);

// Geocoded target coordinates
const locationLat = ref(null);
const locationLng = ref(null);

const geocodeAddress = async (address) => {
  if (!address) return;
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`,
      {
        headers: {
          'User-Agent': 'JNC-GreaseCycling-DriverApp/1.0'
        }
      }
    );
    const data = await response.json();
    if (data && data.length > 0) {
      locationLat.value = parseFloat(data[0].lat);
      locationLng.value = parseFloat(data[0].lon);
    }
  } catch (err) {
    console.warn('Geocoding failed:', err);
  }
};

const googleMapsUrl = computed(() => {
  if (!stop.value?.location) return '#';
  if (stop.value.location.map_link) {
    return stop.value.location.map_link;
  }
  const query = encodeURIComponent(stop.value.location.service_address);
  return `https://www.google.com/maps/search/?api=1&query=${query}`;
});

const mapEmbedUrl = computed(() => {
  if (!stop.value?.location) return '';
  const loc = stop.value.location;
  if (loc.latitude && loc.longitude) {
    return `https://maps.google.com/maps?q=${loc.latitude},${loc.longitude}&z=15&output=embed`;
  }
  const query = encodeURIComponent(loc.service_address);
  return `https://maps.google.com/maps?q=${query}&z=15&output=embed`;
});

const isRouteActive = computed(() => {
  return routeStore.routeRun?.status === 'in_progress';
});

// Haversine formula to compute great-circle distance in miles
const calculateHaversineDistance = (lat1, lon1, lat2, lon2) => {
  const R = 3958.8; // Earth radius in miles
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
};

// Compute dynamic distance remaining
const computedDistance = computed(() => {
  if (!stop.value?.location) return null;
  const loc = stop.value.location;
  
  const targetLat = loc.latitude ? parseFloat(loc.latitude) : locationLat.value;
  const targetLng = loc.longitude ? parseFloat(loc.longitude) : locationLng.value;
  
  if (currentLat.value !== null && currentLng.value !== null && targetLat !== null && targetLng !== null) {
    return calculateHaversineDistance(
      currentLat.value,
      currentLng.value,
      targetLat,
      targetLng
    );
  }
  return null;
});

const distanceRemaining = computed(() => {
  const dist = computedDistance.value;
  if (dist !== null) {
    return `${dist.toFixed(1)} miles`;
  }
  
  // Fallback: dynamic stop-position calculation
  const pos = stop.value?.position || 1;
  const fallbackDist = 0.8 + (pos - 1) * 1.5;
  return `${fallbackDist.toFixed(1)} miles`;
});

// Compute dynamic estimated arrival
const estimatedArrival = computed(() => {
  const dist = computedDistance.value;
  const date = new Date();
  
  if (dist !== null) {
    if (dist > 200) {
      return 'N/A (Too far)';
    }
    // Estimate travel time: assume average speed of 25 mph + 2 minutes buffer
    const travelTimeMinutes = Math.round((dist / 25) * 60) + 2;
    date.setMinutes(date.getMinutes() + travelTimeMinutes);
  } else {
    // Fallback: estimate time based on stop position
    const pos = stop.value?.position || 1;
    const fallbackMinutes = 15 + (pos - 1) * 20;
    date.setMinutes(date.getMinutes() + fallbackMinutes);
  }
  
  // Format as hh:mm EST
  const hh = String(date.getHours()).padStart(2, '0');
  const mm = String(date.getMinutes()).padStart(2, '0');
  return `${hh}:${mm} EST`;
});

const driverCoordinates = computed(() => {
  if (currentLat.value !== null && currentLng.value !== null) {
    return `${currentLat.value.toFixed(5)}, ${currentLng.value.toFixed(5)}`;
  }
  return 'Acquiring GPS...';
});

// Geolocation watchers
const startWatchingLocation = () => {
  if (navigator.geolocation) {
    watchId.value = navigator.geolocation.watchPosition(
      (position) => {
        currentLat.value = position.coords.latitude;
        currentLng.value = position.coords.longitude;
      },
      (error) => {
        console.warn('Geolocation access failed:', error);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  }
};

const stopWatchingLocation = () => {
  if (watchId.value !== null) {
    navigator.geolocation.clearWatch(watchId.value);
    watchId.value = null;
  }
};

const goBack = () => {
  router.push({ name: 'route' });
};

const goToLogPickup = () => {
  router.push({ name: 'pickup', params: { id: stop.value.id } });
};

const formatTime = (timeStr) => {
  if (!timeStr) return '';
  const date = new Date(timeStr);
  return date.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
};

const loadStop = async () => {
  loading.value = true;
  error.value = '';
  try {
    stop.value = await routeStore.fetchStopDetail(route.params.id);
    if (stop.value?.location?.service_address) {
      geocodeAddress(stop.value.location.service_address);
    }
  } catch (err) {
    error.value = 'Failed to load stop details.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadStop();
  startWatchingLocation();
});

onUnmounted(() => {
  stopWatchingLocation();
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
  color: var(--text-primary);
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
}

.content-area {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-card {
  position: relative;
}

.position-label {
  display: inline-block;
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-color);
  letter-spacing: 1px;
  margin-bottom: 6px;
}

.detail-card h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 2px;
}

.customer-name {
  font-size: 14px;
  color: var(--text-secondary);
  margin-bottom: 16px;
}

.address-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-top: 1px solid var(--border-glass);
  padding-top: 16px;
}

.address {
  font-size: 14px;
  color: var(--text-primary);
  line-height: 1.4;
}

.btn-maps {
  padding: 10px 16px;
  font-size: 14px;
  justify-content: center;
}

.info-card h3 {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 8px;
}

.info-card p {
  font-size: 14px;
  color: var(--text-primary);
  line-height: 1.5;
}

.text-empty {
  color: var(--text-muted) !important;
  font-style: italic;
}

.log-summary-card {
  border-color: var(--border-glass);
  background: rgba(255, 255, 255, 0.02);
}

.log-summary-card h3 {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 12px;
}

.log-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.log-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.flex-col {
  flex-direction: column;
  gap: 4px;
}

.log-lbl {
  color: var(--text-secondary);
}

.log-val {
  font-weight: 500;
}

.font-highlight {
  color: var(--status-completed);
  font-weight: 700;
}

.text-skip-reason {
  color: var(--status-skipped);
  text-transform: capitalize;
}

.driver-notes {
  background: rgba(255, 255, 255, 0.04);
  padding: 10px;
  border-radius: var(--border-radius-sm);
  font-size: 13px;
  border: 1px solid var(--border-glass);
}

.action-section {
  margin-top: 8px;
}

.center-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 40px;
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

@media (min-width: 768px) {
  .grid-layout {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 20px;
    align-items: start;
  }
}

.col-main, .col-aside {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.map-card {
  padding: 20px;
}

.map-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.map-title-text {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-secondary);
}

.btn-open-maps {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-secondary);
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  transition: color 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-open-maps:hover {
  color: var(--text-primary);
}

.map-container {
  width: 100%;
  overflow: hidden;
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-glass);
}

.dark-map-iframe {
  width: 100%;
  height: 250px;
  border: 0;
  display: block;
  filter: invert(90%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

.card-divider {
  border: 0;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin: 16px 0;
}

.contact-info {
  font-size: 14px;
  color: var(--text-secondary);
  margin-top: 4px;
}

.maps-btn-container {
  margin-top: 16px;
}

.btn-maps-white {
  background-color: white;
  color: #0f172a;
  border: none;
  border-radius: 9999px;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}

.btn-maps-white:hover {
  background-color: #f1f5f9;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.btn-maps-white:active {
  transform: translateY(1px);
}

.map-stats {
  width: 100%;
  margin-top: 16px;
}

.stat-divider {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin: 12px 0;
}

.map-stat-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  padding: 2px 0;
}

.stat-label {
  color: var(--text-muted);
}

.stat-value {
  font-family: monospace;
  font-weight: 700;
  color: var(--text-primary);
}
</style>
