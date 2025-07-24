<template>
  <div class="admin-dropdown" ref="dropdownRef">
    <div class="admin-dropdown__search">
      <input
        type="text"
        :value="search"
        @input="updateSearch"
        @focus="onInputFocus"
        :placeholder="placeholder"
        class="admin-dropdown__search-input"
      />
      <div v-if="search" class="admin-dropdown__search-clear">
        <button
          @click="clearSearch"
          class="admin-dropdown__search-clear-button"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Dropdown Options -->
    <div
      v-if="filteredOptions.length > 0"
      :class="['admin-dropdown__menu', 'admin-dropdown__menu--search', { 'admin-dropdown__menu--open': showOptions }]"
      @mousedown.stop
    >
      <div
        v-for="option in filteredOptions"
        :key="option.id"
        @click="selectOption(option)"
        class="admin-dropdown__link"
      >
        {{ option.long || option.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const dropdownRef = ref(null);

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

// console.log('[Dropdown] Props received:', props);

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const showOptions = ref(false);

const onInputFocus = () => {
  showOptions.value = true;
  // console.log('[Dropdown] Input focused, showOptions:', showOptions.value);
};

// Initialize search with initial value
onMounted(() => {
  // console.log('[Dropdown] Mounted');
  if (props.initialValue) {
    const value = typeof props.initialValue === 'object' ? props.initialValue : props.options.find(opt => opt.id === props.initialValue);
    if (value) {
      search.value = value.long || value.name;
      emit('update:modelValue', value);
      // console.log('[Dropdown] Initial value set:', value);
    }
  }
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  // console.log('[Dropdown] Unmounted');
  document.removeEventListener('mousedown', handleClickOutside);
});

const filteredOptions = computed(() => {
  let result;
  if (!search.value) {
    result = props.options;
  } else {
    result = props.options.filter(option =>
      (option.long || option.name).toLowerCase().includes(search.value.toLowerCase())
    );
  }
  // console.log('[Dropdown] Filtering options. Search:', search.value, 'Result:', result);
  return result;
});

const updateSearch = (event) => {
  search.value = event.target.value;
  showOptions.value = true;
  // console.log('[Dropdown] Search updated:', search.value, 'showOptions:', showOptions.value);
};

const selectOption = (option) => {
  emit('update:modelValue', option);
  search.value = option.long || option.name;
  showOptions.value = false;
  // console.log('[Dropdown] Option selected:', option, 'showOptions:', showOptions.value);
};

const clearSearch = () => {
  search.value = '';
  emit('update:modelValue', null);
  showOptions.value = false;
  // console.log('[Dropdown] Search cleared, showOptions:', showOptions.value);
};

// Update search when modelValue changes
watch(() => props.modelValue, (newValue) => {
  // console.log('[Dropdown] modelValue changed:', newValue);
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
  // console.log('[Dropdown] initialValue changed:', newValue);
  if (newValue) {
    const value = typeof newValue === 'object' ? newValue : props.options.find(opt => opt.id === newValue);
    if (value) {
      search.value = value.long || value.name;
      emit('update:modelValue', value);
      // console.log('[Dropdown] initialValue set:', value);
    }
  }
}, { immediate: true });

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showOptions.value = false;
    // console.log('[Dropdown] Clicked outside, closing dropdown');
  }
};
</script>