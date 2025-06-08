<template>
  <div class="relative">
    <div class="relative">
      <input
        type="text"
        :value="search"
        @input="updateSearch"
        @focus="showOptions = true"
        :placeholder="placeholder"
        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
      <div v-if="search" class="absolute right-3 top-2.5">
        <button
          @click="clearSearch"
          class="text-gray-400 hover:text-gray-600"
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
      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
    >
      <div
        v-for="option in filteredOptions"
        :key="option.id"
        @click="selectOption(option)"
        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
      >
        {{ option.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Object, null],
    default: null
  },
  options: {
    type: Array,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Search...'
  }
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');
const showOptions = ref(false);

const filteredOptions = computed(() => {
  if (!search.value) return props.options;
  return props.options.filter(option =>
    option.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

const updateSearch = (event) => {
  search.value = event.target.value;
};

const selectOption = (option) => {
  emit('update:modelValue', option);
  search.value = option.name;
  showOptions.value = false;
};

const clearSearch = () => {
  search.value = '';
  emit('update:modelValue', null);
};

// Update search when modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    search.value = newValue.name;
  } else {
    search.value = '';
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