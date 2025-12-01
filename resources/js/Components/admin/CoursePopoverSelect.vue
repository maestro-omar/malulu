<template>
    <div class="course-popover" ref="popoverRef">
        <!-- Trigger Button -->
        <div class="course-popover__trigger" @click="togglePopover">
            <div class="course-popover__trigger-content">
                <span v-if="isMultiple && selectedCourses.length > 0" class="course-popover__selected">
                    {{ selectedCourses.length }} curso{{ selectedCourses.length !== 1 ? 's' : '' }} seleccionado{{ selectedCourses.length !== 1 ? 's' : '' }}
                </span>
                <span v-else-if="!isMultiple && selectedCourse" class="course-popover__selected">
                    {{ selectedCourse.nice_name }}
                </span>
                <span v-else class="course-popover__placeholder">
                    {{ placeholderText }}
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
                <h3 class="course-popover__title">{{ popoverTitle }}</h3>
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
                    <div v-if="!forceYear" class="course-popover__filter-group">
                        <label class="course-popover__filter-label">Año</label>
                        <input type="number" v-model.number="filters.year" @input="triggerFilter" :max="maxYear"
                            class="course-popover__filter-input" :placeholder="`Ej: ${maxYear}`" />
                        <small v-if="yearError" class="course-popover__error">
                            El año no puede ser mayor a {{ maxYear }}
                        </small>
                    </div>

                    <div v-if="!forceActive" class="course-popover__filter-group">
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
                
                <!-- "Sin curso anterior" option (only for single select) -->
                <div v-if="!isMultiple" class="course-popover__item course-popover__item--no-course"
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
                    :class="{ 
                        'course-popover__item--selected': isMultiple 
                            ? selectedCourses.some(c => c.id === course.id)
                            : selectedCourse?.id === course.id 
                    }">
                    <div class="course-popover__item-content">
                        <div class="course-popover__item-main" @click="isMultiple ? toggleCourse(course) : selectCourse(course)">
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
                                <!-- Show checkmark for multi-select -->
                                <div v-if="isMultiple && selectedCourses.some(c => c.id === course.id)" class="course-popover__item-checkmark">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="course-popover__item-actions">
                            <!-- Hide select button for current course being edited -->
                            <button v-if="props.currentCourse?.id !== course.id" 
                                @click="isMultiple ? toggleCourse(course) : selectCourse(course)" 
                                class="course-popover__select-btn"
                                :class="{ 'course-popover__select-btn--selected': isMultiple && selectedCourses.some(c => c.id === course.id) }">
                                {{ isMultiple 
                                    ? (selectedCourses.some(c => c.id === course.id) ? 'Quitar' : 'Agregar')
                                    : 'Seleccionar' 
                                }}
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
            <div v-if="paginationData && paginationData.links && paginationData.links.length > 3"
                class="course-popover__pagination">
                <div class="pagination__container">
                    <template v-for="(link, key) in paginationData.links" :key="key">
                        <!-- Previous Link -->
                        <div v-if="key === 0 && link.url === null" class="pagination__item pagination__item--disabled"
                            v-html="link.label" />
                        <button v-else-if="key === 0" class="pagination__item" 
                            @click.prevent="handlePagination(link.url, link.label)"
                            v-html="link.label" />
                        
                        <!-- Page Numbers -->
                        <div v-else-if="link.url === null && key !== paginationData.links.length - 1"
                            class="pagination__item pagination__item--disabled" v-html="link.label" />
                        <button v-else-if="key !== paginationData.links.length - 1" class="pagination__item"
                            :class="{ 'pagination__item--active': link.active }" 
                            @click.prevent="handlePagination(link.url, link.label)"
                            v-html="link.label" />
                        
                        <!-- Next Link -->
                        <div v-if="key === paginationData.links.length - 1 && link.url === null"
                            class="pagination__item pagination__item--disabled" v-html="link.label" />
                        <button v-else-if="key === paginationData.links.length - 1" class="pagination__item"
                            @click.prevent="handlePagination(link.url, link.label)"
                            v-html="link.label" />
                    </template>
                </div>
            </div>

            <!-- Done Button for Multi-select -->
            <div v-if="isMultiple" class="course-popover__footer">
                <button @click="closePopover" class="course-popover__done-btn">
                    Listo ({{ selectedCourses.length }} seleccionado{{ selectedCourses.length !== 1 ? 's' : '' }})
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import { formatDate } from '@/Utils/date'

