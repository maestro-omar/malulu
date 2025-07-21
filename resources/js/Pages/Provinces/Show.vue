<template>

    <Head :title="`Detalles de la Provincia: ${province.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalles de la Provincia: ${province.name}`" :edit="{
                show: hasPermission($page.props, 'province.manage'),
                href: route('provinces.edit', province.code),
                label: 'Editar'
            }" :del="{
                show: hasPermission($page.props, 'province.manage'),
                onClick: destroy,
                label: 'Eliminar'
            }" />
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Escudo
                                                provincial</label>
                                            <EditableImage v-model="province.logo1" type="logo1"
                                                :model-id="province.code" :can-edit="true"
                                                image-class="h-24 w-24 object-contain"
                                                upload-route="provinces.upload-image"
                                                delete-route="provinces.delete-image"
                                                delete-confirm-message="¿Está seguro que desea eliminar el escudo 1?" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Logo
                                                ministerio</label>
                                            <EditableImage v-model="province.logo2" type="logo2"
                                                :model-id="province.code" :can-edit="true"
                                                image-class="h-24 w-24 object-contain"
                                                upload-route="provinces.upload-image"
                                                delete-route="provinces.delete-image"
                                                delete-confirm-message="¿Está seguro que desea eliminar el escudo 2?" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Código</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ province.code }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Nombre</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ province.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Título</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ province.title }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Subtítulo</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ province.subtitle }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Enlace</label>
                                        <a v-if="province.link" :href="province.link" target="_blank"
                                            class="text-blue-600 underline">{{ province.link }}</a>
                                        <span v-else class="text-gray-500">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import EditableImage from '@/Components/admin/EditableImage.vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
    province: Object,
    breadcrumbs: Array
});


const destroy = () => {
    if (confirm('¿Está seguro que desea eliminar esta provincia?')) {
        router.delete(route('provinces.destroy', props.province.code));
    }
};

</script>