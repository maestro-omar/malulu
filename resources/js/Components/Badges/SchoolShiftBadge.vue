<template>
  <span :class="['school-shift-badge', 'school-shift-badge--' + (shift.code || 'default')]"><!--
    -->{{ label }}
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

const { options } = schoolShiftOptions()

const label = computed(() => options.value[props.shift.code]?.label ?? props.shift.name)
</script>