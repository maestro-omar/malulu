<template>
  <div class="table__filter-group">
    <label v-if="!hideLabel" class="table__filter-label">{{ label }}</label>
    <div class="table__filter-buttons">
      <template v-for="option in shiftOptions" :key="option.id">
        <q-chip clickable @click="handleShiftSelect(option.id)"
          :class="getShiftChipClasses(option.code, modelValue === option.id)">
          {{ option.name }}
        </q-chip>
      </template>
      <q-chip v-if="showAllOption" clickable @click="handleShiftSelect(null)"
        :class="getShiftChipClasses('default', modelValue === null)">
        Todos
      </q-chip>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { schoolShiftOptions } from '@/Composables/schoolShiftOptions'

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: 'Turno'
  },
  showAllOption: {
    type: Boolean,
    default: false
  },
  hideLabel: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const { options: composableOptions } = schoolShiftOptions()

const shiftOptions = computed(() => {
  // If options prop is provided and not empty, use it
  if (props.options && props.options.length > 0) {
    return props.options
  }

  // Otherwise, use the composable options
  if (composableOptions.value && typeof composableOptions.value === 'object') {
    return Object.entries(composableOptions.value).map(([code, data]) => ({
      id: code,
      name: data.label,
      code: code
    }))
  }

  return []
})

const getShiftChipClasses = (shiftCode, isActive) => {
  // Map shift codes to CSS classes matching _school-shift-badge.scss
  // Valid codes: 'manana', 'tarde', 'noche', 'default'
  // For 'ambos' or unknown codes, use 'default'
  const normalizedCode = (shiftCode && ['manana', 'tarde', 'noche'].includes(shiftCode)) 
    ? shiftCode 
    : 'default'
  
  const baseClasses = 'school-shift'
  const shiftClass = `school-shift--${normalizedCode}`
  const stateClass = isActive ? shiftClass : `${shiftClass}-light`
  return `${baseClasses} ${stateClass}`
}

const handleShiftSelect = (id) => {
  emit('update:modelValue', id)
}
</script>