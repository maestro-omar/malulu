<template>

    <Head title="Tipos de Archivo" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Tipos de Archivo`" :add="{
                show: hasPermission($page.props, 'superadmin', null),
                href: route('file-types.create'),
                label: 'Nuevo'
            }">
            </AdminHeader>
        </template>

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :error="flash?.error" :success="flash?.success" />

            <div class="table__wrapper">
                <div class="table__container">
                    <!-- Desktop Table View -->
                    <div class="table__desktop">
                        <table class="table__table">
                            <thead class="table__thead">
                                <tr>
                                    <th class="table__th">Clave</th>
                                    <th class="table__th">Nombre</th>
                                    <th class="table__th">Relacionado Con</th>
                                    <th class="table__th">Estado</th>
                                    <th class="table__th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table__tbody">
                                <tr
                                    v-for="(fileType, index) in fileTypes"
                                    :key="fileType.id"
                                    :class="{
                                        'table__tr--even': index % 2 === 0,
                                        'table__tr--odd': index % 2 === 1
                                    }"
                                >
                                    <td class="table__td">{{ fileType.code }}</td>
                                    <td class="table__td">{{ fileType.name }}</td>
                                    <td class="table__td">{{ fileType.relate_with }}</td>
                                    <td class="table__td">
                                        <span :class="{
                                            'table__status': true,
                                            'table__status--active': fileType.active,
                                            'table__status--inactive': !fileType.active,
                                        }">
                                            {{ fileType.active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="table__td table__actions">
                                        <Link :href="route('file-types.edit', fileType.id)">
                                            Editar
                                        </Link>
                                        <button v-if="fileType.can_delete" @click="deleteFileType(fileType.id)">
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
                            v-for="(fileType, index) in fileTypes"
                            :key="fileType.id"
                            :class="{
                                'table__card--even': index % 2 === 0,
                                'table__card--odd': index % 2 === 1
                            }"
                            class="table__card"
                        >
                            <div class="table__card-header">
                                <div class="table__card-user">
                                    <div class="table__card-info">
                                        <h3>{{ fileType.name }}</h3>
                                        <p>{{ fileType.code }}</p>
                                    </div>
                                </div>
                                <div class="table__card-actions">
                                    <Link :href="route('file-types.edit', fileType.id)">
                                        Editar
                                    </Link>
                                    <button v-if="fileType.can_delete" @click="deleteFileType(fileType.id)">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                            <div class="table__card-section">
                                <div class="table__card-label">Relacionado Con:</div>
                                <div class="table__card-content">{{ fileType.relate_with }}</div>
                            </div>
                            <div class="table__card-section">
                                <div class="table__card-label">Estado:</div>
                                <div class="table__card-content">
                                    <span :class="{
                                        'table__status': true,
                                        'table__status--active': fileType.active,
                                        'table__status--inactive': !fileType.active,
                                    }">
                                        {{ fileType.active ? 'Activo' : 'Inactivo' }}
                                    </span>
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
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';
import FlashMessages from '@/Components/admin/FlashMessages.vue';

defineProps({
    fileTypes: Array,
    breadcrumbs: Array
});

const deleteFileType = (id) => {
    if (confirm('¿Está seguro que desea eliminar este tipo de archivo?')) {
        router.delete(route('file-types.destroy', id));
    }
};
</script>