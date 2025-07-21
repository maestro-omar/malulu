<template>
  <span v-if="shift.includes('Mañana')" class="badge badge--shift school-shift--manana">Mañana</span>
  <span v-else-if="shift.includes('Tarde')" class="badge badge--shift school-shift--tarde">Tarde</span>
  <span v-else-if="shift.includes('Noche')" class="badge badge--shift school-shift--noche">Noche</span>
  <span v-else class="badge badge--shift school-shift--default">{{ shift }}</span>
</template>

<script setup>
defineProps({
  shift: {
    type: String,
    required: true
  }
});
</script>