<template>
    <div class="course-popover" ref="popoverRef">
        <!-- Trigger Button -->
        <div class="course-popover__trigger" @click="togglePopover">
            <div class="course-popover__trigger-content">
                <span v-if="selectedCourse" class="course-popover__selected">
                    {{ selectedCourse.nice_name }}
                </span>
                <span v-else class="course-popover__placeholder">
                    Seleccionar curso anterior
                </span>
            </div>
            <svg class="course-popover__trigger-icon" :class="{ 'course-popover__trigger-icon--open': isOpen }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <!-- Popover Content -->
        <div v-if="isOpen" class="course-popover__overlay" @click="closePopover"></div>
        <div v-if="isOpen" class="course-popover__content">
            <!-- Header -->
            <div class="course-popover__header">
                <h3 class="course-popover__title">Seleccionar Curso Anterior</h3>
                <button @click="closePopover" class="course-popover__close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Filters -->
            <div class="course-popover__filters">
                <div class="course-popover__filters-grid">
                    <div class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Año</label>
                        <input type="number" v-model.number="filters.year" @input="validateAndLoadCourses"
                            :max="maxYear" class="course-popover__filter-input" :placeholder="`Ej: ${maxYear}`" />
                        <small v-if="yearError" class="course-popover__error">
                            El año no puede ser mayor a {{ maxYear }}
                        </small>
                    </div>

                    <div class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Estado</label>
                        <div class="course-popover__filter-radio-group">
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="true" v-model="filters.active"
                                    @change="validateAndLoadCourses">
                                <span>Activo</span>
                            </label>
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="false" v-model="filters.active"
                                    @change="validateAndLoadCourses">
                                <span>Inactivo</span>
                            </label>
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="null" v-model="filters.active"
                                    @change="validateAndLoadCourses">
                                <span>Todos</span>
                            </label>
                        </div>
                    </div>

                    <div class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Turno</label>
                        <SelectSchoolShift v-model="filters.schoolShift" :options="props.schoolShifts"
                            @update:modelValue="validateAndLoadCourses" :showAllOption="true" :hideLabel="true" />
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="course-popover__search">
                <input type="text" v-model="search" @input="filterCourses" placeholder="Buscar cursos..."
                    class="course-popover__search-input" />
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="course-popover__loading">
                <div class="course-popover__loading-spinner"></div>
                <span>Cargando cursos...</span>
            </div>

            <!-- Course List -->
            <div v-else-if="filteredCourses.length > 0" class="course-popover__list">
                <div v-for="course in filteredCourses" :key="course.id" class="course-popover__item"
                    :class="{ 'course-popover__item--selected': selectedCourse?.id === course.id }">
                    <div class="course-popover__item-content">
                        <div class="course-popover__item-main" @click="selectCourse(course)">
                            <h4 class="course-popover__item-title">{{ course.nice_name }}</h4>
                            <div class="course-popover__item-details">
                                <span class="course-popover__item-shift">
                                    <SchoolShiftBadge :shift="course.school_shift" />
                                </span>
                                <span class="course-popover__item-date">{{ formatDate(course.start_date) }}</span>
                            </div>
                        </div>
                        <div class="course-popover__item-actions">
                            <div class="course-popover__item-status">
                                <span :class="{
                                    'course-popover__status': true,
                                    'course-popover__status--active': course.active,
                                    'course-popover__status--inactive': !course.active,
                                }">
                                    {{ course.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <button @click="selectCourse(course)" class="course-popover__select-btn">
                                Seleccionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="course-popover__empty">
                <span>No se encontraron cursos</span>
            </div>

            <!-- Pagination -->
            <div v-if="courses.links && courses.links.length > 3" class="course-popover__pagination">
                <Pagination :links="courses.links" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import Pagination from '@/Components/admin/Pagination.vue'
import { formatDate } from '@/utils/date'

const props = defineProps({
    modelValue: {
        type: [Number, null],
        default: null
    },
    school: {
        type: Object,
        required: true
    },
    schoolLevel: {
        type: Object,
        required: true
    },
    schoolShift: {
        type: [Number, null],
        default: null
    },
    schoolShifts: {
        type: Array,
        default: () => []
    },
    startDate: {
        type: [String, null],
        default: null
    }
})

const emit = defineEmits(['update:modelValue'])

// Refs
const popoverRef = ref(null)
const isOpen = ref(false)
const loading = ref(false)
const search = ref('')
const selectedCourse = ref(null)
const courses = ref([])
const yearError = ref(false)

// Filters
const filters = ref({
    year: props.startDate ? new Date(props.startDate).getFullYear() : new Date().getFullYear(),
    active: null,
    schoolShift: props.schoolShift
})

// Computed
const maxYear = computed(() => {
    return props.startDate ? new Date(props.startDate).getFullYear() : new Date().getFullYear()
})

const filteredCourses = computed(() => {
    if (!search.value) {
        return courses.value
    }
    return courses.value.filter(course =>
        course.nice_name.toLowerCase().includes(search.value.toLowerCase())
    )
})

// Methods
const togglePopover = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        loadCourses()
    }
}

