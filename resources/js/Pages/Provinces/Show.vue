<template>

    <Head :title="`Detalles de la Provincia: ${province.name}`" />
    <AuthenticatedLayout>
        <template #admin-header>
            <AdminHeader  :title="`Detalles de la Provincia: ${province.name}`" :edit="{
                show: hasPermission($page.props, 'province.manage'),
                href: route('provinces.edit', province.code),
                label: 'Editar'
            }" :del="{
                show: hasPermission($page.props, 'province.manage'),
                onClick: destroy,
                label: 'Eliminar'
            }" />
        </template>
        <template #main-page-content>
            <div class="province-show">
                <div class="province-show__container">
                    <div class="province-show__card">
                        <div class="province-show__content">
                            <div class="province-show__grid">
                                <div class="province-show__section">
                                    <div class="province-show__field-grid">
                                        <div class="province-show__field">
                                            <label class="province-show__label">Escudo provincial</label>
                                            <EditableImage v-model="province.logo1" type="logo1" :model-id="province.code"
                                                :can-edit="true" image-class="province-show__image"
                                                upload-route="provinces.upload-image" delete-route="provinces.delete-image"
                                                delete-confirm-message="¿Está seguro que desea eliminar el escudo 1?" />
                                        </div>
                                        <div class="province-show__field">
                                            <label class="province-show__label">Logo ministerio</label>
                                            <EditableImage v-model="province.logo2" type="logo2" :model-id="province.code"
                                                :can-edit="true" image-class="province-show__image"
                                                upload-route="provinces.upload-image" delete-route="provinces.delete-image"
                                                delete-confirm-message="¿Está seguro que desea eliminar el escudo 2?" />
                                        </div>
                                        <div class="province-show__field">
                                            <label class="province-show__label">Código</label>
                                            <p class="province-show__value">{{ province.code }}</p>
                                        </div>
                                        <div class="province-show__field">
                                            <label class="province-show__label">Nombre</label>
                                            <p class="province-show__value">{{ province.name }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="province-show__section">
                                    <div class="province-show__field">
                                        <label class="province-show__label">Título</label>
                                        <p class="province-show__value">{{ province.title }}</p>
                                    </div>
                                    <div class="province-show__field">
                                        <label class="province-show__label">Subtítulo</label>
                                        <p class="province-show__value">{{ province.subtitle }}</p>
                                    </div>
                                    <div class="province-show__field">
                                        <label class="province-show__label">Enlace</label>
                                        <a v-if="province.link" :href="province.link" target="_blank"
                                            class="province-show__link">{{
                                                province.link }}</a>
                                        <span v-else class="province-show__empty">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>

<script setup>
import EditableImage from '@/Components/admin/EditableImage.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router } from '@inertiajs/vue3';

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