<template>
  <Transition name="modal-fade">
    <div v-if="show" class="modal-overlay" @click.self="handleCancel">
      <div class="modal-card glass-card">
        <!-- Icon slot or default warning icon -->
        <div class="modal-icon-wrapper">
          <slot name="icon">
            <span class="default-icon">{{ isAlert ? 'ℹ️' : '❓' }}</span>
          </slot>
        </div>

        <h3 class="modal-title">{{ title }}</h3>
        <p class="modal-message">{{ message }}</p>

        <div class="modal-actions">
          <button 
            v-if="!isAlert" 
            type="button" 
            class="btn-secondary modal-btn" 
            @click="handleCancel"
          >
            {{ cancelText }}
          </button>
          <button 
            type="button" 
            class="btn-primary modal-btn" 
            :class="{ 'btn-alert-ok': isAlert }"
            @click="handleConfirm"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Are you sure?'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  isAlert: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['confirm', 'cancel']);

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('cancel');
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(10, 14, 23, 0.7);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 1000;
}

.modal-card {
  width: 100%;
  max-width: 400px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 24px;
  background: rgba(20, 26, 38, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  transform: translateY(0);
}

.modal-icon-wrapper {
  font-size: 40px;
  margin-bottom: 16px;
  animation: pulse 2s infinite;
}

.default-icon {
  display: inline-block;
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--text-primary);
}

.modal-message {
  font-size: 14px;
  color: var(--text-secondary);
  line-height: 1.5;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  width: 100%;
  gap: 12px;
}

.modal-btn {
  flex: 1;
  padding: 12px;
  font-size: 15px;
  border-radius: var(--border-radius-md);
}

.btn-alert-ok {
  background: var(--accent-gradient);
}

/* Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-active .modal-card {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-leave-active .modal-card {
  animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideDown {
  from {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}
</style>
