<script setup>

defineProps({
  flash: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div v-if="flash?.error" class="flash-messages__message flash-messages__message--error" role="alert">
    <span class="flash-messages__text">{{ flash.error }}</span>
  </div>
  <div v-if="flash?.success" class="flash-messages__message flash-messages__message--success" role="alert">
    <span class="flash-messages__text">{{ flash.success }}</span>
  </div>
</template>