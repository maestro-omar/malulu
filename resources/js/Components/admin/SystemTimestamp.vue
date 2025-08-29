<template>
    <!-- System Information Expansion -->
    <q-expansion-item
        expand-separator
        icon="info"
        label="Información del Sistema"
        class="system-timestamp"
    >
        <div class="row q-col-gutter-sm q-mt-sm">
            <div class="col-12 col-sm-4 col-md-3">
                <DataFieldShow label="Fecha de Registro" :value="row.created_at" type="date" />
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <DataFieldShow label="Última Actualización" :value="row.updated_at" type="date" />
            </div>
            <div v-if="row.deleted_at" class="col-12 col-sm-4 col-md-3">
                <DataFieldShow label="Fecha de eliminación" :value="row.deleted_at" type="date" />
            </div>
        </div>
    </q-expansion-item>
</template>

<script setup>
import DataFieldShow from '@/Components/DataFieldShow.vue';

const props = defineProps({
    row: {
        type: Object,
    },
});
</script>
