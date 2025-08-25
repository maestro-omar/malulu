<template>
  <Head :title="`Detalles de la Provincia: ${province.name}`" />
  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Detalles de la Provincia: ${province.name}`" :edit="{
        show: hasPermission($page.props, 'province.manage'),
        href: route('provinces.edit', province.code),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'province.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }" />
    </template>
    <template #main-page-content>
      <div class="q-pa-md">
        <div class="row q-col-gutter-md">
          <div class="col-12">
            <q-card>
              <q-card-section>
                <div class="row q-col-gutter-lg">
                  <!-- Images Section -->
                  <div class="col-12 col-md-6">
                    <div class="text-h3 q-mb-md">Información de la Provincia</div>
                    <div class="row q-col-gutter-md q-mb-lg">
                      <div class="col-12 col-sm-6">
                        <DataFieldShow label="Escudo provincial">
                          <template #slotValue>
                            <EditableImage v-model="province.logo1" type="logo1" :model-id="province.code"
                              :can-edit="true" image-class="q-mt-sm" upload-route="provinces.upload-image"
                              delete-route="provinces.delete-image"
                              delete-confirm-message="¿Está seguro que desea eliminar el escudo 1?" />
                          </template>
                        </DataFieldShow>
                      </div>
                      <div class="col-12 col-sm-6">
                        <DataFieldShow label="Logo ministerio">
                          <template #slotValue>
                            <EditableImage v-model="province.logo2" type="logo2" :model-id="province.code"
                              :can-edit="true" image-class="q-mt-sm" upload-route="provinces.upload-image"
                              delete-route="provinces.delete-image"
                              delete-confirm-message="¿Está seguro que desea eliminar el escudo 2?" />
                          </template>
                        </DataFieldShow>
                      </div>
                    </div>
                    <div class="row q-col-gutter-md">
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Código" :value="province.code" type="text" />
                      </div>
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Nombre" :value="province.name" type="text" />
                      </div>
                    </div>
                  </div>

                  <!-- Details Section -->
                  <div class="col-12 col-md-6">
                    <div class="text-h3 q-mb-md">&nbsp;</div>
                    <div class="row q-col-gutter-md">
                      <div class="col-12">
                        <DataFieldShow label="Título" :value="province.title" type="text" />
                      </div>
                      <div class="col-12">
                        <DataFieldShow label="Subtítulo" :value="province.subtitle" type="text" />
                      </div>
                      <div class="col-12">
                        <DataFieldShow label="Enlace">
                          <template #slotValue>
                            <a v-if="province.link" :href="province.link" target="_blank"
                              class="text-primary">{{ province.link }}</a>
                            <span v-else>-</span>
                          </template>
                        </DataFieldShow>
                      </div>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import EditableImage from '@/Components/admin/EditableImage.vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  province: Object,
});

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta provincia?')) {
    router.delete(route('provinces.destroy', props.province.code));
  }
};
</script>