<template>
  <q-chip v-if="shift" :class="chipClasses" :title="shift.name" :size="size" :clickable="clickable"
    @click="handleClick">
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
  },
  clickable: {
    type: Boolean,
    default: false
  },
  selected: {
    type: Boolean,
    default: false
  },
  unselected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click']);

const label = computed(() => {
  // If shift.name exists and has a value, use it directly
  if (props.shift.name && props.shift.name.trim()) {
    return props.shift.name
  }

  // Only use the composable as fallback when name is not available
  const { options } = schoolShiftOptions()
  return options.value[props.shift.code]?.label ?? props.shift.name
})

const chipClasses = computed(() => {
  const shiftCode = props.shift.code || 'default';
  const baseClasses = ['school-shift'];

  if (props.clickable) {
    baseClasses.push('cursor-pointer', 'transition-all', 'duration-200');

    if (props.selected) {
      // Use selected variant with shadow
      baseClasses.push(`school-shift--${shiftCode} school-shift--selected`);
    } else if (props.unselected) {
      // Use light variant for unselected state
      baseClasses.push(`school-shift--${shiftCode}-light`);
    } else {
      // Default clickable state - use base shift colors
      baseClasses.push(`school-shift--${shiftCode}`);
    }
  } else {
    // Non-clickable - use base shift colors
    baseClasses.push(`school-shift--${shiftCode}`);
  }

  return baseClasses.join(' ');
});

const handleClick = () => {
  if (props.clickable) {
    emit('click');
  }
};
</script>