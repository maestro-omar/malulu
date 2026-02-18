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
            <template v-slot:body-cell-show="props">
                <q-td :props="props">
                    <q-btn flat round dense icon="visibility" color="green" :href="buildShowFileUrl(props.row)"
                        title="Ver archivo" />
                </q-td>
            </template>
            <template v-slot:body-cell-edit="props">
                <q-td :props="props">
                    <q-btn flat round dense icon="edit" color="warning" :href="buildEditFileUrl(props.row)"
                        title="Editar archivo" />
                </q-td>
            </template>
            <template v-slot:body-cell-download="props">
                <q-td :props="props">
                    <q-btn 
                        flat 
                        round 
                        dense 
                        :icon="props.row.is_external ? 'open_in_new' : 'download'" 
                        color="primary" 
                        @click="props.row.is_external ? openExternalFile(props.row) : downloadFile(props.row)"
                        :title="props.row.is_external ? 'Abrir enlace externo' : 'Descargar archivo'" />
                </q-td>
            </template>
            <template v-slot:body-cell-nice_name="props">
                <q-td :props="props">
                    <div class="row items-center">
                        <q-icon 
                            :name="props.row.is_external ? 'link' : 'description'" 
                            :color="props.row.is_external ? 'orange' : 'grey-6'" 
                            size="sm" 
                            class="q-mr-sm" />
                        <span>{{ props.row.nice_name }}</span>
                        <q-chip 
                            v-if="props.row.is_external" 
                            size="xs" 
                            color="orange" 
                            text-color="white" 
                            class="q-ml-sm">
                            Externo
                        </q-chip>
                    </div>
                </q-td>
            </template>
            <template v-slot:body-cell-replace="props">
                <q-td :props="props">
                    <q-btn flat round dense icon="published_with_changes" color="teal"
                        :href="buildReplaceFileUrl(props.row)" title="Reemplazar archivo" />
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
    },
    showFileBaseUrl: {
        type: String,
        required: true
    },
    editFileBaseUrl: {
        type: String,
        required: true
    },
    replaceFileBaseUrl: {
        type: String,
        required: true
    },
    canDownload: {
        type: Boolean,
        default: true
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

// Open external file function
const openExternalFile = (file) => {
    if (file.url) {
        window.open(file.url, '_blank');
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

];
if (props.canDownload) {
    columns.push(
        {
            name: 'download',
            label: 'Acción',
            // field: 'download',
            align: 'center',
            sortable: false,
            style: 'width: 60px'
        });

}
if (props.showFileBaseUrl) {
    columns.push(
        {
            name: 'show',
            label: 'Detalles',
            // field: '',
            align: 'center',
            style: 'width: 60px'
        },
    );
}
if (props.replaceFileBaseUrl) {
    columns.push(
        {
            name: 'replace',
            label: 'Reemplazar',
            // field: 'replace',
            align: 'center',
            sortable: false,
            style: 'width: 60px'
        },
    );
}
if (props.editFileBaseUrl) {
    columns.push(
        {
            name: 'edit',
            label: 'Editar',
            // field: 'replace',
            align: 'center',
            sortable: false,
            style: 'width: 60px'
        },
    );
}
const buildShowFileUrl = (file) => {
    return props.showFileBaseUrl.replace('##', file.id);
}
const buildEditFileUrl = (file) => {
    return props.editFileBaseUrl.replace('##', file.id);
}
const buildReplaceFileUrl = (file) => {
    return props.replaceFileBaseUrl.replace('##', file.id);
}

</script>
