<template>
  <q-chip
    v-if="hasEventType"
    :class="['event-type-badge', `event-type-badge--${badgeCode}`]"
    :title="badgeLabel"
    :size="size"
    dense
  >
    {{ badgeLabel }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  eventType: {
    type: [String, Object],
    default: null
  },
  size: {
    type: String,
    default: 'sm'
  }
})

const hasEventType = computed(() => !!props.eventType)

const badgeCode = computed(() => {
  if (!props.eventType) {
    return 'default'
  }

  if (typeof props.eventType === 'string') {
    return props.eventType ? props.eventType.toLowerCase() : 'default'
  }

  return props.eventType.code
    ? String(props.eventType.code).toLowerCase()
    : 'default'
})

const badgeLabel = computed(() => {
  if (!props.eventType) {
    return ''
  }

  if (typeof props.eventType === 'string') {
    return props.eventType
  }

  return props.eventType.name || props.eventType.code || ''
})
</script>