const closePopover = () => {
    isOpen.value = false
}

const validateAndLoadCourses = () => {
    // Validate year
    if (filters.value.year > maxYear.value) {
        yearError.value = true
        filters.value.year = maxYear.value
        return
    }

    yearError.value = false
    loadCourses()
}

const loadCourses = async () => {
    loading.value = true
    try {
        // Use Inertia router for automatic CSRF handling
        await router.post(route('school.courses.search', {
            school: props.school.slug,
            schoolLevel: props.schoolLevel.code
        }), {
            school_id: props.school.id,
            school_level_id: props.schoolLevel.id,
            school_shift_id: filters.value.schoolShift,
            start_date: props.startDate,
            year: filters.value.year,
            active: filters.value.active
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['courses'],
            onSuccess: (page) => {
                // Handle the response data from Inertia page props
                if (page.props.courses) {
                    courses.value = page.props.courses
                } else {
                    courses.value = [];
                }
            },
            onError: (errors) => {
                console.error('Error loading courses:', errors);
            }
        })
    } catch (error) {
        console.error('Error loading courses:', error)
        // Fallback to mock data for testing
        courses.value = [
            {
                id: 1,
                nice_name: 'ERROR 1° A - 2024',
                school_shift: { id: 1, name: 'Mañana' },
                start_date: '2024-03-01',
                active: true
            },
            {
                id: 2,
                nice_name: '1° B - 2024',
                school_shift: { id: 1, name: 'Mañana' },
                start_date: '2024-03-01',
                active: true
            }
        ]
    } finally {
        loading.value = false
    }
}

const filterCourses = () => {
    // Local filtering is handled by computed property
}

const selectCourse = (course) => {
    selectedCourse.value = course
    emit('update:modelValue', course.id)
    closePopover()
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue && courses.value.length > 0) {
        selectedCourse.value = courses.value.find(course => course.id === newValue)
    } else {
        selectedCourse.value = null
    }
}, { immediate: true })

// Watch for schoolShift changes
watch(() => props.schoolShift, (newValue) => {
    filters.value.schoolShift = newValue
    if (isOpen.value) {
        loadCourses()
    }
})

// Watch for startDate changes
watch(() => props.startDate, (newValue) => {
    if (newValue) {
        const newMaxYear = new Date(newValue).getFullYear()
        if (filters.value.year > newMaxYear) {
            filters.value.year = newMaxYear
            yearError.value = false
        }
    }
})

// Click outside to close
const handleClickOutside = (event) => {
    if (popoverRef.value && !popoverRef.value.contains(event.target)) {
        closePopover()
    }
}

// Keyboard support
const handleKeydown = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
        closePopover()
    }
}

// Lifecycle
onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside)
    document.removeEventListener('keydown', handleKeydown)
})
</script>
