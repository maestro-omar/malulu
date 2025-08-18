<template>
    <div class="course-popover" ref="popoverRef">
        <!-- Trigger Button -->
        <div class="course-popover__trigger" @click="togglePopover">
            <div class="course-popover__trigger-content">
                <span v-if="selectedCourse" class="course-popover__selected">
                    {{ selectedCourse.nice_name }}
                </span>
                <span v-else class="course-popover__placeholder">
                    Sin curso anterior
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
                <h3 class="course-popover__title">Seleccionar Curso Anterior (opcional)</h3>
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
                        <input type="number" v-model.number="filters.year" @input="triggerFilter" :max="maxYear"
                            class="course-popover__filter-input" :placeholder="`Ej: ${maxYear}`" />
                        <small v-if="yearError" class="course-popover__error">
                            El año no puede ser mayor a {{ maxYear }}
                        </small>
                    </div>

                    <div class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Estado</label>
                        <div class="course-popover__filter-radio-group">
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="true" v-model="filters.active"
                                    @change="triggerFilter">
                                <span>Activo</span>
                            </label>
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="false" v-model="filters.active"
                                    @change="triggerFilter">
                                <span>Inactivo</span>
                            </label>
                            <label class="course-popover__filter-radio">
                                <input type="radio" name="active-status" :value="null" v-model="filters.active"
                                    @change="triggerFilter">
                                <span>Todos</span>
                            </label>
                        </div>
                    </div>

                    <div class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Turno</label>
                        <SelectSchoolShift ref="popoverShiftSelect" v-model="filters.schoolShift"
                            :options="props.schoolShifts" @update:modelValue="triggerFilter" :showAllOption="true"
                            :hideLabel="true" />
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
                <!-- Debug info -->
                <div style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; font-size: 12px;">
                    Debug: {{ filteredCourses.length }} courses found
                </div>
                
                <!-- "Sin curso anterior" option -->
                <div class="course-popover__item course-popover__item--no-course"
                    :class="{ 'course-popover__item--selected': selectedCourse === null }">
                    <div class="course-popover__item-content">
                        <div class="course-popover__item-main" @click="selectNoCourse">
                            <div class="course-popover__item-details">
                                <span class="course-popover__item-description">No asignar curso previo</span>
                            </div>
                        </div>
                        <div class="course-popover__item-actions">
                            <button @click="selectNoCourse" class="course-popover__select-btn course-popover__select-btn--no-course">
                                Sin curso anterior
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course items -->
                <div v-for="course in filteredCourses" :key="course.id" class="course-popover__item"
                    :class="{ 'course-popover__item--selected': selectedCourse?.id === course.id }">
                    <div class="course-popover__item-content">
                        <div class="course-popover__item-main" @click="selectCourse(course)">
                            <div class="course-popover__item-details">
                                <h4 class="course-popover__item-title">{{ course.nice_name }}</h4>
                                <span class="course-popover__item-shift">
                                    <SchoolShiftBadge :shift="course.school_shift" />
                                </span>
                                <span class="course-popover__item-date">(inicio: {{ formatDate(course.start_date) }})</span>
                                <div class="course-popover__item-status">
                                    <span :class="{
                                        'course-popover__status': true,
                                        'course-popover__status--active': course.active,
                                        'course-popover__status--inactive': !course.active,
                                    }">
                                        {{ course.active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <!-- Show "curso actual" legend for the course being edited -->
                                <div v-if="props.currentCourse?.id === course.id" class="course-popover__item-current">
                                    <span class="course-popover__current-legend">- curso actual -</span>
                                </div>
                            </div>
                        </div>
                        <div class="course-popover__item-actions">
                            <!-- Hide select button for current course being edited -->
                            <button v-if="props.currentCourse?.id !== course.id" @click="selectCourse(course)" class="course-popover__select-btn">
                                Seleccionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="course-popover__empty">
                <span>No se encontraron cursos</span>
                <!-- Debug info -->
                <div style="background: #f0f0f0; padding: 10px; margin-top: 10px; font-size: 12px;">
                    Debug: courses.value.length = {{ courses?.length || 0 }},
                    filteredCourses.length = {{ filteredCourses.length }},
                    loading = {{ loading }}
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="paginationData && paginationData.links && paginationData.links.length > 3"
                class="course-popover__pagination">
                <div class="pagination__container">
                    <template v-for="(link, key) in paginationData.links" :key="key">
                        <!-- Previous Link -->
                        <div v-if="key === 0 && link.url === null" class="pagination__item pagination__item--disabled"
                            v-html="link.label" />
                                                <button v-else-if="key === 0" class="pagination__item" @click.prevent="handlePagination(link.url)"
                            v-html="link.label" />
                        
                        <!-- Page Numbers -->
                        <div v-else-if="link.url === null && key !== paginationData.links.length - 1"
                            class="pagination__item pagination__item--disabled" v-html="link.label" />
                        <button v-else-if="key !== paginationData.links.length - 1" class="pagination__item"
                            :class="{ 'pagination__item--active': link.active }" @click.prevent="handlePagination(link.url)"
                            v-html="link.label" />
                        
                        <!-- Next Link -->
                        <div v-if="key === paginationData.links.length - 1 && link.url === null"
                            class="pagination__item pagination__item--disabled" v-html="link.label" />
                        <button v-else-if="key === paginationData.links.length - 1" class="pagination__item"
                            @click.prevent="handlePagination(link.url)" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import { formatDate } from '@/utils/date'

const props = defineProps({
    modelValue: {
        type: [Number, null],
        default: null
    },
    lastSaved: {
        type: [Object, null],
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
    },
    currentCourse: {
        type: [Object, null],
        default: null
    }
})

const emit = defineEmits(['update:modelValue'])

// Refs
const popoverRef = ref(null)
const popoverShiftSelect = ref(null)
const isOpen = ref(false)
const loading = ref(false)
const search = ref('')
const selectedCourse = ref(null)
const originalSelectedCourse = ref(null) // Store the original value when opening popover
const courses = ref([])
const paginationData = ref(null)
const yearError = ref(false)

// Filters
const filters = ref({
    year: props.startDate ? new Date(props.startDate).getFullYear() : new Date().getFullYear(),
    active: true, // Default to active courses
    schoolShift: props.schoolShift || null
})

// Computed
const maxYear = computed(() => {
    return props.startDate ? new Date(props.startDate).getFullYear() : new Date().getFullYear()
})

// Helper function to get course number from course object
const getCourseNumber = (course) => {
    return course?.number || null;
}

const filteredCourses = computed(() => {
    // console.log('🔍 filteredCourses computed - courses.value:', courses.value)
    // console.log('🔍 filteredCourses computed - search.value:', search.value)

    // Safety check for undefined courses.value
    if (!courses.value || !Array.isArray(courses.value)) {
        // console.log('🔍 filteredCourses: courses.value is not an array, returning empty array')
        return []
    }

    let result = courses.value

    // Filter by course number (only show courses with number <= current course number)
    if (props.currentCourse?.number) {
        result = result.filter(course => {
            const courseNumber = getCourseNumber(course)
            return courseNumber === null || courseNumber <= props.currentCourse.number
        })
        // console.log('🔍 Filtered by course number <=', props.currentCourse.number, ':', result.length, 'courses')
    }

    // Filter by search term
    if (search.value) {
        result = result.filter(course =>
            course.nice_name.toLowerCase().includes(search.value.toLowerCase())
        )
        // console.log('🔍 filteredCourses result (with search):', result)
    } else {
        // console.log('🔍 filteredCourses result (no search):', result)
    }

    return result
})

// Methods
const togglePopover = () => {
    // console.log('🔍 togglePopover called, current isOpen:', isOpen.value)
    isOpen.value = !isOpen.value
    // console.log('🔍 isOpen changed to:', isOpen.value)
    if (isOpen.value) {
        // console.log('🔍 Opening popover, saving original value and calling loadCourses')
        // Save the current selected course as original value
        originalSelectedCourse.value = selectedCourse.value
        loadCourses()
    }
}

const closePopover = () => {
    isOpen.value = false
    // Restore original value if popover was closed without selection
    if (originalSelectedCourse.value && selectedCourse.value !== originalSelectedCourse.value) {
        selectedCourse.value = originalSelectedCourse.value
        // console.log('🔍 Restored original selected course:', selectedCourse.value)
    }
}

const triggerFilter = () => {
    // console.log('popoverShiftSelected', filters.value.schoolShift);
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
        // Check if required props are available
        if (!props.school?.slug || !props.schoolLevel?.code) {
            console.error('Missing required props: school.slug or schoolLevel.code')
            courses.value = []
            return
        }

        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

        // Use fetch for direct JSON response
        const response = await fetch(route('school.courses.search', {
            school: props.school.slug,
            schoolLevel: props.schoolLevel.code
        }), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                school_id: props.school.id,
                school_level_id: props.schoolLevel.id,
                school_shift_id: filters.value.schoolShift,
                start_date: props.startDate,
                year: filters.value.year,
                active: filters.value.active
            })
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        // console.log('🔍 API Response:', data)
        // console.log('🔍 Courses data:', data.courses)
        // console.log('🔍 Courses type:', typeof data.courses)
        // console.log('🔍 Courses length:', data.courses?.length)

        if (data.courses) {
            // Handle paginated response - courses are in data.courses.data
            if (data.courses.data && Array.isArray(data.courses.data)) {
                courses.value = data.courses.data
                paginationData.value = data.courses
                // console.log('🔍 Updated courses.value (from paginated data):', courses.value)
                // console.log('🔍 Updated paginationData:', paginationData.value)
            } else if (Array.isArray(data.courses)) {
                // Handle direct array response
                courses.value = data.courses
                paginationData.value = null
                // console.log('🔍 Updated courses.value (direct array):', courses.value)
            } else {
                courses.value = []
                paginationData.value = null
                // console.log('🔍 No valid courses data found, set empty array')
            }
            
            // After loading courses, set selectedCourse if modelValue exists
            if (props.modelValue && courses.value.length > 0) {
                selectedCourse.value = courses.value.find(course => course.id === props.modelValue)
                // console.log('🔍 Set selectedCourse after loading:', selectedCourse.value)
            }
        } else {
            courses.value = []
            paginationData.value = null
            // console.log('🔍 No courses found, set empty array')
        }
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
    // Clear original value since we made a selection
    originalSelectedCourse.value = null
    closePopover()
}

const selectNoCourse = () => {
    selectedCourse.value = null
    emit('update:modelValue', null)
    // Clear original value since we made a selection
    originalSelectedCourse.value = null
    closePopover()
}

const handlePagination = async (url) => {
    // console.log('🔍 Pagination clicked, loading URL:', url)
    loading.value = true

    try {
        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

        // Extract page number from URL
        const urlParams = new URLSearchParams(url.split('?')[1])
        const page = urlParams.get('page')

        // console.log('🔍 Loading page:', page)

        // Construct the full URL by using the route helper with the page parameter
        const fullUrl = route('school.courses.search', {
            school: props.school.slug,
            schoolLevel: props.schoolLevel.code,
            page: page
        })

        // console.log('🔍 Full URL for pagination:', fullUrl)

        // Use fetch to load the paginated data
        const response = await fetch(fullUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                school_id: props.school.id,
                school_level_id: props.schoolLevel.id,
                school_shift_id: filters.value.schoolShift,
                start_date: props.startDate,
                year: filters.value.year,
                active: filters.value.active
            })
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        // console.log('🔍 Pagination API Response:', data)

        if (data.courses) {
            // Handle paginated response - courses are in data.courses.data
            if (data.courses.data && Array.isArray(data.courses.data)) {
                courses.value = data.courses.data
                paginationData.value = data.courses
                // console.log('🔍 Updated courses.value (pagination):', courses.value)
                // console.log('🔍 Updated paginationData (pagination):', paginationData.value)
            } else if (Array.isArray(data.courses)) {
                // Handle direct array response
                courses.value = data.courses
                paginationData.value = null
                // console.log('🔍 Updated courses.value (direct array):', courses.value)
            } else {
                courses.value = []
                paginationData.value = null
                // console.log('🔍 No valid courses data found, set empty array')
            }
            
            // After loading courses, set selectedCourse if modelValue exists
            if (props.modelValue && courses.value.length > 0) {
                selectedCourse.value = courses.value.find(course => course.id === props.modelValue)
                // console.log('🔍 Set selectedCourse after pagination:', selectedCourse.value)
            }
        } else {
            courses.value = []
            paginationData.value = null
            // console.log('🔍 No courses found, set empty array')
        }
    } catch (error) {
        console.error('Error loading paginated courses:', error)
    } finally {
        loading.value = false
    }
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        // If courses are already loaded, find the course immediately
        if (courses.value.length > 0) {
            selectedCourse.value = courses.value.find(course => course.id === newValue)
        } else {
            // If courses aren't loaded yet, we'll need to load them and then find the course
            // This will be handled in the loadCourses method
            // Don't set to null if we have lastSaved
            if (!props.lastSaved) {
                selectedCourse.value = null
            }
        }
    } else {
        // Only clear if we don't have lastSaved
        if (!props.lastSaved) {
            selectedCourse.value = null
        }
    }
}, { immediate: true })

// Initialize selectedCourse with lastSaved if available
watch(() => props.lastSaved, (newValue) => {
    if (newValue) {
        selectedCourse.value = newValue
        // console.log('🔍 Set selectedCourse from lastSaved:', selectedCourse.value)
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
