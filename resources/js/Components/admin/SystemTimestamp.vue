<template>
    <!-- System Information Card -->
    <q-card>
        <q-card-section>
            <div class="text-h4 q-mb-xs">Información del Sistema</div>
            <div class="row q-col-gutter-sm">
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
        </q-card-section>
    </q-card>

</template>

<script setup>
import DataFieldShow from '@/Components/DataFieldShow.vue';

const props = defineProps({
    row: {
        type: Object,
    },
});
</script>
