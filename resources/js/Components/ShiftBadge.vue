<template>
  <span 
    v-if="shift.includes('Mañana')"
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
  >
    Mañana
  </span>
  <span 
    v-if="shift.includes('Tarde')"
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
  >
    Tarde
  </span>
  <span 
    v-if="shift.includes('Noche')"
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
  >
    Noche
  </span>
</template>

<script setup>
defineProps({
  shift: {
    type: String,
    required: true
  }
});
</script> 