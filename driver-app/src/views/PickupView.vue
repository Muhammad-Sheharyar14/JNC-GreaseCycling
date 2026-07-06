<template>
  <div class="page-container">
    <!-- Header -->
    <header class="app-header">
      <button @click="goBack" class="btn-back">⬅ Back</button>
      <span class="header-title">Log Pickup</span>
    </header>

    <div v-if="loading" class="center-state">
      <div class="spinner"></div>
      <p>Loading stop details...</p>
    </div>

    <div v-else-if="stop" class="content-area">
      <!-- Stop Summary Header -->
      <div class="stop-summary-banner">
        <h3>{{ stop.location.name }}</h3>
        <p>{{ stop.location.service_address }}</p>
      </div>

      <!-- Segmented Status Picker -->
      <div class="form-group">
        <label class="form-label">Pickup Result</label>
        <div class="status-selector">
          <button
            type="button"
            class="status-btn btn-completed"
            :class="{ active: status === 'completed' }"
            @click="status = 'completed'"
          >
            <span>✅</span>
            <span>Collected</span>
          </button>
          <button
            type="button"
            class="status-btn btn-skipped"
            :class="{ active: status === 'skipped' }"
            @click="status = 'skipped'"
          >
            <span>❌</span>
            <span>Skipped</span>
          </button>
        </div>
      </div>

      <!-- Form Inputs -->
      <form @submit.prevent="handleSubmit" class="pickup-form">
        <!-- Completed Fields -->
        <div v-if="status === 'completed'" class="form-group fade-in">
          <label class="form-label" for="pounds">Pounds Collected</label>
          <div class="input-addon-wrapper">
            <input
              id="pounds"
              v-model.number="poundsCollected"
              type="number"
              step="0.01"
              min="0"
              class="form-input text-large"
              placeholder="0.00"
              required
              inputmode="decimal"
              ref="poundsInput"
            />
            <span class="input-addon">lbs</span>
          </div>
        </div>

        <!-- Skipped Fields -->
        <div v-if="status === 'skipped'" class="form-group fade-in">
          <label class="form-label" for="skip_reason">Reason for Skipping</label>
          <select id="skip_reason" v-model="skipReason" class="form-input form-select" required>
            <option value="" disabled selected>Select a reason...</option>
            <option value="closed">Business Closed</option>
            <option value="no_access">No Access / Gates Locked</option>
            <option value="other">Other / Container Empty</option>
          </select>
        </div>

        <!-- Common Notes Field -->
        <div class="form-group">
          <label class="form-label" for="notes">Notes / Observations</label>
          <textarea
            id="notes"
            v-model="notes"
            class="form-input form-textarea"
            rows="3"
            placeholder="Add any details, container conditions, etc. (optional)"
          ></textarea>
        </div>

        <!-- Display Warnings / Errors -->
        <div v-if="warning" class="warning-banner">
          <span class="warning-icon">⚠️</span>
          <p>{{ warning }}</p>
        </div>

        <div v-if="error" class="error-banner">
          <p>{{ error }}</p>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary btn-submit" :disabled="submitting">
          <span v-if="submitting" class="spinner-small"></span>
          <span>{{ submitting ? 'Logging...' : 'Submit Pickup Log' }}</span>
        </button>
      </form>
    </div>

    <!-- Custom Dialog Modal -->
    <CustomModal
      :show="showModal"
      title="Validation Warning"
      :message="modalMessage"
      confirm-text="OK"
      :is-alert="true"
      @confirm="handleModalClose"
      @cancel="handleModalClose"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useRouteStore } from '../stores/route';
import CustomModal from '../components/CustomModal.vue';

const route = useRoute();
const router = useRouter();
const routeStore = useRouteStore();

const stop = ref(null);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const warning = ref('');

// Modal state
const showModal = ref(false);
const modalMessage = ref('');
const afterModalRedirect = ref(false);

// Form fields
const status = ref('completed');
const poundsCollected = ref(null);
const skipReason = ref('');
const notes = ref('');

const poundsInput = ref(null);

const goBack = () => {
  router.push({ name: 'stop-detail', params: { id: route.params.id } });
};

const loadStopDetails = async () => {
  loading.value = true;
  error.value = '';
  try {
    stop.value = await routeStore.fetchStopDetail(route.params.id);
    
    // Prefill form if it was already logged
    if (stop.value.status !== 'pending' && stop.value.pickup_event) {
      status.value = stop.value.status;
      notes.value = stop.value.pickup_event.notes || '';
      
      if (stop.value.status === 'completed') {
        poundsCollected.value = stop.value.pickup_event.pounds_collected;
      } else {
        skipReason.value = stop.value.pickup_event.skip_reason || '';
      }
    }
  } catch (err) {
    error.value = 'Failed to load stop details.';
  } finally {
    loading.value = false;
    if (status.value === 'completed') {
      nextTick(() => poundsInput.value?.focus());
    }
  }
};

const handleSubmit = async () => {
  submitting.value = true;
  error.value = '';
  warning.value = '';

  const payload = {
    status: status.value,
    notes: notes.value,
  };

  if (status.value === 'completed') {
    payload.pounds_collected = poundsCollected.value;
  } else {
    payload.skip_reason = skipReason.value;
  }

  const result = await routeStore.logStopPickup(route.params.id, payload);
  submitting.value = false;

  if (result.success) {
    if (result.warning) {
      warning.value = result.warning;
      modalMessage.value = result.warning;
      showModal.value = true;
      afterModalRedirect.value = true;
    } else {
      router.push({ name: 'route' });
    }
  } else {
    error.value = result.message;
  }
};

const handleModalClose = () => {
  showModal.value = false;
  if (afterModalRedirect.value) {
    router.push({ name: 'route' });
  }
};

onMounted(() => {
  loadStopDetails();
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

.header-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-secondary);
}

.content-area {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.stop-summary-banner h3 {
  font-size: 18px;
  font-weight: 700;
}

.stop-summary-banner p {
  font-size: 13px;
  color: var(--text-secondary);
  margin-top: 2px;
}

.status-selector {
  display: flex;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border-glass);
  border-radius: var(--border-radius-md);
  padding: 4px;
  gap: 4px;
}

.status-btn {
  flex: 1;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  padding: 12px;
  font-family: var(--font-sans);
  font-size: 15px;
  font-weight: 600;
  border-radius: var(--border-radius-sm);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.status-btn.active {
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-completed.active {
  background: var(--status-completed);
}

.btn-skipped.active {
  background: var(--status-skipped);
}

.input-addon-wrapper {
  display: flex;
  position: relative;
  align-items: center;
}

.text-large {
  font-size: 24px;
  padding: 12px 64px 12px 16px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.input-addon {
  position: absolute;
  right: 16px;
  font-size: 18px;
  font-weight: 600;
  color: var(--text-secondary);
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 16px;
}

.form-textarea {
  resize: none;
}

.warning-banner {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  color: #fde047;
  padding: 14px;
  border-radius: var(--border-radius-md);
  font-size: 14px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  line-height: 1.4;
}

.warning-icon {
  font-size: 20px;
}

.error-banner {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  padding: 12px;
  border-radius: var(--border-radius-md);
  font-size: 14px;
  text-align: center;
}

.btn-submit {
  margin-top: 10px;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.fade-in {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes spin {
  to { transform: rotate(360deg); }
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
</style>