const props = defineProps({
    modelValue: {
        type: [Number, Array, null],
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
    },
    multiple: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Seleccionar Curso Anterior (opcional)'
    },
    placeholder: {
        type: String,
        default: 'Sin curso anterior'
    },
    forceActive: {
        type: Boolean,
        default: false
    },
    forceYear: {
        type: Boolean,
        default: false
    },
    selectedCourses: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue', 'courseSelected', 'coursesSelected'])

// Computed
const isMultiple = computed(() => props.multiple)
const popoverTitle = computed(() => props.title)
const placeholderText = computed(() => props.placeholder)

// Refs
const popoverRef = ref(null)
const popoverShiftSelect = ref(null)
const isOpen = ref(false)
const loading = ref(false)
const search = ref('')
const selectedCourse = ref(null)
const selectedCourses = ref([...props.selectedCourses]) // For multi-select
const originalSelectedCourse = ref(null) // Store the original value when opening popover
const originalSelectedCourses = ref([]) // Store the original values when opening popover
const courses = ref([])
const paginationData = ref(null)
const yearError = ref(false)

// Filters
const filters = ref({
    year: props.startDate ? new Date(props.startDate).getFullYear() : new Date().getFullYear(),
    active: props.forceActive ? true : true, // Default to active courses, force if prop is true
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
    // Safety check for undefined courses.value
    if (!courses.value || !Array.isArray(courses.value)) {
        return []
    }

    // Get selected course IDs and full course objects
    let selectedCourseIds = []
    let selectedCourseObjects = []
    
    if (isMultiple.value) {
        selectedCourseIds = selectedCourses.value.map(c => c.id)
        // Try to find full course objects from courses.value, or use the stored objects
        selectedCourseObjects = selectedCourses.value.map(selected => {
            const fullCourse = courses.value.find(c => c.id === selected.id)
            return fullCourse || selected // Use full course if available, otherwise use stored object
        })
    } else if (selectedCourse.value) {
        selectedCourseIds = [selectedCourse.value.id]
        // Try to find full course object from courses.value, or use the stored object
        const fullCourse = courses.value.find(c => c.id === selectedCourse.value.id)
        selectedCourseObjects = fullCourse ? [fullCourse] : [selectedCourse.value]
    }

    let result = courses.value

    // Filter by course number (only show courses with number <= current course number)
    if (props.currentCourse?.number) {
        result = result.filter(course => {
            const courseNumber = getCourseNumber(course)
            return courseNumber === null || courseNumber <= props.currentCourse.number
        })
    }

    // Filter by search term
    if (search.value) {
        result = result.filter(course =>
            course.nice_name.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    // Always include selected courses at the top, even if they don't match filters
    if (selectedCourseIds.length > 0) {
        // Find selected courses that are already in the filtered result
        const selectedInResult = result.filter(course => 
            selectedCourseIds.includes(course.id)
        )
        
        // Find selected courses that are NOT in the filtered result (need to add them)
        const selectedNotInResult = selectedCourseObjects.filter(selected => 
            !result.some(c => c.id === selected.id)
        )
        
        // Combine: selected courses at top (those in result + those not in result), then rest
        const notSelectedInResult = result.filter(course => 
            !selectedCourseIds.includes(course.id)
        )
        result = [...selectedInResult, ...selectedNotInResult, ...notSelectedInResult]
    }

    return result
})

// Methods
const togglePopover = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        if (isMultiple.value) {
            // Save the current selected courses as original values
            originalSelectedCourses.value = [...selectedCourses.value]
            // Ensure selectedCourses is synced with props before loading
            if (props.selectedCourses && props.selectedCourses.length > 0) {
                selectedCourses.value = [...props.selectedCourses]
            } else if (Array.isArray(props.modelValue) && props.modelValue.length > 0) {
                // If we have modelValue but no selectedCourses prop, initialize from modelValue
                // This will be updated when courses are loaded
                selectedCourses.value = []
            }
        } else {
            // Save the current selected course as original value
            originalSelectedCourse.value = selectedCourse.value
        }
        loadCourses()
    }
}

const closePopover = () => {
    isOpen.value = false
    if (isMultiple.value) {
        // Emit the selected courses when closing
        emit('coursesSelected', selectedCourses.value)
        emit('update:modelValue', selectedCourses.value.map(c => c.id))
    } else {
        // Restore original value if popover was closed without selection
        if (originalSelectedCourse.value && selectedCourse.value !== originalSelectedCourse.value) {
            selectedCourse.value = originalSelectedCourse.value
        }
    }
}

const triggerFilter = () => {
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

        if (data.courses) {
            // Handle paginated response - courses are in data.courses.data
            if (data.courses.data && Array.isArray(data.courses.data)) {
                courses.value = data.courses.data
                paginationData.value = data.courses
            } else if (Array.isArray(data.courses)) {
                // Handle direct array response
                courses.value = data.courses
                paginationData.value = null
            } else {
                courses.value = []
                paginationData.value = null
            }
            
            // After loading courses, update selectedCourse/selectedCourses if modelValue exists
            // IMPORTANT: Preserve existing selected courses that don't match current filters
            if (isMultiple.value) {
                if (Array.isArray(props.modelValue) && props.modelValue.length > 0) {
                    // Find courses from newly loaded courses that match modelValue (store full objects)
                    const newSelectedFromLoaded = courses.value.filter(course => props.modelValue.includes(course.id))
                    
                    // Merge: preserve existing selected courses, update with full data if found in loaded courses
                    const updatedSelected = props.modelValue.map(id => {
                        // Check if we already have this course in selectedCourses
                        const existing = selectedCourses.value.find(c => c.id === id)
                        if (existing) {
                            // Update with full course data if available in loaded courses
                            const fullCourse = newSelectedFromLoaded.find(c => c.id === id)
                            return fullCourse || existing // Use full course data if available, otherwise keep existing
                        }
                        // New selection from loaded courses
                        return newSelectedFromLoaded.find(c => c.id === id)
                    }).filter(Boolean) // Remove any undefined entries
                    
                    // Only update if we have valid selections (preserve existing if no matches found)
                    if (updatedSelected.length > 0) {
                        selectedCourses.value = updatedSelected
                    }
                    // If updatedSelected is empty but we have existing selections, keep them
                }
            } else {
                if (props.modelValue) {
                    const foundCourse = courses.value.find(course => course.id === props.modelValue)
                    if (foundCourse) {
                        selectedCourse.value = foundCourse
                    }
                    // If not found in loaded courses, keep existing selectedCourse
                }
            }
        } else {
            courses.value = []
            paginationData.value = null
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

const toggleCourse = (course) => {
    const index = selectedCourses.value.findIndex(c => c.id === course.id)
    if (index > -1) {
        // Remove course
        selectedCourses.value.splice(index, 1)
    } else {
        // Add full course object to preserve all data (needed when filters change)
        selectedCourses.value.push({
            ...course, // Store full course object
            school_shift: course.school_shift || null
        })
    }
    // Don't close popover in multi-select mode, allow selecting multiple
}

const selectCourse = (course) => {
    selectedCourse.value = course
    emit('update:modelValue', course.id)
    emit('courseSelected', course)
    // Clear original value since we made a selection
    originalSelectedCourse.value = null
    closePopover()
}

const selectNoCourse = () => {
    selectedCourse.value = null
    emit('update:modelValue', null)
    emit('courseSelected', null)
    // Clear original value since we made a selection
    originalSelectedCourse.value = null
    closePopover()
}

const handlePagination = async (url, label = null) => {
    loading.value = true

    try {
        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

        // Laravel pagination URLs are GET URLs, but we need to POST
        // Prioritize label extraction for page numbers since labels are always accurate
        let page = null
        
        // Method 1: Extract page number from label first (most reliable for page numbers)
        if (label) {
            // Try to extract numeric value from label (strip HTML entities and get number)
            const labelText = label.replace(/&[^;]+;/g, '').trim() // Remove HTML entities like &laquo;
            const labelMatch = labelText.match(/^(\d+)$/) // Match pure numbers like "1", "2", "3"
            if (labelMatch && labelMatch[1]) {
                // Found a numeric page number in the label - use it!
                page = labelMatch[1]
            } else if (labelText.toLowerCase().includes('anterior') || labelText.toLowerCase().includes('previous')) {
                // For "Previous" / "Anterior", go to current_page - 1
                if (paginationData.value) {
                    const currentPage = paginationData.value.current_page || 1
                    page = Math.max(1, currentPage - 1).toString()
                }
            } else if (labelText.toLowerCase().includes('siguiente') || labelText.toLowerCase().includes('next')) {
                // For "Next" / "Siguiente", go to current_page + 1
                if (paginationData.value) {
                    const currentPage = paginationData.value.current_page || 1
                    const lastPage = paginationData.value.last_page || 1
                    page = Math.min(lastPage, currentPage + 1).toString()
                }
            }
        }
        
        // Method 2: Fallback to URL extraction if label didn't work
        if (!page && url) {
            // Try regex match first (works for both relative and absolute URLs)
            const pageMatch = url.match(/[?&]page=(\d+)/)
            if (pageMatch && pageMatch[1]) {
                page = pageMatch[1]
            } else {
                // Try parsing as URL
                try {
                    // Handle both relative and absolute URLs
                    let fullUrl = url
                    if (!url.startsWith('http')) {
                        const baseUrl = window.location.origin
                        fullUrl = baseUrl + url
                    }
                    
                    const urlObj = new URL(fullUrl)
                    const extractedPage = urlObj.searchParams.get('page')
                    if (extractedPage) {
                        page = extractedPage
                    }
                } catch (e) {
                    // URL parsing failed
                }
            }
        }
        
        // Default to page 1 if nothing worked
        if (!page) {
            page = '1'
        }

        // Use the same route as loadCourses
        const routeUrl = route('school.courses.search', {
            school: props.school.slug,
            schoolLevel: props.schoolLevel.code
        })

        // Laravel pagination uses 'p' as the page parameter (see resources/lang/es/pagination.php)
        // Build the full URL with page parameter in query string
        const finalUrl = `${routeUrl}?p=${page}`

        // Use fetch to load the paginated data
        // Include 'p' in both query string and body to ensure backend reads it correctly
        const response = await fetch(finalUrl, {
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
                active: filters.value.active,
                p: parseInt(page) // Include 'p' in body as well (Laravel pagination parameter)
            })
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()

        if (data.courses) {
            // Handle paginated response - courses are in data.courses.data
            if (data.courses.data && Array.isArray(data.courses.data)) {
                courses.value = data.courses.data
                paginationData.value = data.courses
            } else if (Array.isArray(data.courses)) {
                // Handle direct array response
                courses.value = data.courses
                paginationData.value = null
            } else {
                courses.value = []
                paginationData.value = null
            }
            
            // After loading courses, update selectedCourse/selectedCourses if modelValue exists
            // IMPORTANT: Preserve existing selected courses that don't match current filters
            if (isMultiple.value) {
                if (Array.isArray(props.modelValue) && props.modelValue.length > 0) {
                    // Find courses from newly loaded courses that match modelValue (store full objects)
                    const newSelectedFromLoaded = courses.value.filter(course => props.modelValue.includes(course.id))
                    
                    // Merge: preserve existing selected courses, update with full data if found in loaded courses
                    const updatedSelected = props.modelValue.map(id => {
                        // Check if we already have this course in selectedCourses
                        const existing = selectedCourses.value.find(c => c.id === id)
                        if (existing) {
                            // Update with full course data if available in loaded courses
                            const fullCourse = newSelectedFromLoaded.find(c => c.id === id)
                            return fullCourse || existing // Use full course data if available, otherwise keep existing
                        }
                        // New selection from loaded courses
                        return newSelectedFromLoaded.find(c => c.id === id)
                    }).filter(Boolean) // Remove any undefined entries
                    
                    // Only update if we have valid selections (preserve existing if no matches found)
                    if (updatedSelected.length > 0) {
                        selectedCourses.value = updatedSelected
                    }
                    // If updatedSelected is empty but we have existing selections, keep them
                }
            } else {
                if (props.modelValue) {
                    const foundCourse = courses.value.find(course => course.id === props.modelValue)
                    if (foundCourse) {
                        selectedCourse.value = foundCourse
                    }
                    // If not found in loaded courses, keep existing selectedCourse
                }
            }
        } else {
            courses.value = []
            paginationData.value = null
        }
    } catch (error) {
        console.error('Error loading paginated courses:', error)
    } finally {
        loading.value = false
    }
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (isMultiple.value) {
        if (Array.isArray(newValue) && newValue.length > 0) {
            // Get current selected course IDs
            const currentSelectedIds = selectedCourses.value.map(c => c.id)
            
            // Check if modelValue matches current selection - if so, don't overwrite
            const modelValueSet = new Set(newValue)
            const currentSet = new Set(currentSelectedIds)
            const areEqual = modelValueSet.size === currentSet.size && 
                            [...modelValueSet].every(id => currentSet.has(id))
            
            if (!areEqual) {
                // Only update if courses are loaded and we can find matches
                if (courses.value.length > 0) {
                    const newSelectedFromLoaded = courses.value
                        .filter(course => newValue.includes(course.id))
                        .map(course => ({
                            id: course.id,
                            nice_name: course.nice_name,
                            school_shift: course.school_shift || null
                        }))
                    
                    // Merge: preserve existing selections, update with new data
                    const updatedSelected = newValue.map(id => {
                        const existing = selectedCourses.value.find(c => c.id === id)
                        if (existing) {
                            const fullCourse = newSelectedFromLoaded.find(c => c.id === id)
                            return fullCourse || existing
                        }
                        return newSelectedFromLoaded.find(c => c.id === id)
                    }).filter(Boolean)
                    
                    if (updatedSelected.length > 0) {
                        selectedCourses.value = updatedSelected
                    }
                } else {
                    // If courses aren't loaded yet, preserve existing selections that match modelValue
                    selectedCourses.value = selectedCourses.value.filter(c => newValue.includes(c.id))
                    // Add any new IDs from modelValue (will be updated when courses load)
                    const newIds = newValue.filter(id => !currentSelectedIds.includes(id))
                    if (newIds.length > 0) {
                        // Add placeholder objects - will be updated when courses load
                        newIds.forEach(id => {
                            selectedCourses.value.push({
                                id: id,
                                nice_name: `Curso ${id}`,
                                school_shift: null
                            })
                        })
                    }
                }
            }
        } else {
            selectedCourses.value = []
        }
    } else {
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
    }
}, { immediate: true })

// Initialize selectedCourse with lastSaved if available
watch(() => props.lastSaved, (newValue) => {
    if (newValue && !isMultiple.value) {
        selectedCourse.value = newValue
    }
}, { immediate: true })

// Watch for selectedCourses prop changes (for multi-select)
watch(() => props.selectedCourses, (newValue) => {
    if (isMultiple.value && Array.isArray(newValue)) {
        selectedCourses.value = [...newValue]
    }
}, { immediate: true, deep: true })

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
