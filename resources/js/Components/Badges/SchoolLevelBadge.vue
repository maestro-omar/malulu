<template>
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="'bg-' + basecolor + '-100 text-' + basecolor + '-800'">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions'

const props = defineProps({
  level: {
    type: Object,
    required: true
  }
});

const { options } = schoolLevelOptions()

const basecolor = computed(() => options.value[props.level.code]?.color ?? 'gray')
const label = computed(() => options.value[props.level.code]?.label ?? props.level.name)
</script>