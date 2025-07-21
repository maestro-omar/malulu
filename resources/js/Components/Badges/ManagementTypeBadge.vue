<template>
  <span :class="['management-type-badge', 'management-type-badge--' + (mtype.code || 'default')]"
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

const label = computed(() => options.value[props.mtype.code]?.label ?? props.mtype.name)
</script>