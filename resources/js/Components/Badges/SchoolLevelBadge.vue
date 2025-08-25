<template>
  <q-chip v-if="level" :class="['school-level--' + (level.code || 'default')]" :title="level.name" :size="size">
    {{ label }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions'

const props = defineProps({
  level: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
  }
});

const label = computed(() => {
  // If level.name exists and has a value, use it directly
  if (props.level.name && props.level.name.trim()) {
    return props.level.name
  }
  
  // Only use the composable as fallback when name is not available
  const { options } = schoolLevelOptions()
  return options.value[props.level.code]?.label ?? props.level.name
})
</script>