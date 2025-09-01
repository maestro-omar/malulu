<template>
  <!-- This component now uses Quasar notifications instead of HTML template -->
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  flash: {
    type: Object,
    required: true,
  },
});

const showSuccess = ref(true);
const showError = ref(true);

const showNotification = (message, type) => {
  $q.notify({
    message: message,
    type: type,
    position: 'top',
    timeout: 5000,
    multiLine: true,
    group: false,
    actions: [
      {
        label: 'Cerrar',
        color: 'white',
        handler: () => {
          // Notification will be dismissed
        }
      }
    ]
  });
};

const dismissSuccess = () => {
  showSuccess.value = false;
};

const dismissError = () => {
  showError.value = false;
};

// Watch for flash messages and show notifications
watch(() => props.flash, (newFlash) => {
  if (newFlash?.success && showSuccess.value) {
    showNotification(newFlash.success, 'positive');
  }

  if (newFlash?.error && showError.value) {
    showNotification(newFlash.error, 'negative');
  }
}, { immediate: true, deep: true });


</script>