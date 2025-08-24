<template>

    <Head title="Usuarios" />

    <AuthenticatedLayout title="Usuarios">
        <AdminHeader :breadcrumbs="breadcrumbs" title="Listado de usuarios" :add="{
            show: hasPermission($page.props, 'user.manage'),
            href: route('users.create'),
            label: 'Nuevo usuario'
        }" :trashed="{
            show: hasPermission($page.props, 'user.manage'),
            href: route('users.trashed'),
            label: 'Eliminados'
        }">
        </AdminHeader>
        <q-page class="q-pa-md">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <!-- Search Filter -->
            <div class="row q-mb-md">
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="search"
                        dense
                        outlined
                        placeholder="Buscar usuarios..."
                        @update:model-value="handleSearch"
                        clearable
                    >
                        <template v-slot:prepend>
                            <q-icon name="search" />
                        </template>
                    </q-input>
                </div>
            </div>

            <!-- Quasar Table -->
            <q-table 
                class="mll-table mll-table--users striped-table" 
                dense 
                :rows="users.data" 
                :columns="columns" 
                row-key="id" 
                :pagination="{ rowsPerPage: 30 }"
                :filter="search"
                :filter-method="customFilter"
            >
                <!-- Custom cell for photo -->
                <template #body-cell-photo="props">
                    <q-td :props="props">
                        <q-avatar size="40px">
                            <img :src="props.row.picture || noImage" :alt="props.row.name" />
                        </q-avatar>
                    </q-td>
                </template>

                <!-- Custom cell for email -->
                <template #body-cell-email="props">
                    <q-td :props="props">
                        <EmailField :email="props.row.email" />
                    </q-td>
                </template>

                <!-- Custom cell for schools -->
                <template #body-cell-schools="props">
                    <q-td :props="props">
                        <div class="row q-gutter-xs">
                            <q-chip 
                                v-for="school in props.row.schools" 
                                :key="school.id" 
                                size="sm" 
                                :color="schoolColors[school.id] || 'primary'"
                                text-color="white"
                                :label="school.short"
                                :title="school.name"
                            />
                        </div>
                    </q-td>
                </template>

                <!-- Custom cell for roles -->
                <template #body-cell-roles="props">
                    <q-td :props="props">
                        <div class="row q-gutter-xs">
                            <RoleBadge 
                                v-for="role in getUniqueRoles(props.row.roles)" 
                                :key="role.id"
                                :role="role" 
                            />
                        </div>
                    </q-td>
                </template>

                <!-- Custom cell for actions -->
                <template #body-cell-actions="props">
                    <q-td :props="props">
                        <div class="row items-center q-gutter-sm">
                            <q-btn 
                                flat 
                                round 
                                color="primary" 
                                icon="visibility" 
                                size="sm"
                                :href="route('users.show', props.row.id)" 
                                title="Ver" 
                            />
                            <q-btn 
                                v-if="hasPermission($page.props, 'user.manage') &&
                                    (!isAdmin(props.row) || (isAdmin(props.row) && props.row.id === $page.props.auth.user.id))"
                                flat 
                                round 
                                color="secondary" 
                                icon="edit" 
                                size="sm"
                                :href="route('users.edit', props.row.id)" 
                                title="Editar" 
                            />
                            <q-btn 
                                v-if="hasPermission($page.props, 'user.manage') &&
                                    !isAdmin(props.row) &&
                                    props.row.id !== $page.props.auth.user.id"
                                flat 
                                round 
                                color="negative" 
                                icon="delete" 
                                size="sm"
                                @click="deleteUser(props.row.id)" 
                                title="Eliminar" 
                            />
                        </div>
                    </q-td>
                </template>
            </q-table>
        </q-page>
    </AuthenticatedLayout>
</template>

<script setup>
import EmailField from '@/Components/admin/EmailField.vue';
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import noImage from '@images/no-image-person.png';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { ref, computed } from 'vue';

const page = usePage();
const $q = useQuasar();

// Search functionality
const search = ref('');

const handleSearch = () => {
    // This will trigger the q-table's built-in filtering
    // The filter is applied automatically through the :filter prop
};

const customFilter = (rows, terms, cols, cellValue) => {
    const lowerTerms = terms.toLowerCase();
    return rows.filter(row => {
        return (
            (row.firstname && row.firstname.toLowerCase().includes(lowerTerms)) ||
            (row.lastname && row.lastname.toLowerCase().includes(lowerTerms)) ||
            (row.email && row.email.toLowerCase().includes(lowerTerms)) ||
            (row.schools && row.schools.some(school => 
                school.name && school.name.toLowerCase().includes(lowerTerms) ||
                school.short && school.short.toLowerCase().includes(lowerTerms)
            )) ||
            (row.roles && row.roles.some(role => 
                role.name && role.name.toLowerCase().includes(lowerTerms)
            ))
        );
    });
};

const props = defineProps({
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

// Available colors for schools
const schoolColorOptions = [
    'primary', 'secondary', 'accent', 'positive', 'negative', 
    'info', 'warning', 'deep-purple', 'purple', 'pink', 
    'red', 'orange', 'yellow', 'green', 'teal', 
    'cyan', 'blue', 'indigo', 'brown', 'grey'
];

// Create school colors mapping
const schoolColors = computed(() => {
    const colors = {};
    const uniqueSchools = new Set();
    
    // Extract all unique schools from users data
    if (props.users && props.users.data) {
        props.users.data.forEach(user => {
            if (user.schools && Array.isArray(user.schools)) {
                user.schools.forEach(school => {
                    uniqueSchools.add(school.id);
                });
            }
        });
    }
    
    // Assign colors to each unique school
    Array.from(uniqueSchools).forEach((schoolId, index) => {
        colors[schoolId] = schoolColorOptions[index % schoolColorOptions.length];
    });
    
    return colors;
});

// Table columns definition
const columns = [
    {
        name: 'photo',
        label: 'Foto',
        field: 'picture',
        align: 'center',
        sortable: false,
        style: 'width: 80px'
    },
    {
        name: 'name',
        required: true,
        label: 'Nombre',
        align: 'left',
        field: 'firstname',
        sortable: true
    },
    {
        name: 'last_name',
        required: true,
        label: 'Apellido',
        align: 'left',
        field: 'lastname',
        sortable: true
    },    {
        name: 'email',
        required: true,
        label: 'Email',
        align: 'left',
        field: 'email',
        sortable: true
    },
    {
        name: 'schools',
        label: 'Escuelas',
        field: 'schools',
        align: 'left',
        sortable: false
    },
    {
        name: 'roles',
        label: 'Roles',
        field: 'roles',
        align: 'left',
        sortable: false
    },
    {
        name: 'actions',
        label: 'Acciones',
        field: 'actions',
        align: 'center',
        sortable: false,
        classes: 'mll-table__cell-actions',
        headerClasses: 'mll-table__cell-actions-header',
        style: 'width: 150px'
    }
];

const deleteUser = (id) => {
    $q.dialog({
        title: 'Confirmar eliminación',
        message: '¿Está seguro de eliminar este usuario?',
        cancel: true,
        persistent: true
    }).onOk(() => {
        router.delete(route('users.destroy', id));
    });
};

const isAdmin = (user) => {
    return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
};

const isCurrentUserAdmin = () => {
    return isAdmin($page.props.auth.user);
};

const getUniqueRoles = (roles) => {
    return roles.filter((role, index, self) =>
        index === self.findIndex((r) => r.id === role.id)
    );
};
</script>
