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
          <div class="map-container" style="position: relative; height: 380px;">
            <div id="stop-route-map" style="width: 100%; height: 100%; min-height: 380px; border-radius: var(--border-radius-md);"></div>
            <button 
              @click="toggleFollowMode" 
              :class="['btn-recenter', { 'active': isFollowing }]"
              title="Recenter on Location"
            >
              <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.51-4.38-4.06-7.93-8.44-8.44V1h-2v1.56C6.12 3.07 2.57 6.62 2.06 11H0v2h2.06c.51 4.38 4.06 7.93 8.44 8.44V23h-2v-1.56c4.38-.51 7.93-4.06 8.44-8.44H24v-2h-3.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
              </svg>
            </button>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
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

// Map and Routing refs
const map = ref(null);
const driverMarker = ref(null);
const stopMarker = ref(null);
const directionsRenderer = ref(null);
const directionsService = ref(null);

// Directions measurements from Google
const googleDistance = ref(null);
const googleDuration = ref(null);

const parseCoords = (mapLink) => {
  if (!mapLink) return null;
  const queryPattern = /[?&](query|q)=([-+]?\d+\.\d+),([-+]?\d+\.\d+)/;
  const matchQuery = mapLink.match(queryPattern);
  if (matchQuery) {
    return { lat: parseFloat(matchQuery[2]), lng: parseFloat(matchQuery[3]) };
  }
  const atPattern = /@([-+]?\d+\.\d+),([-+]?\d+\.\d+)/;
  const matchAt = mapLink.match(atPattern);
  if (matchAt) {
    return { lat: parseFloat(matchAt[1]), lng: parseFloat(matchAt[2]) };
  }
  return null;
};

const geocodeAddress = (address) => {
  if (!address) return Promise.resolve(null);
  return new Promise((resolve) => {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
      if (status === 'OK' && results[0] && results[0].geometry) {
        const location = results[0].geometry.location;
        resolve({ lat: location.lat(), lng: location.lng() });
      } else {
        console.warn('Google Geocoding failed:', status);
        resolve(null);
      }
    });
  });
};

const googleMapsUrl = computed(() => {
  if (!stop.value?.location) return '#';
  if (stop.value.location.map_link) {
    return stop.value.location.map_link;
  }
  const query = encodeURIComponent(stop.value.location.service_address);
  return `https://www.google.com/maps/search/?api=1&query=${query}`;
});

const isRouteActive = computed(() => {
  return routeStore.routeRun?.status === 'in_progress';
});

// Haversine formula to compute great-circle distance in miles (fallback)
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
  if (googleDistance.value) {
    return googleDistance.value;
  }
  const dist = computedDistance.value;
  if (dist !== null) {
    return `${dist.toFixed(1)} miles`;
  }
  
  const pos = stop.value?.position || 1;
  const fallbackDist = 0.8 + (pos - 1) * 1.5;
  return `${fallbackDist.toFixed(1)} miles`;
});

