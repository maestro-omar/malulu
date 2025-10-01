<template>
  <span :class="badgeClass" class="subject-badge">
    {{ displayName }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  subject: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const badgeClass = computed(() => {
  const code = props.subject?.code || 'default'
  const sizeClass = `subject-badge--${props.size}`
  return `subject-badge--${code} ${sizeClass}`
})

const displayName = computed(() => {
  return props.subject?.name || props.subject?.short_name || 'N/A'
})
</script>
