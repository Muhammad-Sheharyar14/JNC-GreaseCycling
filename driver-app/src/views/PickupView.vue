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
        <div v-if="status === 'completed'" class="form-section fade-in">
          <!-- Material Selection -->
          <div class="form-group">
            <label class="form-label" for="material_type">Material / Product Type</label>
            <select id="material_type" v-model="materialType" class="form-input form-select">
              <option value="used_cooking_oil">Used Cooking Oil (UCO)</option>
              <option value="grease">Yellow / Brown Grease</option>
              <option value="tallow">Tallow</option>
              <option value="other">Other Feedstocks</option>
            </select>
          </div>

          <!-- Weight Calculator Section -->
          <div class="weight-card">
            <h4 class="weight-card-title">⚖️ Weight & Volume Calculation</h4>
            
            <div class="grid-2col">
              <div class="form-group">
                <label class="form-label-sm" for="gross_weight">Gross Weight (lbs)</label>
                <input
                  id="gross_weight"
                  v-model.number="grossWeight"
                  @input="calculateNet"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input text-medium"
                  placeholder="0.00"
                  inputmode="decimal"
                />
              </div>

              <div class="form-group">
                <label class="form-label-sm" for="tare_weight">Tare Weight (lbs)</label>
                <input
                  id="tare_weight"
                  v-model.number="tareWeight"
                  @input="calculateNet"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-input text-medium"
                  placeholder="0.00"
                  inputmode="decimal"
                />
              </div>
            </div>

            <!-- Net Weight / Pounds Collected -->
            <div class="form-group margin-top-sm">
              <label class="form-label" for="pounds">Net Quantity Collected</label>
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
              <p v-if="grossWeight && tareWeight" class="calc-hint">
                Auto-calculated: {{ grossWeight }} - {{ tareWeight }} = {{ poundsCollected }} lbs
              </p>
            </div>
          </div>

          <!-- Digital Signature Pads Section -->
          <div class="signatures-section">
            <h4 class="section-subtitle">✍️ Digital Signatures</h4>
            
            <!-- Driver Signature -->
            <div class="signature-block">
              <div class="sig-header">
                <label class="form-label-sm">Driver Signature</label>
                <button type="button" @click="clearDriverSignature" class="btn-clear-sig">Clear</button>
              </div>
              <div class="canvas-wrapper">
                <canvas
                  ref="driverSigCanvas"
                  class="sig-canvas"
                  @mousedown="startDrawing($event, 'driver')"
                  @mousemove="draw($event, 'driver')"
                  @mouseup="stopDrawing('driver')"
                  @mouseleave="stopDrawing('driver')"
                  @touchstart.prevent="startDrawingTouch($event, 'driver')"
                  @touchmove.prevent="drawTouch($event, 'driver')"
                  @touchend="stopDrawing('driver')"
                ></canvas>
              </div>
            </div>

            <!-- Customer Signature -->
            <div class="signature-block margin-top-md">
              <div class="sig-header">
                <label class="form-label-sm">Customer Representative Signature (Optional)</label>
                <button type="button" @click="clearCustomerSignature" class="btn-clear-sig">Clear</button>
              </div>
              <div class="canvas-wrapper">
                <canvas
                  ref="customerSigCanvas"
                  class="sig-canvas"
                  @mousedown="startDrawing($event, 'customer')"
                  @mousemove="draw($event, 'customer')"
                  @mouseup="stopDrawing('customer')"
                  @mouseleave="stopDrawing('customer')"
                  @touchstart.prevent="startDrawingTouch($event, 'customer')"
                  @touchmove.prevent="drawTouch($event, 'customer')"
                  @touchend="stopDrawing('customer')"
                ></canvas>
              </div>
            </div>
          </div>

          <!-- Photos Capture Section -->
          <div class="photos-section">
            <h4 class="section-subtitle">📸 Site Photos & Proof</h4>
            <div class="photo-uploader">
              <input
                type="file"
                id="photoInput"
                accept="image/*"
                capture="environment"
                multiple
                class="hidden-file-input"
                @change="handlePhotoUpload"
              />
              <label for="photoInput" class="btn-upload-photos">
                <span>📷</span>
                <span>Take or Attach Photos</span>
              </label>
            </div>
            
            <!-- Photo Previews Grid -->
            <div v-if="photos.length > 0" class="photo-preview-grid">
              <div v-for="(img, idx) in photos" :key="idx" class="photo-card">
                <img :src="img" alt="Pickup Photo" />
                <button type="button" @click="removePhoto(idx)" class="btn-remove-photo">✕</button>
              </div>
            </div>
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
const materialType = ref('used_cooking_oil');
const grossWeight = ref(null);
const tareWeight = ref(null);
const poundsCollected = ref(null);
const skipReason = ref('');
const notes = ref('');
const photos = ref([]);

