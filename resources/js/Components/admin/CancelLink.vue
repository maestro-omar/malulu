<template>
  <Link
    :href="href"
    :class="class"
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
  },
  class: {
    type: String,
    default: 'nav-cancel'
  }
});
</script>