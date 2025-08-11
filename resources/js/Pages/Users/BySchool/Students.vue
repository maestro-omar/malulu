<!-- Students list for teachers or system admin (not for public access or for students to see their classmates) -->
<script setup>
import EmailField from '@/Components/admin/EmailField.vue';
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import SchoolLevelBadge from '@/Components/badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/badges/SchoolShiftBadge.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';
import { route_school_student } from '@/utils/routes';
import { formatNumber } from '@/utils/strings';
import { useTableSearchSort } from '@/utils/tables';
import noImage from '@images/no-image-person.png';
import { Head, Link, router } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    school: Object,
    users: Object,
    flash: {
        type: Object,
        default: () => ({})
    },
    breadcrumbs: Array,
    filters: {
        type: Object,
        default: () => ({})
    },
});

// Use table search and sort composable
const {
    search,
    sortField,
    sortDirection,
    handleSearch,
    handleSort,
    getSortClass,
    clearSearch
} = useTableSearchSort({
    routeName: 'school.students',
    routeParams: { school: props.school.slug },
    filters: props.filters
});

// Watch for changes in search
watch(search, () => {
    handleSearch();
});

// Debug log when component receives props
// console.log('Users data received:', props.users);

const deleteUser = (id) => {
    if (confirm('¿Está seguro de eliminar este usuario?')) {
        router.delete(route('users.destroy', id));
    }
};

const isAdmin = (user) => {
    return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
};

</script>

<template>

    <Head title="Estudiantes" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Listado de estudiantes`" :add="{
                show: hasPermission($page.props, 'student.create'),
                href: route('school.students.create', { 'school': school.slug }),
                label: 'Nuevo'
            }" />
        </template>

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <div class="table__wrapper">
                <div class="table__container">
                    <!-- Search Filter -->
                    <div class="table__search">
                        <input type="text" v-model="search" @input="handleSearch" placeholder="Buscar estudiantes..." />
                        <button v-if="search" @click="clearSearch" class="table__search-clear">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table__desktop">
                        <table class="table__table">
                            <thead class="table__thead">
                                <tr>
                                    <th class="table__th">Foto</th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('firstname')]"
                                        @click="handleSort('firstname')">
                                        Nombre
                                    </th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('lastname')]"
                                        @click="handleSort('lastname')">
                                        Apellido
                                    </th>
                                    <th class="table__th">Email</th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('id_number')]"
                                        @click="handleSort('id_number')">
                                        DNI
                                    </th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('level')]"
                                        @click="handleSort('level')">
                                        Nivel
                                    </th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('shift')]"
                                        @click="handleSort('shift')">
                                        Turno
                                    </th>
                                    <th :class="['table__th', 'table__th--sortable', getSortClass('course')]"
                                        @click="handleSort('course')">
                                        Curso
                                    </th>
                                    <th class="table__th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table__tbody">
                                <tr v-for="(user, index) in users.data" :key="user.id" :class="{
                                    'table__tr--highlighted': user.id === $page.props.auth.user.id,
                                    'table__tr--even': user.id !== $page.props.auth.user.id && index % 2 === 0,
                                    'table__tr--odd': user.id !== $page.props.auth.user.id && index % 2 === 1
                                }">
                                    <td class="table__td table__photo">
                                        <img :src="user.picture || noImage" :alt="user.name" />
                                    </td>
                                    <td class="table__td table__name">
                                        <Link :href="route_school_student(school, user, 'show')">
                                        {{ user.firstname }}
                                        </Link>
                                    </td>
                                    <td class="table__td table__name">
                                        {{ user.lastname }}
                                    </td>
                                    <td class="table__td table__email">
                                        <EmailField :email="user.email" />
                                    </td>
                                    <td class="table__td table__id">
                                        {{ formatNumber(user.id_number) }}
                                    </td>
                                    <td class="table__td table__name">
                                        <SchoolLevelBadge v-if="user.course" :level="user.course.level" />
                                    </td>
                                    <td class="table__td table__name">
                                        <SchoolShiftBadge v-if="user.course" :shift="user.course.shift" />
                                    </td>
                                    <td class="table__td table__name">
                                        {{ user.course ? user.course.nice_name : '' }}
                                    </td>
                                    <td class="table__td table__actions">
                                        <Link :href="route_school_student(school, user, 'show')">
                                        Ver
                                        </Link>
                                        <Link v-if="hasPermission($page.props, 'student.edit')"
                                            :href="route_school_student(school, user, 'edit')">
                                        Editar
                                        </Link>
                                        <button v-if="hasPermission($page.props, 'student.delete') &&
                                            !isAdmin(user) &&
                                            user.id !== $page.props.auth.user.id" @click="deleteUser(user.id)">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="table__mobile">
                        <div v-for="(user, index) in users.data" :key="user.id" :class="{
                            'table__card--highlighted': user.id === $page.props.auth.user.id,
                            'table__card--even': user.id !== $page.props.auth.user.id && index % 2 === 0,
                            'table__card--odd': user.id !== $page.props.auth.user.id && index % 2 === 1
                        }" class="table__card">
                            <div class="table__card-header">
                                <div class="table__card-user">
                                    <img :src="user.picture || noImage" :alt="user.name" />
                                    <div class="table__card-info">
                                        <h3>{{ user.name }}</h3>
                                        <p>{{ user.email }}</p>
                                    </div>
                                </div>
                                <div class="table__card-actions">
                                    <Link :href="route('users.show', user.id)">
                                    Ver
                                    </Link>
                                    <Link v-if="hasPermission($page.props, 'student.edit')"
                                        :href="route('users.edit', user.id)">
                                    Editar
                                    </Link>
                                    <button v-if="hasPermission($page.props, 'student.delete')">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="table__pagination">
                        <div class="table__pagination-info">
                            Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }} resultados
                        </div>
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>