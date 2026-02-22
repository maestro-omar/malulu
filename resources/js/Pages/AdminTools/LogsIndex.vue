<template>
  <Head title="Laravel LOG" />

  <AuthenticatedLayout title="Laravel LOG">
    <template #main-page-content>
      <q-card class="q-pa-md">
        <q-card-section class="row items-center justify-between q-pb-md">
          <div class="text-h6">
            <q-icon name="description" class="q-mr-sm" />
            storage/logs/laravel.log
          </div>
          <div class="row q-gutter-sm">
            <q-btn
              flat
              round
              color="primary"
              icon="refresh"
              title="Recargar"
              :loading="reloading"
              @click="refresh"
            />
            <q-btn
              v-if="exists"
              flat
              round
              color="negative"
              icon="delete"
              title="Eliminar archivo"
              :loading="deleting"
              @click="confirmDelete"
            />
          </div>
        </q-card-section>

        <q-card-section v-if="!exists" class="text-grey-7">
          <q-banner rounded class="bg-grey-3">
            El archivo de log no existe o está vacío.
          </q-banner>
        </q-card-section>

        <q-card-section v-else>
          <q-banner v-if="truncated" rounded class="bg-warning q-mb-md" dense>
            Se muestran solo los últimos 512 KB del archivo.
          </q-banner>
          <pre
            class="q-pa-md bg-grey-2 rounded-borders scroll"
            style="max-height: 70vh; overflow: auto; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4;"
          >{{ content || '(vacío)' }}</pre>
        </q-card-section>
      </q-card>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';

const $q = useQuasar();
const $page = usePage();

const props = defineProps({
  content: { type: String, default: null },
  exists: { type: Boolean, default: false },
  truncated: { type: Boolean, default: false },
  path: { type: String, default: '' },
});

const reloading = ref(false);
const deleting = ref(false);

// Show flash message from redirect (e.g. after delete)
watch(
  () => $page.props.flash,
  (flash) => {
    if (flash?.message) {
      $q.notify({
        type: flash.type === 'positive' ? 'positive' : flash.type === 'negative' ? 'negative' : 'info',
        message: flash.message,
      });
    }
  },
  { immediate: true }
);

function refresh() {
  reloading.value = true;
  router.reload({ preserveState: false, onFinish: () => { reloading.value = false; } });
}

function confirmDelete() {
  $q.dialog({
    title: 'Eliminar archivo de log',
    message: 'Se eliminará storage/logs/laravel.log. Laravel lo volverá a crear al escribir nuevos logs. ¿Continuar?',
    cancel: true,
    persistent: true,
  }).onOk(() => {
    deleting.value = true;
    router.delete(route('logs.destroy'), {
      onFinish: () => { deleting.value = false; },
    });
  });
}
</script>
