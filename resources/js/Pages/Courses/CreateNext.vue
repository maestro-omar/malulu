<template>

    <Head :title="`${school.short} - Crear próximos cursos de ${selectedLevel.name}`" />

    <AuthenticatedLayout>
        <AdminHeader 
            :title="`${school.short} - Crear próximos cursos de ${selectedLevel.name}`">
        </AdminHeader>

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <form @submit.prevent="submit">
                <div class="table__wrapper">
                    <div class="table__container">
                        <div class="table__filters" v-if="schoolShifts && schoolShifts.length > 1">
                            <div class="table__filters-grid">
                                <SelectSchoolShift v-model="selectedShift" @update:modelValue="triggerFilter"
                                    :options="schoolShifts" :showAllOption="true" />
                            </div>
                        </div>
                        <table class="table__table">
                            <thead class="table__thead">
                                <tr>
                                    <th class="table__th table__th--center">Curso</th>
                                    <th class="table__th table__th--center">Turno</th>
                                    <th class="table__th">Fecha de Inicio</th>
                                    <th class="table__th">Fecha de Fin</th>
                                    <th class="table__th">
                                        ¿Asignar existente?
                                        <div style="margin-top: 0.25em;">
                                            <label style="font-weight: normal; font-size: 0.95em;">
                                                <input type="checkbox" v-model="allAssignExistingChecked"
                                                    @change="toggleAllCheckboxes('assign_existing', $event.target.checked)"
                                                    style="margin-right: 0.3em;" />
                                                todos
                                            </label>
                                        </div>
                                    </th>
                                    <th class="table__th">
                                        ¿Crear siguiente?
                                        <div style="margin-top: 0.25em;">
                                            <label style="font-weight: normal; font-size: 0.95em;">
                                                <input type="checkbox" v-model="allCreateNextChecked"
                                                    @change="toggleAllCheckboxes('create_next', $event.target.checked)"
                                                    style="margin-right: 0.3em;" />
                                                todos
                                            </label>
                                        </div>
                                    </th>
                                    <th class="table__th">
                                        ¿Duplicar inicial?
                                        <div style="margin-top: 0.25em;">
                                            <label style="font-weight: normal; font-size: 0.95em;">
                                                <input type="checkbox" v-model="allDuplicateInitialChecked"
                                                    @change="toggleAllCheckboxes('duplicate_initial', $event.target.checked)"
                                                    style="margin-right: 0.3em;" />
                                                todos
                                            </label>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="table__tbody">
                                <tr v-for="(course, index) in courses" :key="course.id" :class="{
                                    'table__tr--even': index % 2 === 0,
                                    'table__tr--odd': index % 2 === 1
                                }" :data-shift="course.school_shift.id">
                                    <td class="table__td table__td--center">{{ course.nice_name }}</td>
                                    <td class="table__td table__td--center">
                                        <SchoolShiftBadge :shift="course.school_shift" />
                                    </td>
                                    <td class="table__td">{{ formatDate(course.start_date) }}</td>
                                    <td class="table__td">{{ course.end_date ? formatDate(course.end_date) : '-' }}</td>
                                    <td class="table__td">
                                        <template v-if="course.to_set.existing">
                                            <input type="checkbox" :name="'assign_existing[]'" :value="course.id"
                                                style="margin-right: 0.5em;" />
                                            <a :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.to_set.existing) })"
                                                class="table__link" target="_blank">
                                                {{ course.to_set.existing.nice_name + ' (' +
                                                    getFullYear(course.to_set.existing.start_date) + ')' }}
                                            </a>
                                        </template>
                                    </td>
                                    <td class="table__td">
                                        <template v-if="course.to_set.create">
                                            <input type="checkbox" :name="'create_next[]'" :value="course.id"
                                                style="margin-right: 0.5em;" />
                                            <span>
                                                {{ course.to_set.create.nice_name + ' (' +
                                                    getFullYear(course.to_set.create.start_date) + ')' }}
                                            </span>
                                        </template>
                                    </td>
                                    <td class="table__td">
                                        <template v-if="course.to_duplicate">
                                            <input type="checkbox" :name="'duplicate_initial[]'" :value="course.id"
                                                style="margin-right: 0.5em;" />
                                            <span>
                                                {{ course.to_duplicate.nice_name + ' (' +
                                                    getFullYear(course.to_duplicate.start_date) + ')' }}
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <ActionButtons button-label="Guardar Cambios"
                    :cancel-href="route('school.courses', { school: school.slug, schoolLevel: selectedLevel.code })"
                    :disabled="form.processing" />

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import FlashMessages from '@/Components/admin/FlashMessages.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
import { formatDate, getFullYear } from '../../utils/date'
import { getCourseSlug } from '../../utils/strings'

