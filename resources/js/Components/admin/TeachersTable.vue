<template>
pendiente
</template>

<script setup>
import { getUserSlug } from '@/utils/strings';
import { calculateAge, formatDate } from '@/utils/date';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  teachers: { type: Array, default: () => [] },
});
</script>