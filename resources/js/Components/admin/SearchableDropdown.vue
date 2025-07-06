<template>
  <div class="dropdown">
    <div class="dropdown__search">
      <input
        type="text"
        :value="search"
        @input="updateSearch"
        @focus="showOptions = true"
        :placeholder="placeholder"
        class="dropdown__search-input"
      />
      <div v-if="search" class="dropdown__search-clear">
        <button
          @click="clearSearch"
          class="dropdown__search-clear-button"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Dropdown Options -->
    <div
      v-if="showOptions && filteredOptions.length > 0"
      class="dropdown__menu dropdown__menu--search"
    >
      <div
        v-for="option in filteredOptions"
        :key="option.id"
        @click="selectOption(option)"
        class="dropdown__link"
      >
        {{ option.long || option.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Object, Number, null],
    default: null
  },
  options: {
    type: Array,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Search...'
  },
  initialValue: {
    type: [Object, Number, null],
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const showOptions = ref(false);

// Initialize search with initial value
onMounted(() => {
  if (props.initialValue) {
    const value = typeof props.initialValue === 'object' ? props.initialValue : props.options.find(opt => opt.id === props.initialValue);
    if (value) {
      search.value = value.long || value.name;
      emit('update:modelValue', value);
    }
  }
});

const filteredOptions = computed(() => {
  if (!search.value) return props.options;
  return props.options.filter(option =>
    (option.long || option.name).toLowerCase().includes(search.value.toLowerCase())
  );
});

const updateSearch = (event) => {
  search.value = event.target.value;
};

const selectOption = (option) => {
  emit('update:modelValue', option);
  search.value = option.long || option.name;
  showOptions.value = false;
};

const clearSearch = () => {
  search.value = '';
  emit('update:modelValue', null);
};

// Update search when modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const value = typeof newValue === 'object' ? newValue : props.options.find(opt => opt.id === newValue);
    if (value) {
      search.value = value.long || value.name;
    }
  } else {
    search.value = '';
  }
}, { immediate: true });

// Also watch initialValue for changes
watch(() => props.initialValue, (newValue) => {
  if (newValue) {
    const value = typeof newValue === 'object' ? newValue : props.options.find(opt => opt.id === newValue);
    if (value) {
      search.value = value.long || value.name;
      emit('update:modelValue', value);
    }
  }
}, { immediate: true });

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showOptions.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script> 