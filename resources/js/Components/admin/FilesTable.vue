<template>
    <q-expansion-item expand-separator default-opened class="mll-table-expansion q-mt-md">
        <template v-slot:header>
            <q-item-section avatar>
                <q-icon name="business_center" size="sm" color="accent" />
            </q-item-section>

            <q-item-section align="left">
                {{ title }}
            </q-item-section>

            <q-item-section avatar v-if="newFileUrl">
                <q-btn size="sm" padding="sm" dense icon="add" color="green" :href="newFileUrl" title="Agregar archivo">
                    Agregar archivo
                </q-btn>
            </q-item-section>
        </template>
        <q-table v-if="files" class="mll-table mll-table--files striped-table" dense :rows="files" :columns="columns"
            row-key="id">
            <template v-slot:body-cell-download="props">
                <q-td :props="props">
                    <q-btn flat round dense icon="download" color="primary" @click="downloadFile(props.row)"
                        title="Descargar archivo" />
                </q-td>
            </template>
        </q-table>
        <div v-else class="">Sin archivos</div>
    </q-expansion-item>
</template>

<script setup>
const props = defineProps({
    files: {
        type: Object,
    },
    title: {
        type: String,
        required: true
    },
    context: {
        type: String,
        default: 'user'
    },
    newFileUrl: {
        type: String,
        required: false
    }
});

// Download file function
const downloadFile = (file) => {
    if (file.url) {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.original_name || 'download';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

// Table columns definition
const columns = [
    {
        name: 'type',
        label: 'Tipo',
        field: 'type',
        align: 'left',
        sortable: true,
        style: 'width: 120px'
    },
    {
        name: 'subtype',
        label: 'Subtipo',
        field: 'subtype',
        align: 'left',
        sortable: true,
        style: 'width: 180px'
    },
    {
        name: 'nice_name',
        label: 'Archivo',
        field: 'nice_name',
        align: 'left',
        sortable: true,
        style: 'width: 180px'
    },
    {
        name: 'description',
        label: 'Descripción',
        field: 'description',
        align: 'left',
        sortable: false
    },
    {
        name: 'replaced_by',
        label: 'Reemplaza...',
        field: 'replaces',
        align: 'left',
        sortable: true,
        style: 'width: 80px'
    },
    {
        name: 'created_by',
        label: 'Creado por',
        field: 'created_by',
        align: 'left',
        sortable: true,
        style: 'width: 120px'
    },
    {
        name: 'created_at',
        label: 'Creado el',
        field: 'created_at',
        required: true,
        align: 'center',
        sortable: true,
        style: 'width: 80px'
    },
    {
        name: 'download',
        label: 'Descargar',
        field: 'download',
        align: 'center',
        sortable: false,
        style: 'width: 60px'
    },
];

</script>