const poundsInput = ref(null);

// Canvas Signatures refs
const driverSigCanvas = ref(null);
const customerSigCanvas = ref(null);
const isDrawingDriver = ref(false);
const isDrawingCustomer = ref(false);

const calculateNet = () => {
  if (grossWeight.value !== null && tareWeight.value !== null) {
    const net = grossWeight.value - tareWeight.value;
    poundsCollected.value = net >= 0 ? parseFloat(net.toFixed(2)) : 0;
  }
};

const goBack = () => {
  router.push({ name: 'stop-detail', params: { id: route.params.id } });
};

// Canvas drawing functions
const getCanvasCoords = (e, canvas) => {
  const rect = canvas.getBoundingClientRect();
  return {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top,
  };
};

const getTouchCoords = (e, canvas) => {
  const rect = canvas.getBoundingClientRect();
  const touch = e.touches[0];
  return {
    x: touch.clientX - rect.left,
    y: touch.clientY - rect.top,
  };
};

const startDrawing = (e, type) => {
  const canvas = type === 'driver' ? driverSigCanvas.value : customerSigCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const coords = getCanvasCoords(e, canvas);
  ctx.beginPath();
  ctx.moveTo(coords.x, coords.y);
  if (type === 'driver') isDrawingDriver.value = true;
  else isDrawingCustomer.value = true;
};

const draw = (e, type) => {
  const isDrawing = type === 'driver' ? isDrawingDriver.value : isDrawingCustomer.value;
  if (!isDrawing) return;
  const canvas = type === 'driver' ? driverSigCanvas.value : customerSigCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const coords = getCanvasCoords(e, canvas);
  ctx.lineWidth = 2.5;
  ctx.lineCap = 'round';
  ctx.strokeStyle = '#38bdf8';
  ctx.lineTo(coords.x, coords.y);
  ctx.stroke();
};

const startDrawingTouch = (e, type) => {
  const canvas = type === 'driver' ? driverSigCanvas.value : customerSigCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const coords = getTouchCoords(e, canvas);
  ctx.beginPath();
  ctx.moveTo(coords.x, coords.y);
  if (type === 'driver') isDrawingDriver.value = true;
  else isDrawingCustomer.value = true;
};

const drawTouch = (e, type) => {
  const isDrawing = type === 'driver' ? isDrawingDriver.value : isDrawingCustomer.value;
  if (!isDrawing) return;
  const canvas = type === 'driver' ? driverSigCanvas.value : customerSigCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const coords = getTouchCoords(e, canvas);
  ctx.lineWidth = 2.5;
  ctx.lineCap = 'round';
  ctx.strokeStyle = '#38bdf8';
  ctx.lineTo(coords.x, coords.y);
  ctx.stroke();
};

const stopDrawing = (type) => {
  if (type === 'driver') isDrawingDriver.value = false;
  else isDrawingCustomer.value = false;
};

