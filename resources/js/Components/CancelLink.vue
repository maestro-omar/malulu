<template>
  <Link
    :href="href"
    class="text-red-600 hover:text-red-800 font-medium"
  >
    Cancelar
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: true
  }
});
</script>