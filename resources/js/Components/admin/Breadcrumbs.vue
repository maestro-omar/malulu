<template>
  <nav aria-label="breadcrumb" class="admin-nav-breadcrumbs">
    <ol class="admin-nav-breadcrumbs__list">
      <li v-for="(crumb, index) in breadcrumbs" :key="index" class="admin-nav-breadcrumbs__item">
        <template v-if="crumb.url && index !== breadcrumbs.length - 1">
          <Link :href="crumb.url" class="admin-nav-breadcrumbs__link">
          {{ crumb.title }}
          </Link>
        </template>
        <template v-else>
          <span class="admin-nav-breadcrumbs__text">{{ crumb.title }}</span>
        </template>
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