<template>
  <q-chip v-if="gender" :class="chipClasses" :title="genderLabel" :size="size" :clickable="clickable"
    @click="handleClick">
    {{ genderLabel }}
  </q-chip>
  <span v-else class="text-grey-5">-</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  gender: {
    type: String,
    default: null
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

const genderLabel = computed(() => {
  const genderMap = {
    'masc': 'Masc',
    'fem': 'Fem',
    'trans': 'Trans',
    'fluido': 'Fluido',
    'no-bin': 'No-bin',
    'otro': 'Otro'
  };
  return genderMap[props.gender] || props.gender;
});

const chipClasses = computed(() => {
  const genderCode = (props.gender || 'default').toLowerCase();
  const baseClasses = ['gender-badge'];

  if (props.clickable) {
    baseClasses.push('cursor-pointer', 'transition-all', 'duration-200');

    if (props.selected) {
      // Use selected variant with shadow
      baseClasses.push(`gender-badge--${genderCode} gender-badge--selected`);
    } else if (props.unselected) {
      // Use light variant for unselected state
      baseClasses.push(`gender-badge--${genderCode}-light`);
    } else {
      // Default clickable state - use base gender colors
      baseClasses.push(`gender-badge--${genderCode}`);
    }
  } else {
    // Non-clickable - use base gender colors
    baseClasses.push(`gender-badge--${genderCode}`);
  }

  return baseClasses.join(' ');
});

const handleClick = () => {
  if (props.clickable) {
    emit('click');
  }
};
</script>
