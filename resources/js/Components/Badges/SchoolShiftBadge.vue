<template>
  <span :class="['badge', 'badge--school-shift', 'school-shift--' + (shift.code || 'default')]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { schoolShiftOptions } from '@/Composables/schoolShiftOptions'

const props = defineProps({
  shift: {
    type: Object,
    required: true
  }
});

const label = computed(() => {
  // If shift.name exists and has a value, use it directly
  if (props.shift.name && props.shift.name.trim()) {
    return props.shift.name
  }
  
  // Only use the composable as fallback when name is not available
  const { options } = schoolShiftOptions()
  return options.value[props.shift.code]?.label ?? props.shift.name
})
</script>