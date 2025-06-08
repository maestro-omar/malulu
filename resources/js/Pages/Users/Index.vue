<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    flash: {
        type: Object,
        default: () => ({})
    }
});

// Role color mapping
const roleColors = {
    'admin': 'bg-purple-100 text-purple-800',
    'director': 'bg-blue-100 text-blue-800',
    'regent': 'bg-green-100 text-green-800',
    'secretary': 'bg-yellow-100 text-yellow-800',
    'professor': 'bg-indigo-100 text-indigo-800',
    'grade_teacher': 'bg-pink-100 text-pink-800',
    'assistant_teacher': 'bg-orange-100 text-orange-800',
    'curricular_teacher': 'bg-teal-100 text-teal-800',
    'special_teacher': 'bg-cyan-100 text-cyan-800',
    'class_assistant': 'bg-emerald-100 text-emerald-800',
    'librarian': 'bg-violet-100 text-violet-800',
    'guardian': 'bg-rose-100 text-rose-800',
    'student': 'bg-sky-100 text-sky-800',
    'cooperative': 'bg-amber-100 text-amber-800',
    'former_student': 'bg-slate-100 text-slate-800'
};

// Get color class for a role
const getRoleColor = (role) => {
    return roleColors[role.key] || 'bg-gray-100 text-gray-800';
};

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
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
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
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                                    {{ school.name }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <span v-for="(role, index) in user.roles" :key="role.id" class="inline-block mr-2">
                                                <span :class="['px-2 py-1 text-xs rounded-full', getRoleColor(role)]" :title="role.name">
                                                    {{ role.short }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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