// Compute dynamic estimated arrival
const estimatedArrival = computed(() => {
  const date = new Date();
  
  if (googleDuration.value) {
    let durationMinutes = 5;
    const minutesMatch = googleDuration.value.match(/(\d+)\s*min/);
    const hoursMatch = googleDuration.value.match(/(\d+)\s*hour/);
    if (minutesMatch) durationMinutes = parseInt(minutesMatch[1]);
    if (hoursMatch) durationMinutes += parseInt(hoursMatch[1]) * 60;
    
    date.setMinutes(date.getMinutes() + durationMinutes);
  } else {
    const dist = computedDistance.value;
    if (dist !== null) {
      const travelTimeMinutes = Math.round((dist / 25) * 60) + 2;
      date.setMinutes(date.getMinutes() + travelTimeMinutes);
    } else {
      const pos = stop.value?.position || 1;
      const fallbackMinutes = 15 + (pos - 1) * 20;
      date.setMinutes(date.getMinutes() + fallbackMinutes);
    }
  }
  
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

// Map and Recenter follow modes
const isFollowing = ref(true);

const toggleFollowMode = () => {
  isFollowing.value = !isFollowing.value;
  if (isFollowing.value) {
    recenterMapOnDriver();
  }
};

const recenterMapOnDriver = () => {
  if (map.value && currentLat.value !== null && currentLng.value !== null) {
    map.value.setCenter({ lat: currentLat.value, lng: currentLng.value });
    map.value.setZoom(16);
  }
};

// Google Map canvas rendering & routing
const initGoogleMap = () => {
  const checkGoogle = setInterval(async () => {
    if (window.google && window.google.maps) {
      clearInterval(checkGoogle);

      // Resolve target coordinates using Google Geocoder if not parsed
      const loc = stop.value?.location;
      const parsed = parseCoords(loc?.map_link);
      if (parsed) {
        locationLat.value = parsed.lat;
        locationLng.value = parsed.lng;
      } else if (loc?.service_address && (locationLat.value === null || locationLng.value === null)) {
        const coords = await geocodeAddress(loc.service_address);
        if (coords) {
          locationLat.value = coords.lat;
          locationLng.value = coords.lng;
        }
      }

      setupMap();
    }
  }, 100);
};

const setupMap = () => {
  const mapElement = document.getElementById('stop-route-map');
  if (!mapElement) return;

  const loc = stop.value?.location;
  const targetLat = loc?.latitude ? parseFloat(loc.latitude) : locationLat.value;
  const targetLng = loc?.longitude ? parseFloat(loc.longitude) : locationLng.value;

  const defaultCenter = (targetLat && targetLng) 
    ? { lat: targetLat, lng: targetLng } 
    : { lat: 31.5204, lng: 74.3587 };

  map.value = new google.maps.Map(mapElement, {
    center: defaultCenter,
    zoom: 14,
    disableDefaultUI: false,
    zoomControl: true,
    gestureHandling: 'greedy', // Greedy gesture handling makes map dragging & zooming super smooth on mobile
  });

  map.value.addListener('dragstart', () => {
    isFollowing.value = false;
  });

  directionsService.value = new google.maps.DirectionsService();
  directionsRenderer.value = new google.maps.DirectionsRenderer({
    map: map.value,
    suppressMarkers: true,
    preserveViewport: true, // Prevents directionsRenderer from forcing zooms that override user/follow camera
    polylineOptions: {
      strokeColor: '#f59e0b',
      strokeWeight: 6,
      strokeOpacity: 0.8
    }
  });

  if (targetLat && targetLng) {
    stopMarker.value = new google.maps.Marker({
      position: { lat: targetLat, lng: targetLng },
      map: map.value,
      title: loc?.name || 'Stop Location',
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#f59e0b',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2,
        scale: 12,
      }
    });
  }

  updateDriverMarkerAndRoute();
};

const updateDriverMarkerAndRoute = () => {
  if (!map.value) return;

  const loc = stop.value?.location;
  const targetLat = loc?.latitude ? parseFloat(loc.latitude) : locationLat.value;
  const targetLng = loc?.longitude ? parseFloat(loc.longitude) : locationLng.value;

  if (currentLat.value !== null && currentLng.value !== null) {
    const driverPos = { lat: currentLat.value, lng: currentLng.value };

    if (driverMarker.value) {
      driverMarker.value.setPosition(driverPos);
    } else {
      driverMarker.value = new google.maps.Marker({
        position: driverPos,
        map: map.value,
        title: 'Your Location',
        icon: {
          path: 'M23.5 17h-1.5v-3.5c0-1.4-1.1-2.5-2.5-2.5h-9c-1.4 0-2.5 1.1-2.5 2.5v3.5h-1.5c-.8 0-1.5.7-1.5 1.5v3c0 .8.7 1.5 1.5 1.5h18c.8 0 1.5-.7 1.5-1.5v-3c0-.8-.7-1.5-1.5-1.5zm-12.5-3.5c0-.3.2-.5.5-.5h2.5v3h-3v-2.5zm5 0h2.5c.3 0 .5.2.5.5v2.5h-3v-3zm-9 9.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm13 0c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5z',
          fillColor: '#10b981',
          fillOpacity: 1,
          strokeColor: '#ffffff',
          strokeWeight: 1,
          scale: 1.2,
          anchor: new google.maps.Point(12, 12)
        }
      });
    }

    if (targetLat && targetLng && directionsService.value && directionsRenderer.value) {
      directionsService.value.route(
        {
          origin: driverPos,
          destination: { lat: targetLat, lng: targetLng },
          travelMode: google.maps.TravelMode.DRIVING
        },
        (result, status) => {
          if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.value.setDirections(result);
            const leg = result.routes[0].legs[0];
            if (leg) {
              googleDistance.value = leg.distance.text;
              googleDuration.value = leg.duration.text;
            }
          } else {
            console.warn('Google Directions request failed status:', status);
          }
        }
      );
    }
  }
};

watch([currentLat, currentLng], () => {
  updateDriverMarkerAndRoute();
  if (isFollowing.value) {
    recenterMapOnDriver();
  }
});

// Geolocation watchers
const startWatchingLocation = () => {
  if (navigator.geolocation) {
    watchId.value = navigator.geolocation.watchPosition(
      (position) => {
        currentLat.value = position.coords.latitude;
        currentLng.value = position.coords.longitude;
        console.log('Driver location tracked:', currentLat.value, currentLng.value);
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
    initGoogleMap();
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

.btn-recenter {
  position: absolute;
  bottom: 16px;
  right: 16px;
  background: rgba(30, 41, 59, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
  z-index: 10;
}

.btn-recenter:hover {
  background: rgba(30, 41, 59, 1);
  color: #ffffff;
}

.btn-recenter.active {
  background: #3b82f6;
  color: #ffffff;
  border-color: #60a5fa;
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
