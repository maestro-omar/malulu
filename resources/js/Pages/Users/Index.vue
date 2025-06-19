<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import RoleBadge from '@/Components/admin/RoleBadge.vue';
import { ref, computed } from 'vue';
import noImage from '@images/no-image-person.png';

const props = defineProps({
    users: Object,
    flash: {
        type: Object,
        default: () => ({})
    }
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
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
                <div class="flex space-x-4">
                    <Link
                        v-if="$page.props.auth.user.can['create users']"
                        :href="route('users.create')"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    >
                        Nuevo Usuario
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.can['delete users']"
                        :href="route('users.trashed')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                    >
                        Usuarios Eliminados
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Debug Information -->
                <!-- <div class="mb-4 p-4 bg-gray-100 rounded">
                    <h3 class="font-bold mb-2">Debug Info:</h3>
                    <pre class="text-xs">{{ JSON.stringify(users, null, 2) }}</pre>
                </div> -->

                <!-- Flash Messages -->
                <div v-if="flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.error }}</span>
                </div>
                <div v-if="flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Desktop Table View -->
                        <div class="hidden md:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Foto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Escuelas
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Roles
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(user, index) in users.data"
                                        :key="user.id"
                                        :class="{
                                            'bg-indigo-50': user.id === $page.props.auth.user.id,
                                            'bg-gray-50': user.id !== $page.props.auth.user.id && index % 2 === 0,
                                            'bg-white': user.id !== $page.props.auth.user.id && index % 2 === 1
                                        }"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img :src="user.picture || noImage" 
                                                 :alt="user.name"
                                                 class="h-10 w-10 rounded-full object-cover" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                <span v-for="(school, index) in user.schools" :key="school.id" class="inline-block mr-2">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800" :title="school.name">
                                                        {{ school.short }}
                                                    </span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                <div class="flex flex-wrap gap-1">
                                                    <RoleBadge
                                                        v-for="role in getUniqueRoles(user.roles)"
                                                        :key="role.id"
                                                        :role="role"
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('users.show', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Ver
                                            </Link>
                                            <Link
                                                v-if="$page.props.auth.user.can['edit users'] && 
                                                      (!isAdmin(user) || (isAdmin(user) && user.id === $page.props.auth.user.id))"
                                                :href="route('users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                v-if="$page.props.auth.user.can['delete users'] && 
                                                      !isAdmin(user) && 
                                                      user.id !== $page.props.auth.user.id"
                                                @click="deleteUser(user.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4">
                            <div v-for="(user, index) in users.data"
                                :key="user.id"
                                :class="{
                                    'bg-indigo-50': user.id === $page.props.auth.user.id,
                                    'bg-gray-50': user.id !== $page.props.auth.user.id && index % 2 === 0,
                                    'bg-white': user.id !== $page.props.auth.user.id && index % 2 === 1
                                }"
                                class="rounded-lg shadow p-4"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center space-x-3">
                                        <img :src="user.picture || '/images/no-image.png'" 
                                             :alt="user.name"
                                             class="h-10 w-10 rounded-full object-cover" />
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">{{ user.name }}</h3>
                                            <p class="text-sm text-gray-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="route('users.show', user.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.can['edit users'] && 
                                                  (!isAdmin(user) || (isAdmin(user) && user.id === $page.props.auth.user.id))"
                                            :href="route('users.edit', user.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="$page.props.auth.user.can['delete users'] && 
                                                  !isAdmin(user) && 
                                                  user.id !== $page.props.auth.user.id"
                                            @click="deleteUser(user.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-xs font-medium text-gray-500 mb-1">Escuelas:</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="school in user.schools" 
                                              :key="school.id" 
                                              class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"
                                              :title="school.name">
                                            {{ school.short }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-xs font-medium text-gray-500 mb-1">Roles:</div>
                                    <div class="flex flex-wrap gap-1">
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
                        <div class="mt-4">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }} resultados
                                </div>
                                <Pagination :links="users.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 