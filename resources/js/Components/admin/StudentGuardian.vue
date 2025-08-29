<template>
    <div :class="['student-guardian', { 'student-guardian--restricted': guardian.is_restricted }]">
        <div class="student-guardian__title">
            <DataFieldShow :label="toTitleCase(guardian.relationship_type)">
                <template #slotValue>
                    <a href="javascript:void(0);" class="student-guardian__title-link"
                        @click.prevent="showDialog = true"
                        >
                        {{ guardian.firstname }} {{ guardian.lastname }}
                    </a>
                </template>
            </DataFieldShow>
        </div>
        <q-dialog v-model="showDialog" class="!max-w-none !w-[800px]">
            <q-card class="student-guardian__popup">
                <q-card-section>
                    <div class="text-h6">
                        <span v-if="guardian.is_restricted"
                            class="student-guardian__restricted q-mr-xs">RESTRICCIÓN</span>
                        {{ guardian.firstname }} {{ guardian.lastname }} ({{ guardian.relationship_type }})
                    </div>
                </q-card-section>
                <q-card-section>
                    <div class="row q-col-gutter-sm">
                        <div class="col-3" v-if="guardian.is_emergency_contact !== undefined">
                            <DataFieldShow label="Contacto de emergencia"
                                :value="guardian.is_emergency_contact ? 'Sí' : 'No'" type="text" />
                        </div>
                        <div class="col-3"
                            v-if="guardian.emergency_contact_priority !== undefined && guardian.emergency_contact_priority !== null">
                            <DataFieldShow label="Prioridad de emergencia" :value="guardian.emergency_contact_priority"
                                type="text" />
                        </div>
                        <div class="col-12">
                            <DataFieldShow label="Teléfono">
                                <template #slotValue>
                                    <PhoneField :phone="guardian.phone" />
                                </template>
                            </DataFieldShow>
                        </div>
                        <div class="col-12">
                            <DataFieldShow label="Email">
                                <template #slotValue>
                                    <EmailField :email="guardian.email" />
                                </template>
                            </DataFieldShow>
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="DNI" :value="guardian.id_number" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="Género" :value="guardian.gender" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="Dirección" :value="guardian.address" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="Localidad" :value="guardian.locality" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="Provincia" :value="guardian.province" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="País" :value="guardian.country" type="text" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <DataFieldShow label="Nacionalidad" :value="guardian.nationality" type="text" />
                        </div>
                        <div class="col-12 col-sm-6" v-if="guardian.birthdate">
                            <DataFieldShow label="Fecha de Nacimiento" :value="guardian.birthdate" type="date" />
                        </div>
                    </div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cerrar" color="primary" v-close-popup />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import { toTitleCase } from '@/Utils/strings';

const props = defineProps({
    guardian: {
        type: Object,
    },
});

const showDialog = ref(false);
</script>
