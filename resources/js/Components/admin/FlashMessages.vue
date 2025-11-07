<template>
  <!-- This component now uses Quasar notifications instead of HTML template -->
</template>

<script setup>
import { ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import { usePage } from '@inertiajs/vue3';

const $q = useQuasar();
const $page = usePage();

const props = defineProps({
  flash: {
    type: Object,
    required: true,
  },
});

const activeNotifications = ref(new Set());

const showNotification = (message, type) => {
  if (!message) {
    return;
  }

  const key = `${type}:${message}`;

  if (activeNotifications.value.has(key)) {
    return;
  }

  activeNotifications.value.add(key);

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

  setTimeout(() => {
    activeNotifications.value.delete(key);
  });
};

const extractErrorMessages = (errors) => {
  if (!errors) {
    return [];
  }

  if (typeof errors === 'string') {
    return [errors];
  }

  if (Array.isArray(errors)) {
    return errors
      .flatMap((error) => extractErrorMessages(error))
      .filter(Boolean);
  }

  if (typeof errors === 'object') {
    return Object.values(errors)
      .flatMap((value) => extractErrorMessages(value))
      .filter(Boolean);
  }

  return [];
};

const lastFlash = ref({ success: null, error: null });

// Watch for flash messages and show notifications
watch(
  () => props.flash,
  (newFlash) => {
    const successMessage = newFlash?.success;
    const errorMessage = newFlash?.error;

    if (successMessage && successMessage !== lastFlash.value.success) {
      showNotification(successMessage, 'positive');
    }

    if (errorMessage && errorMessage !== lastFlash.value.error) {
      showNotification(errorMessage, 'negative');
    }

    lastFlash.value = {
      success: successMessage ?? null,
      error: errorMessage ?? null,
    };
  },
  { immediate: true, deep: true }
);

// Watch for page errors (validation errors from controller exceptions)
watch(() => $page.props.errors, (newErrors) => {
  const errorMessages = extractErrorMessages(newErrors);

  errorMessages.forEach((message) => {
    showNotification(message, 'negative');
  });
}, { immediate: true, deep: true });


</script>