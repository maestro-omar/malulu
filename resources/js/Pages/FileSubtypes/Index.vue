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

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <div class="table__wrapper">
                <div class="table__container">
                    <!-- Desktop Table View -->
                    <div class="table__desktop">
                        <table class="table__table">
                            <thead class="table__thead">
                                <tr>
                                    <th class="table__th">Tipo</th>
                                    <th class="table__th">Nombre</th>
                                    <th class="table__th">Descripción</th>
                                    <th class="table__th">Orden</th>
                                    <th class="table__th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table__tbody">
                                <tr v-for="(fileSubtype, index) in fileSubtypes" :key="fileSubtype.id" :class="{
                                    'table__tr--even': index % 2 === 0,
                                    'table__tr--odd': index % 2 === 1
                                }">
                                    <td class="table__td table__type">{{ fileSubtype.file_type }}</td>
                                    <td class="table__td table__name">{{ fileSubtype.name }}</td>
                                    <td class="table__td table__description">{{ fileSubtype.description }}</td>
                                    <td class="table__td table__order">{{ fileSubtype.order }}</td>
                                    <td class="table__td table__actions">
                                        <Link :href="route('file-subtypes.edit', fileSubtype.id)">
                                        Editar
                                        </Link>
                                        <button v-if="fileSubtype.can_delete"
                                            @click="deleteFileSubtype(fileSubtype.id)">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="table__mobile">
                        <div v-for="(fileSubtype, index) in fileSubtypes" :key="fileSubtype.id" :class="{
                            'table__card--even': index % 2 === 0,
                            'table__card--odd': index % 2 === 1
                        }" class="table__card">
                            <div class="table__card-header">
                                <div class="table__card-user">
                                    <div class="table__card-info">
                                        <h3>{{ fileSubtype.name }}</h3>
                                        <p>{{ fileSubtype.file_type }}</p>
                                    </div>
                                </div>
                                <div class="table__card-actions">
                                    <Link :href="route('file-subtypes.edit', fileSubtype.id)">
                                    Editar
                                    </Link>
                                    <button v-if="fileSubtype.can_delete" @click="deleteFileSubtype(fileSubtype.id)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                            <div class="table__card-section">
                                <div class="table__card-label">Descripción:</div>
                                <div class="table__card-content">{{ fileSubtype.description }}</div>
                            </div>
                            <div class="table__card-section">
                                <div class="table__card-label">Orden:</div>
                                <div class="table__card-content">{{ fileSubtype.order }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';
import { Head, Link, router } from '@inertiajs/vue3';

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