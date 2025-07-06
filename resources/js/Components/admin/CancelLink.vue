<template>
  <Link
    :href="href"
    class="nav-cancel"
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