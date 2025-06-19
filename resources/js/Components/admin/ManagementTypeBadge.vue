<template>
  <span 
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="{
      'bg-green-100 text-green-800': type === 'Estatal',
      'bg-blue-100 text-blue-800': type === 'Privada',
      'bg-purple-100 text-purple-800': type === 'Generativa',
      'bg-yellow-100 text-yellow-800': type === 'Autogestionada'
    }"
  >
    {{ type }}
  </span>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    required: true
  }
});
</script> 