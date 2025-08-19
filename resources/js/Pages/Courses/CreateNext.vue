<template>

    <Head :title="`${school.short} - Crear próximos cursos de ${selectedLevel.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs"
                :title="`${school.short} - Crear próximos cursos de ${selectedLevel.name}`">
            </AdminHeader>
        </template>

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <div class="table__wrapper">
                <div class="table__container">
                    <!-- Filter Section -->
                    <div class="table__filters">
                        <h3 class="table__filters-title">Filtros</h3>
                        <div class="table__filters-grid">
                            <!-- Active Status Filter -->
                            <div class="table__filter-group">
                                <label class="table__filter-label">Estado</label>
                                <div class="table__filter-radio-group">
                                    <label class="table__filter-radio">
                                        <input type="radio" name="active-status" :value="true" v-model="activeStatus"
                                            @change="triggerFilter">
                                        <span>Activo</span>
                                    </label>
                                    <label class="table__filter-radio">
                                        <input type="radio" name="active-status" :value="false" v-model="activeStatus"
                                            @change="triggerFilter">
                                        <span>Inactivo</span>
                                    </label>
                                    <label class="table__filter-radio">
                                        <input type="radio" name="active-status" :value="null" v-model="activeStatus"
                                            @change="triggerFilter">
                                        <span>Todos</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Shift Filter -->
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
                                <th class="table__th table__th--center">Activo</th>
                                <th class="table__th">¿Asignar existente?</th>
                                <th class="table__th">¿Crear siguiente?</th>
                                <th class="table__th">¿Duplicar inicial?</th>
                            </tr>
                        </thead>
                        <tbody class="table__tbody">
                            <tr v-for="(course, index) in courses" :key="course.id" :class="{
                                'table__tr--even': index % 2 === 0,
                                'table__tr--odd': index % 2 === 1
                            }">
                                <td class="table__td table__td--center">{{ course.nice_name }}</td>
                                <td class="table__td table__td--center">
                                    <SchoolShiftBadge :shift="course.school_shift" />
                                </td>
                                <td class="table__td">{{ formatDate(course.start_date) }}</td>
                                <td class="table__td">{{ course.end_date ? formatDate(course.end_date) : '-' }}</td>
                                <td class="table__td table__td--center">
                                    <span :class="{
                                        'table__status': true,
                                        'table__status--active': course.active,
                                        'table__status--inactive': !course.active,
                                    }">
                                        {{ course.active ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="table__td">
                                    <Link v-if="course.to_set.existing"
                                        :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.to_set.existing) })"
                                        class="table__link">
                                    {{ course.to_set.existing.nice_name + ' (' +
                                        getFullYear(course.to_set.existing.start_date)
                                        + ')' }}
                                    </Link>
                                </td>
                                <td class="table__td">
                                    <span v-if="course.to_set.create">
                                        {{ course.to_set.create.nice_name + ' (' +
                                            getFullYear(course.to_set.create.start_date)
                                        + ')' }}
                                    </span>
                                </td>
                                <td class="table__td">
                                    <span v-if="course.to_duplicate">
                                        {{ course.to_duplicate.nice_name + ' (' +
                                            getFullYear(course.to_duplicate.start_date)
                                        + ')' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue'
import Pagination from '@/Components/admin/Pagination.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
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
    breadcrumbs: Array,
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
})

const currentYear = computed(() => new Date().getFullYear());
const selectedYear = ref(props.year || currentYear.value);
const activeStatus = ref(props.active !== undefined ? props.active : true);
const selectedShift = ref(props.shift || null);

// console.log('props.courses:', props.courses);

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    filterTimeout = setTimeout(() => {
        router.get(
            route('school.courses', {
                school: props.school.slug,
                schoolLevel: props.selectedLevel.code,
                year: selectedYear.value,
                active: activeStatus.value,
                shift: selectedShift.value,
            }),
            {},
            { preserveState: true, replace: true }
        );
    }, 300);
};



</script>