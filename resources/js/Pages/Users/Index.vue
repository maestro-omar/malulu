<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import { ref, computed, watch } from 'vue';
import noImage from '@images/no-image-person.png';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';
import EmailField from '@/Components/admin/EmailField.vue';

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

const search = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            route('users.index'),
            { search: search.value },
            { preserveState: true, preserveScroll: true }
        );
    }, 300);
};

const clearSearch = () => {
    search.value = '';
    router.get(
        route('users.index'),
        {},
        { preserveState: true, preserveScroll: true }
    );
};

// Watch for changes in search
watch(search, () => {
    handleSearch();
});

// Debug log when component receives props
console.log('Users data received:', props.users);

const deleteUser = (id) => {
    if (confirm('¿Está seguro de eliminar este usuario?')) {
        router.delete(route('users.destroy', id));
    }
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

<template>

    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader 
                :breadcrumbs="breadcrumbs" 
                :title="`Listado de usuarios`" 
                :add="{
                    show: hasPermission($page.props, 'user.manage'),
                    href: route('users.create'),
                    label: 'Nuevo usuario'
                }" 
                :trashed="{
                    show: hasPermission($page.props, 'user.manage'),
                    href: route('users.trashed'),
                    label: 'Eliminados'
                }"
            />
        </template>

        <div class="container">
                <!-- Flash Messages -->
                <div v-if="flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.error }}</span>
                </div>
                <div v-if="flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>

                <div class="table__wrapper">
                    <div class="table__container">
                        <!-- Search Filter -->
                        <div class="table__search">
                            <input 
                                type="text" 
                                v-model="search" 
                                @input="handleSearch"
                                placeholder="Buscar usuarios..."
                            />
                            <button v-if="search" @click="clearSearch" class="table__search-clear">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="table__desktop">
                            <table class="table__table">
                                <thead class="table__thead">
                                    <tr>
                                        <th class="table__th">Foto</th>
                                        <th class="table__th">Nombre</th>
                                        <th class="table__th">Email</th>
                                        <th class="table__th">Escuelas</th>
                                        <th class="table__th">Roles</th>
                                        <th class="table__th">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="table__tbody">
                                    <tr 
                                        v-for="(user, index) in users.data" 
                                        :key="user.id" 
                                        :class="{
                                            'table__tr--highlighted': user.id === $page.props.auth.user.id,
                                            'table__tr--even': user.id !== $page.props.auth.user.id && index % 2 === 0,
                                            'table__tr--odd': user.id !== $page.props.auth.user.id && index % 2 === 1
                                        }"
                                    >
                                        <td class="table__td table__photo">
                                            <img :src="user.picture || noImage" :alt="user.name" />
                                        </td>
                                        <td class="table__td table__name">
                                            {{ user.name }}
                                        </td>
                                        <td class="table__td table__email">
                                            <EmailField :email="user.email" />
                                        </td>
                                        <td class="table__td table__schools">
                                            <span 
                                                v-for="school in user.schools" 
                                                :key="school.id"
                                                class="table__badge"
                                                :title="school.name"
                                            >
                                                {{ school.short }}
                                            </span>
                                        </td>
                                        <td class="table__td table__roles">
                                            <RoleBadge 
                                                v-for="role in getUniqueRoles(user.roles)" 
                                                :key="role.id"
                                                :role="role" 
                                            />
                                        </td>
                                        <td class="table__td table__actions">
                                            <Link :href="route('users.show', user.id)">
                                                Ver
                                            </Link>
                                            <Link
                                                v-if="hasPermission($page.props, 'user.manage') &&
                                                    (!isAdmin(user) || (isAdmin(user) && user.id === $page.props.auth.user.id))"
                                                :href="route('users.edit', user.id)"
                                            >
                                                Editar
                                            </Link>
                                            <button 
                                                v-if="hasPermission($page.props, 'user.manage') &&
                                                    !isAdmin(user) &&
                                                    user.id !== $page.props.auth.user.id" 
                                                @click="deleteUser(user.id)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="table__mobile">
                            <div 
                                v-for="(user, index) in users.data" 
                                :key="user.id" 
                                :class="{
                                    'table__card--highlighted': user.id === $page.props.auth.user.id,
                                    'table__card--even': user.id !== $page.props.auth.user.id && index % 2 === 0,
                                    'table__card--odd': user.id !== $page.props.auth.user.id && index % 2 === 1
                                }" 
                                class="table__card"
                            >
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
                                        <Link
                                            v-if="hasPermission($page.props, 'user.manage') &&
                                                (!isAdmin(user) || (isAdmin(user) && user.id === $page.props.auth.user.id))"
                                            :href="route('users.edit', user.id)"
                                        >
                                            Editar
                                        </Link>
                                        <button 
                                            v-if="hasPermission($page.props, 'user.manage') &&
                                                !isAdmin(user) &&
                                                user.id !== $page.props.auth.user.id" 
                                            @click="deleteUser(user.id)"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                                <div class="table__card-section">
                                    <div class="table__card-label">Escuelas:</div>
                                    <div class="table__card-content">
                                        <span 
                                            v-for="school in user.schools" 
                                            :key="school.id"
                                            class="table__badge"
                                            :title="school.name"
                                        >
                                            {{ school.short }}
                                        </span>
                                    </div>
                                </div>
                                <div class="table__card-section">
                                    <div class="table__card-label">Roles:</div>
                                    <div class="table__card-content">
                                        <RoleBadge 
                                            v-for="role in getUniqueRoles(user.roles)" 
                                            :key="role.id"
                                            :role="role" 
                                        />
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