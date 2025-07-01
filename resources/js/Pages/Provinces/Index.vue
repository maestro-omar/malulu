<template>
    <Head title="Provincias" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Provincias`" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escudo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="province in provinces" :key="province.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img v-if="province.logo1" :src="province.logo1" alt="Escudo" class="h-8 w-8 object-contain" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ province.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ province.title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('provinces.show', province.code)" class="text-blue-600 hover:text-blue-900 mr-4">Ver</Link>
                                            <Link :href="route('provinces.edit', province.code)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                            <button @click="deleteProvince(province.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';

defineProps({
    provinces: Array,
    breadcrumbs: Array
});

const deleteProvince = (id) => {
    if (confirm('¿Está seguro que desea eliminar esta provincia?')) {
        router.delete(route('provinces.destroy', id));
    }
};
</script> 