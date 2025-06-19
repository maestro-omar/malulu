<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import Pagination from '@/Components/admin/Pagination.vue';

const props = defineProps({
    users: Object,
    flash: {
        type: Object,
        default: () => ({})
    }
});

const restoreUser = (id) => {
    if (confirm('¿Está seguro de restaurar este usuario?')) {
        router.post(route('users.restore', id));
    }
};

const forceDeleteUser = (id) => {
    if (confirm('¿Está seguro de eliminar permanentemente este usuario? Esta acción no se puede deshacer.')) {
        router.delete(route('users.force-delete', id));
    }
};
</script>

<template>
    <Head title="Usuarios Eliminados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-red-600 leading-tight">Usuarios Eliminados</h2>
                <Link
                    :href="route('users.index')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                >
                    Volver a Usuarios
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">
                                            Roles
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">
                                            Eliminado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(user, index) in users.data" 
                                        :key="user.id"
                                        :class="{
                                            'bg-red-50': index % 2 === 0,
                                            'bg-white': index % 2 === 1
                                        }">
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
                                                {{ user.roles.map(role => role.name).join(', ') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-red-600">
                                                {{ new Date(user.deleted_at).toLocaleDateString() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button
                                                v-if="$page.props.auth.user.can['delete users']"
                                                @click="restoreUser(user.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Restaurar
                                            </button>
                                            <button
                                                v-if="$page.props.auth.user.can['delete users']"
                                                @click="forceDeleteUser(user.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Eliminar Permanentemente
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
                                    'bg-red-50': index % 2 === 0,
                                    'bg-white': index % 2 === 1
                                }"
                                class="rounded-lg shadow p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">{{ user.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            v-if="$page.props.auth.user.can['delete users']"
                                            @click="restoreUser(user.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Restaurar
                                        </button>
                                        <button
                                            v-if="$page.props.auth.user.can['delete users']"
                                            @click="forceDeleteUser(user.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-xs font-medium text-gray-500 mb-1">Roles:</div>
                                    <div class="text-sm text-gray-500">
                                        {{ user.roles.map(role => role.name).join(', ') }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-xs font-medium text-gray-500 mb-1">Eliminado:</div>
                                    <div class="text-sm text-red-600">
                                        {{ new Date(user.deleted_at).toLocaleDateString() }}
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