<template>
    <div class="text-h4 q-mb-md">{{ title }}</div>
    <div class="row q-col-gutter-sm">
        <q-table v-if="files" class="mll-table mll-table--files striped-table" dense :rows="files" :columns="columns"
            row-key="id">
            <template v-slot:body-cell-download="props">
                <q-td :props="props">
                    <q-btn
                        flat
                        round
                        dense
                        icon="download"
                        color="primary"
                        @click="downloadFile(props.row)"
                        title="Descargar archivo"
                    />
                </q-td>
            </template>
        </q-table>
        <div v-else class="">Sin archivos</div>
    </div>
</template>

<script setup>
const props = defineProps({
    files: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        required: true
    },
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
