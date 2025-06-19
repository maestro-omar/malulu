<template>
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="'bg-' + basecolor + '-100 text-' + basecolor + '-800'">
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

const { options } = schoolShiftOptions()

const basecolor = computed(() => options.value[props.shift.code]?.color ?? 'gray')
const label = computed(() => options.value[props.shift.code]?.label ?? props.shift.name)
</script>