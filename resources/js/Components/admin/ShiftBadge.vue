<template>
  <span v-if="shift.includes('Mañana')" class="shift-badge shift-badge--manana">Mañana</span>
  <span v-else-if="shift.includes('Tarde')" class="shift-badge shift-badge--tarde">Tarde</span>
  <span v-else-if="shift.includes('Noche')" class="shift-badge shift-badge--noche">Noche</span>
  <span v-else class="shift-badge shift-badge--default">{{ shift }}</span>
</template>

<script setup>
defineProps({
  shift: {
    type: String,
    required: true
  }
});
</script> 