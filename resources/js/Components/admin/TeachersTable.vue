<template>
pendiente
</template>

<script setup>
import { getUserSlug } from '@/Utils/strings';
import { calculateAge, formatDate } from '@/Utils/date';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  teachers: { type: Array, default: () => [] },
});
</script>