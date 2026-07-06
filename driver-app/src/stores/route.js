import { defineStore } from 'pinia';
import axios from 'axios';
import { cacheRoute, getCachedRoute, queuePickup } from '../services/db';

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api';

export const useRouteStore = defineStore('route', {
  state: () => ({
    routeId: null,
    routeRun: null,
    stops: [],
    loading: false,
    error: null,
  }),
  
  actions: {
    async fetchTodayRoute() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`${apiBaseUrl}/driver/route`);
        this.routeId = response.data.route_id;
        this.routeRun = response.data.route_run;
        this.stops = response.data.stops || [];
        
        // Cache this route data locally in IndexedDB
        await cacheRoute({
          routeId: this.routeId,
          routeRun: this.routeRun,
          stops: this.stops,
        });
      } catch (err) {
        console.warn('Network request failed, attempting to load cached route...', err);
        
        // Fallback: Read from local cache in IndexedDB
        const cached = await getCachedRoute();
        if (cached) {
          this.routeId = cached.routeId;
          this.routeRun = cached.routeRun;
          this.stops = cached.stops || [];
          this.error = 'Running in offline mode. Loading cached stops.';
        } else {
          this.error = err.response?.data?.message || 'Failed to load route. No local cache available.';
        }
      } finally {
        this.loading = false;
      }
    },
    
    async startRoute() {
      if (!this.routeId) return;
      try {
        const response = await axios.post(`${apiBaseUrl}/driver/route/start`, {
          route_id: this.routeId,
        });
        this.routeRun = response.data.route_run;
        
        // Update cache
        await cacheRoute({
          routeId: this.routeId,
          routeRun: this.routeRun,
          stops: this.stops,
        });
        return { success: true };
      } catch (err) {
        console.error('Error starting route run:', err);
        return { success: false, message: err.response?.data?.message || 'Could not start route.' };
      }
    },
    
    async completeRoute() {
      if (!this.routeId) return;
      try {
        const response = await axios.post(`${apiBaseUrl}/driver/route/complete`, {
          route_id: this.routeId,
        });
        this.routeRun = response.data.route_run;
        
        // Update cache
        await cacheRoute({
          routeId: this.routeId,
          routeRun: this.routeRun,
          stops: this.stops,
        });
        return { success: true };
      } catch (err) {
        console.error('Error completing route run:', err);
        return { success: false, message: err.response?.data?.message || 'Could not complete route.' };
      }
    },
    
    async fetchStopDetail(stopId) {
      // Find in state first (crucial for offline)
      const stop = this.stops.find(s => s.id === parseInt(stopId));
      if (stop) {
        return stop;
      }
      
      // Fallback to API if online
      try {
        const response = await axios.get(`${apiBaseUrl}/driver/stops/${stopId}`);
        return response.data.stop;
      } catch (err) {
        console.error('Error fetching stop detail:', err);
        throw err;
      }
    },
    
    async logStopPickup(stopId, payload) {
      const stopIndex = this.stops.findIndex(s => s.id === parseInt(stopId));
      
      // Helper function to update store state and IndexedDB cache immediately
      const applyLocalOfflineUpdate = async () => {
        if (stopIndex !== -1) {
          this.stops[stopIndex].status = payload.status;
          this.stops[stopIndex].pickup_event = {
            occurred_at: new Date().toISOString(),
            pounds_collected: payload.status === 'completed' ? payload.pounds_collected : null,
            skip_reason: payload.status === 'skipped' ? payload.skip_reason : null,
            notes: payload.notes || null,
          };
          
          // Auto start route locally if not started
          if (!this.routeRun || this.routeRun.status === 'not_started') {
            this.routeRun = {
              status: 'in_progress',
              started_at: new Date().toISOString(),
            };
          }
          
          await cacheRoute({
            routeId: this.routeId,
            routeRun: this.routeRun,
            stops: this.stops,
          });
        }
      };

      // 1. If navigator is explicitly offline, queue immediately
      if (!navigator.onLine) {
        await queuePickup(stopId, payload);
        await applyLocalOfflineUpdate();
        return { success: true, queuedOffline: true };
      }
      
      // 2. Otherwise try sending to backend
      try {
        const response = await axios.post(`${apiBaseUrl}/driver/stops/${stopId}/pickup`, payload);
        await this.fetchTodayRoute(); // reload stops
        return { success: true, warning: response.data.warning };
      } catch (err) {
        // Fallback: Queue offline if it's a network-level failure (no response from server)
        if (!err.response) {
          console.warn('Server unreachable, queueing pickup offline...', err);
          await queuePickup(stopId, payload);
          await applyLocalOfflineUpdate();
          return { success: true, queuedOffline: true };
        }
        
        // Return verification validation errors directly
        return { success: false, message: err.response?.data?.message || 'Failed to log pickup.' };
      }
    },
    
    async fetchDriverProfile() {
      try {
        const response = await axios.get(`${apiBaseUrl}/driver/profile`);
        return response.data;
      } catch (err) {
        console.error('Error fetching driver profile:', err);
        throw err;
      }
    }
  }
});
