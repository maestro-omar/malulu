<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', 'bg-' + basecolor + '-100 text-' + basecolor + '-800']"
    :title="mtype.name">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { schoolManagementTypeOptions } from '@/Composables/schoolManagementTypeOptions'

const props = defineProps({
  mtype: {
    type: Object,
    required: true
  }
});

const { options } = schoolManagementTypeOptions()

const basecolor = computed(() => options.value[props.mtype.code]?.color ?? 'gray')
const label = computed(() => options.value[props.mtype.code]?.label ?? props.mtype.name)
</script>