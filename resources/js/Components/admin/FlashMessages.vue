<script setup>
import { ref } from 'vue';

defineProps({
  flash: {
    type: Object,
    required: true,
  },
});

const showSuccess = ref(true);
const showError = ref(true);

const dismissSuccess = () => {
  showSuccess.value = false;
};

const dismissError = () => {
  showError.value = false;
};
</script>

<template>
  <Transition name="flash-slide" appear>
    <div v-if="flash?.error && showError"
      class="flash-messages__message flash-messages__message--error flash-messages__message--dismissible" role="alert"
      @click="dismissError">
      <span class="flash-messages__text">{{ flash.error }}</span>
      <button class="flash-messages__close" aria-label="Cerrar mensaje">×</button>
    </div>
  </Transition>

  <Transition name="flash-slide" appear>
    <div v-if="flash?.success && showSuccess"
      class="flash-messages__message flash-messages__message--success flash-messages__message--dismissible" role="alert"
      @click="dismissSuccess">
      <span class="flash-messages__text">{{ flash.success }}</span>
      <button class="flash-messages__close" aria-label="Cerrar mensaje">×</button>
    </div>
  </Transition>
</template>