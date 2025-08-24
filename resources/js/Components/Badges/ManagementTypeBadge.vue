<template>
  <q-chip :class="['management-type--' + (mtype.code || 'default')]" :title="mtype.name" :size="size">
    {{ label }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'
import { schoolManagementTypeOptions } from '@/Composables/schoolManagementTypeOptions'

const props = defineProps({
  mtype: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
  }
});

const label = computed(() => {
  // If mtype.name exists and has a value, use it directly
  if (props.mtype.name && props.mtype.name.trim()) {
    return props.mtype.name
  }
  
  // Only use the composable as fallback when name is not available
  const { options } = schoolManagementTypeOptions()
  return options.value[props.mtype.code]?.label ?? props.mtype.name
})
</script>