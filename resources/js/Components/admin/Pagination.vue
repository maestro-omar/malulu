<template>
  <div v-if="links.length > 3">
    <div class="pagination__container">
      <template v-for="(link, key) in links" :key="key">
        <!-- Previous Link -->
        <div v-if="key === 0 && link.url === null" 
             class="pagination__item pagination__item--disabled"
             v-html="link.label" />
        <Link v-else-if="key === 0"
              class="pagination__item"
              :href="link.url"
              v-html="link.label" />
        
        <!-- Page Numbers -->
        <div v-else-if="link.url === null && key !== links.length - 1" 
             class="pagination__item pagination__item--disabled"
             v-html="link.label" />
        <Link v-else-if="key !== links.length - 1"
              class="pagination__item"
              :class="{ 'pagination__item--active': link.active }"
              :href="link.url"
              v-html="link.label" />
        
        <!-- Next Link -->
        <div v-if="key === links.length - 1 && link.url === null" 
             class="pagination__item pagination__item--disabled"
             v-html="link.label" />
        <Link v-else-if="key === links.length - 1"
              class="pagination__item"
              :href="link.url"
              v-html="link.label" />
      </template>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});
</script> 