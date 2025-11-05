<template>
  <q-chip v-if="category" :class="['diagnosis-category', `diagnosis-category--${categoryCode}`]" :title="categoryName" :size="size">
    {{ categoryName }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  category: {
    type: [String, Object],
    required: true
  },
  categoryName: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
  }
});

const categoryCode = computed(() => {
  if (typeof props.category === 'string') {
    return props.category || 'default';
  }
  return props.category?.category || props.category?.code || 'default';
});
</script>

