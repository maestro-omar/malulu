<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, key) in links" :key="key">
        <!-- Previous Link -->
        <div v-if="key === 0 && link.url === null" 
             class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
             v-html="link.label" />
        <Link v-else-if="key === 0"
              class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
              :href="link.url"
              v-html="link.label" />
        
        <!-- Page Numbers -->
        <div v-else-if="link.url === null && key !== links.length - 1" 
             class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
             v-html="link.label" />
        <Link v-else-if="key !== links.length - 1"
              class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
              :class="{ 'bg-indigo-50': link.active }"
              :href="link.url"
              v-html="link.label" />
        
        <!-- Next Link -->
        <div v-if="key === links.length - 1 && link.url === null" 
             class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
             v-html="link.label" />
        <Link v-else-if="key === links.length - 1"
              class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
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