<template>
  <q-chip :class="['school-shift--' + (shift.code || 'default')]" :title="shift.name" :size="size">
    {{ label }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'
import { schoolShiftOptions } from '@/Composables/schoolShiftOptions'

const props = defineProps({
  shift: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
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