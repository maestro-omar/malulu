<template>
  <nav aria-label="breadcrumb" class="text-sm text-gray-600 mb-0">
    <ol class="list-reset flex">
      <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
        <template v-if="crumb.url && index !== breadcrumbs.length - 1">
          <Link :href="crumb.url" class="text-blue-600 hover:underline">
          {{ crumb.title }}
          </Link>
        </template>
        <template v-else>
          <span class="text-gray-500">{{ crumb.title }}</span>
        </template>

        <span v-if="index !== breadcrumbs.length - 1" class="mx-2">/</span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  breadcrumbs: {
    type: Array,
    required: true,
  },
})
</script>