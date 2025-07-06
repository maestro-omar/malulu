<template>
  <nav aria-label="breadcrumb" class="nav-breadcrumbs">
    <ol class="nav-breadcrumbs__list">
      <li v-for="(crumb, index) in breadcrumbs" :key="index" class="nav-breadcrumbs__item">
        <template v-if="crumb.url && index !== breadcrumbs.length - 1">
          <Link :href="crumb.url" class="nav-breadcrumbs__link">
          {{ crumb.title }}
          </Link>
        </template>
        <template v-else>
          <span class="nav-breadcrumbs__text">{{ crumb.title }}</span>
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