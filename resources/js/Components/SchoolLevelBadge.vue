<template>
  <span 
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="{
      'bg-rose-100 text-rose-800': level.key === 'kinder',
      'bg-amber-100 text-amber-800': level.key === 'primary',
      'bg-violet-100 text-violet-800': level.key === 'secondary'
    }"
  >
    {{ level.name }}
  </span>
</template>

<script setup>
defineProps({
  level: {
    type: Object,
    required: true
  }
});
</script> 