const clearDriverSignature = () => {
  const canvas = driverSigCanvas.value;
  if (canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
};

const clearCustomerSignature = () => {
  const canvas = customerSigCanvas.value;
  if (canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
};

const isCanvasBlank = (canvas) => {
  if (!canvas) return true;
  const ctx = canvas.getContext('2d');
  const pixelBuffer = new Uint32Array(
    ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
  );
  return !pixelBuffer.some(color => color !== 0);
};

const handlePhotoUpload = (e) => {
  const files = e.target.files;
  if (!files || files.length === 0) return;
  
  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const reader = new FileReader();
    reader.onload = (event) => {
      photos.value.push(event.target.result);
    };
    reader.readAsDataURL(file);
  }
};

const removePhoto = (idx) => {
  photos.value.splice(idx, 1);
};

const initCanvases = () => {
  nextTick(() => {
    [driverSigCanvas.value, customerSigCanvas.value].forEach((c) => {
      if (c) {
        c.width = c.offsetWidth || 300;
        c.height = 120;
      }
    });
  });
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
        materialType.value = stop.value.pickup_event.material_type || 'used_cooking_oil';
        grossWeight.value = stop.value.pickup_event.gross_weight;
        tareWeight.value = stop.value.pickup_event.tare_weight;
        if (stop.value.pickup_event.photos) {
          photos.value = stop.value.pickup_event.photos;
        }
      } else {
        skipReason.value = stop.value.pickup_event.skip_reason || '';
      }
    }
  } catch (err) {
    error.value = 'Failed to load stop details.';
  } finally {
    loading.value = false;
    initCanvases();
    if (status.value === 'completed') {
      nextTick(() => poundsInput.value?.focus());
    }
  }
};

const handleSubmit = async () => {
  submitting.value = true;
  error.value = '';
  warning.value = '';

  let driverSigBase64 = null;
  let customerSigBase64 = null;

  if (status.value === 'completed') {
    if (driverSigCanvas.value && !isCanvasBlank(driverSigCanvas.value)) {
      driverSigBase64 = driverSigCanvas.value.toDataURL('image/png');
    }
    if (customerSigCanvas.value && !isCanvasBlank(customerSigCanvas.value)) {
      customerSigBase64 = customerSigCanvas.value.toDataURL('image/png');
    }
  }

  const payload = {
    status: status.value,
    notes: notes.value,
  };

  if (status.value === 'completed') {
    payload.pounds_collected = poundsCollected.value;
    payload.material_type = materialType.value;
    payload.gross_weight = grossWeight.value;
    payload.tare_weight = tareWeight.value;
    payload.net_weight = poundsCollected.value;
    payload.driver_signature = driverSigBase64;
    payload.customer_signature = customerSigBase64;
    payload.photos = photos.value;
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
      if (routeStore.routeId) {
        router.push({ name: 'route', params: { id: routeStore.routeId } });
      } else {
        router.push({ name: 'routes' });
      }
    }
  } else {
    error.value = result.message;
  }
};

const handleModalClose = () => {
  showModal.value = false;
  if (afterModalRedirect.value) {
    if (routeStore.routeId) {
      router.push({ name: 'route', params: { id: routeStore.routeId } });
    } else {
      router.push({ name: 'routes' });
    }
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

.weight-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-glass);
  border-radius: var(--border-radius-md);
  padding: 16px;
  margin-top: 12px;
}

.weight-card-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--accent-color);
  margin-bottom: 12px;
}

.grid-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-label-sm {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 4px;
  display: block;
}

.text-medium {
  font-size: 16px;
  padding: 10px 12px;
}

.margin-top-sm {
  margin-top: 12px;
}

.margin-top-md {
  margin-top: 16px;
}

.calc-hint {
  font-size: 11px;
  color: #10b981;
  margin-top: 4px;
  font-weight: 500;
}

.signatures-section, .photos-section {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-glass);
  border-radius: var(--border-radius-md);
  padding: 16px;
  margin-top: 16px;
}

.section-subtitle {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.sig-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.btn-clear-sig {
  background: transparent;
  border: none;
  color: #ef4444;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.canvas-wrapper {
  background: #0f172a;
  border: 1px dashed var(--border-glass);
  border-radius: var(--border-radius-sm);
  touch-action: none;
}

.sig-canvas {
  width: 100%;
  height: 120px;
  display: block;
  cursor: crosshair;
}

.hidden-file-input {
  display: none;
}

.btn-upload-photos {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px dashed var(--accent-color);
  border-radius: var(--border-radius-md);
  color: var(--accent-color);
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-upload-photos:hover {
  background: rgba(255, 255, 255, 0.1);
}

.photo-preview-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 12px;
}

.photo-card {
  position: relative;
  aspect-ratio: 1;
  border-radius: var(--border-radius-sm);
  overflow: hidden;
  border: 1px solid var(--border-glass);
}

.photo-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.btn-remove-photo {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  font-size: 12px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
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

.form-select option {
  background-color: #1e293b !important;
  color: #f8fafc !important;
  padding: 10px;
}

.form-select option:disabled {
  color: #9ca3af !important;
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
