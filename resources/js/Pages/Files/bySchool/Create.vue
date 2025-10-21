<template>
  <Head :title="`Nuevo archivo para escuela`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Nuevo archivo para escuela`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <div class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__section">
                    <h3 class="admin-form__card-title">
                      <q-icon name="add" class="q-mr-sm" />
                      Crear nuevo archivo
                    </h3>
                    <div class="admin-form__card-content">
                      <div class="text-subtitle2 q-mb-lg text-grey-6">
                        Agregue un archivo o enlace externo para esta escuela
                      </div>
                      
                      <FileForm
                        :sub-types="subTypes"
                        :context="context"
                        :context-id="contextId"
                        :store-url="storeUrl"
                        :cancel-url="cancelUrl"
                        @success="onSuccess"
                        @error="onError"
                      />
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
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import FileForm from '@/Components/admin/FileForm.vue'

const props = defineProps({
  subTypes: {
    type: Array,
    required: true
  },
  context: {
    type: String,
    required: true
  },
  contextId: {
    type: [String, Number],
    required: true
  },
  storeUrl: {
    type: String,
    required: true
  },
  cancelUrl: {
    type: String,
    required: true
  }
})

const onSuccess = (page) => {
  // The FileForm component will handle the redirect through the controller
  // This is just a fallback in case we need to do something additional
}

const onError = (errors) => {
  // The FileForm component will handle the error display
  // This is just a fallback in case we need to do something additional
  console.error('File creation errors:', errors)
}
</script>
