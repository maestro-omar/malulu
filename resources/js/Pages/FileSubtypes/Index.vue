<template>

    <Head title="Subtipos de Archivo" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Subtipos de Archivo`" :add="{
                show: hasPermission($page.props, 'superadmin', null),
                href: route('file-subtypes.create'),
                label: 'Nuevo'
            }">
            </AdminHeader>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Descripción</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Orden</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="fileSubtype in fileSubtypes" :key="fileSubtype.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.file_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.name }}</td>
                                        <td class="px-6 py-4">{{ fileSubtype.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ fileSubtype.order }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('file-subtypes.edit', fileSubtype.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4">
                                            Editar
                                            </Link>
                                            <button v-if="fileSubtype.can_delete"
                                                @click="deleteFileSubtype(fileSubtype.id)"
                                                class="text-red-600 hover:text-red-900">
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
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

defineProps({
    fileSubtypes: Array,
    breadcrumbs: Array
});

const deleteFileSubtype = (id) => {
    if (confirm('¿Está seguro que desea eliminar este subtipo de archivo?')) {
        router.delete(route('file-subtypes.destroy', id));
    }
};
</script>