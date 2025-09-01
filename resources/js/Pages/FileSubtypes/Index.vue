<template>

    <Head title="Subtipos de Archivo" />

    <AuthenticatedLayout title="Subtipos de Archivo">
        <template #admin-header>
            <AdminHeader title="Subtipos de Archivo" :add="{
                show: hasPermission($page.props, 'superadmin', null),
                href: route('file-subtypes.create'),
                label: 'Nuevo'
            }">
            </AdminHeader>
        </template>

        <template #main-page-content>
            <!-- Quasar Table -->
            <q-table class="mll-table mll-table--small-width striped-table" dense :rows="fileSubtypes"
                :columns="columns" row-key="id" :pagination="{ rowsPerPage: 30 }">
                <!-- Custom cell for actions -->
                <template #body-cell-actions="props">
                    <q-td :props="props">
                        <div class="row items-center q-gutter-sm">
                            <!-- View button - always visible -->
                            <!-- <q-btn 
                                flat 
                                round 
                                color="primary" 
                                icon="visibility" 
                                size="sm"
                                :href="route('file-subtypes.show', props.row.id)" 
                                title="Ver" 
                            /> -->

                            <!-- Edit button - always visible -->
                            <q-btn flat round color="warning" icon="edit" size="sm"
                                :href="route('file-subtypes.edit', props.row.id)" title="Editar" />

                            <!-- Delete button - always visible but disabled when not allowed -->
                            <q-btn flat round :color="props.row.can_delete ? 'negative' : 'grey'" icon="delete"
                                size="sm" :disable="!props.row.can_delete"
                                @click="props.row.can_delete ? deleteFileSubtype(props.row.id) : null"
                                :title="props.row.can_delete ? 'Eliminar' : 'No se puede eliminar este subtipo de archivo'" />
                        </div>
                    </q-td>
                </template>
            </q-table>
        </template>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue'
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $page = usePage();
const $q = useQuasar();

defineProps({
    fileSubtypes: Array,


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
        style: 'width: 60px'
    },
    {
        name: 'actions',
        label: 'Acciones',
        field: 'actions',
        align: 'center',
        sortable: false,
        classes: 'mll-table__cell-actions',
        headerClasses: 'mll-table__cell-actions-header',
        style: 'width: 150px'
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