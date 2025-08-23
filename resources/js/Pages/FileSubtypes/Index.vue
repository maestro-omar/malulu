<template>

    <Head title="Subtipos de Archivo" />

    <AuthenticatedLayout title="Subtipos de Archivo">
        <AdminHeader :breadcrumbs="breadcrumbs" title="Subtipos de Archivo" :add="{
            show: hasPermission($page.props, 'superadmin', null),
            href: route('file-subtypes.create'),
            label: 'Nuevo'
        }">
        </AdminHeader>
        <q-page class="q-pa-md">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <!-- Quasar Table -->
            <q-table class="mll-table mll-table--file-subtypes striped-table" dense :rows="fileSubtypes"
                :columns="columns" row-key="id" :pagination="{ rowsPerPage: 30 }">
                <!-- Custom header -->


                <!-- Custom cell for actions -->
                <template #body-cell-actions="props">
                    <q-td :props="props">
                        <div class="row items-center q-gutter-sm">
                            <q-btn flat round color="secondary" icon="edit" size="sm"
                                :href="route('file-subtypes.edit', props.row.id)" title="Editar" />
                            <q-btn v-if="props.row.can_delete" flat round color="negative" icon="delete" size="sm"
                                @click="deleteFileSubtype(props.row.id)" title="Eliminar" />
                        </div>
                    </q-td>
                </template>
            </q-table>
        </q-page>
    </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue'
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const page = usePage();
const $q = useQuasar();

defineProps({
    fileSubtypes: Array,
    breadcrumbs: Array,
    flash: Object || null
});

// Table columns definition
const columns = [
    {
        name: 'file_type',
        required: true,
        label: 'Tipo',
        align: 'left',
        field: 'file_type',
        sortable: true
    },
    {
        name: 'name',
        required: true,
        label: 'Nombre',
        align: 'left',
        field: 'name',
        sortable: true
    },
    {
        name: 'description',
        label: 'Descripción',
        field: 'description',
        align: 'left',
        sortable: false
    },
    {
        name: 'order',
        label: 'Orden',
        field: 'order',
        align: 'center',
        sortable: true,
        style: 'width: 80px'
    },
    {
        name: 'actions',
        label: 'Acciones',
        field: 'actions',
        align: 'center',
        sortable: false,
        classes: 'mll-table__cell-actions',
        headerClasses: 'mll-table__cell-actions-header',
        style: 'width: 120px'
    }
];

const deleteFileSubtype = (id) => {
    $q.dialog({
        title: 'Confirmar eliminación',
        message: '¿Está seguro que desea eliminar este subtipo de archivo?',
        cancel: true,
        persistent: true
    }).onOk(() => {
        router.delete(route('file-subtypes.destroy', id));
    });
};
</script>