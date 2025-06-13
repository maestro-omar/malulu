<template>
    <Head title="Subtipos de Archivo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Subtipos de Archivo</h2>
                <Link
                    :href="route('file-subtypes.create')"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                >
                    Agregar Nuevo Subtipo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="fileSubtype in fileSubtypes" :key="fileSubtype.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.file_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.name }}</td>
                                        <td class="px-6 py-4">{{ fileSubtype.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.order }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('file-subtypes.edit', fileSubtype.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                v-if="fileSubtype.can_delete"
                                                @click="deleteFileSubtype(fileSubtype.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Eliminar
                                            </button>
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

defineProps({
    fileSubtypes: Array
});

const deleteFileSubtype = (id) => {
    if (confirm('¿Está seguro que desea eliminar este subtipo de archivo?')) {
        router.delete(route('file-subtypes.destroy', id));
    }
};
</script> 