const props = defineProps({
    courses: {
        type: Object,
        required: true,
    },
    school: {
        type: Object,
        required: true,
    },
    schoolShifts: {
        type: Array,
        required: true,
    },
    selectedLevel: {
        type: Object,
        required: true,
    },
    
    year: {
        type: [Number, null],
        default: null,
    },
    active: {
        type: [Boolean, null],
        default: null,
    },
    shift: {
        type: [String, null],
        default: null,
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
})

const selectedShift = ref(props.shift || null);

// Form for handling the submission
const form = useForm({
    assign_existing: [],
    create_next: [],
    duplicate_initial: [],
})

// Checkbox states for "select all" functionality
const allAssignExistingChecked = ref(false)
const allCreateNextChecked = ref(false)
const allDuplicateInitialChecked = ref(false)

// Watch for changes in individual checkboxes to update "select all" state
const updateSelectAllState = () => {
    const assignExistingCheckboxes = document.querySelectorAll('input[name="assign_existing[]"]');
    const createNextCheckboxes = document.querySelectorAll('input[name="create_next[]"]');
    const duplicateInitialCheckboxes = document.querySelectorAll('input[name="duplicate_initial[]"]');

    const visibleAssignExisting = Array.from(assignExistingCheckboxes).filter(cb =>
        cb.closest('tr').style.display !== 'none'
    );
    const visibleCreateNext = Array.from(createNextCheckboxes).filter(cb =>
        cb.closest('tr').style.display !== 'none'
    );
    const visibleDuplicateInitial = Array.from(duplicateInitialCheckboxes).filter(cb =>
        cb.closest('tr').style.display !== 'none'
    );

    allAssignExistingChecked.value = visibleAssignExisting.length > 0 &&
        visibleAssignExisting.every(cb => cb.checked);
    allCreateNextChecked.value = visibleCreateNext.length > 0 &&
        visibleCreateNext.every(cb => cb.checked);
    allDuplicateInitialChecked.value = visibleDuplicateInitial.length > 0 &&
        visibleDuplicateInitial.every(cb => cb.checked);
};

// console.log('props.courses:', props.courses);

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
    const selectedShiftId = selectedShift.value;
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        const shiftId = row.dataset.shift;
        if (shiftId == selectedShiftId || selectedShiftId === null) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
        // Disable all checkboxes in hidden rows so they are not sent with the form
        tableRows.forEach(row => {
            const isVisible = row.style.display !== 'none';
            const checkboxes = row.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.disabled = !isVisible;
            });
        });
    });

    // Update select all state after filtering
    setTimeout(updateSelectAllState, 0);
};

const toggleAllCheckboxes = (columnsName, value) => {
    const checkboxes = document.querySelectorAll(`tr:not([style*="display: none"]) input[name="${columnsName}[]"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = value;
    });

    // Update the reactive state for the "select all" checkboxes
    if (columnsName === 'assign_existing') {
        allAssignExistingChecked.value = value;
    } else if (columnsName === 'create_next') {
        allCreateNextChecked.value = value;
    } else if (columnsName === 'duplicate_initial') {
        allDuplicateInitialChecked.value = value;
    }
};

// Add event listeners to individual checkboxes when component mounts
onMounted(() => {
    // Add event listeners to all checkboxes
    const allCheckboxes = document.querySelectorAll('input[name="assign_existing[]"], input[name="create_next[]"], input[name="duplicate_initial[]"]');
    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    // Initial state update
    updateSelectAllState();
});

const submit = () => {
    // Collect checked values from checkboxes
    const assignExistingCheckboxes = document.querySelectorAll('input[name="assign_existing[]"]:checked');
    const createNextCheckboxes = document.querySelectorAll('input[name="create_next[]"]:checked');
    const duplicateInitialCheckboxes = document.querySelectorAll('input[name="duplicate_initial[]"]:checked');

    form.assign_existing = Array.from(assignExistingCheckboxes).map(cb => cb.value);
    form.create_next = Array.from(createNextCheckboxes).map(cb => cb.value);
    form.duplicate_initial = Array.from(duplicateInitialCheckboxes).map(cb => cb.value);

    console.log('Submitting form with data:', {
        assign_existing: form.assign_existing,
        create_next: form.create_next,
        duplicate_initial: form.duplicate_initial
    });

    form.post(route('school.course.store-next', {
        school: props.school.slug,
        schoolLevel: props.selectedLevel.code
    }));
};

</script>