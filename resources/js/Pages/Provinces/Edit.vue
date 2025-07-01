<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';

const props = defineProps({
  province: Object,
  breadcrumbs: Array
});

const form = useForm({
  code: props.province.code,
  name: props.province.name,
  order: props.province.order,
  logo1: props.province.logo1,
  logo2: props.province.logo2,
  title: props.province.title,
  subtitle: props.province.subtitle,
  link: props.province.link,
  config: props.province.config,
});

const submit = () => {
  form.put(route('provinces.update', props.province.code));
};
</script>

<template>

  <Head :title="`Editar Provincia: ${props.province.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Provincia: ${props.province.name}`" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <InputLabel for="code" value="Clave" />
                    <TextInput id="code" v-model="form.code" type="text" class="mt-1 block w-full" required autofocus />
                    <InputError :message="form.errors.code" class="mt-2" />
                  </div>
                  <div>
                    <InputLabel for="order" value="Orden" />
                    <TextInput id="order" v-model="form.order" type="number" class="mt-1 block w-full" />
                    <InputError :message="form.errors.order" class="mt-2" />
                  </div>
                </div>
                <div class="mb-4">
                  <InputLabel for="name" value="Nombre" />
                  <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>
                <div class="mb-4">
                  <InputLabel for="title" value="Título" />
                  <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>
                <div class="mb-4">
                  <InputLabel for="subtitle" value="Subtítulo" />
                  <TextInput id="subtitle" v-model="form.subtitle" type="text" class="mt-1 block w-full" />
                  <InputError :message="form.errors.subtitle" class="mt-2" />
                </div>
                <div class="mb-4">
                  <InputLabel for="link" value="Enlace" />
                  <TextInput id="link" v-model="form.link" type="text" class="mt-1 block w-full" />
                  <InputError :message="form.errors.link" class="mt-2" />
                </div>
                <div class="mb-4">
                  <InputLabel for="config" value="Config (JSON libre)" />
                  <textarea id="config" v-model="form.config"
                    class="mt-1 block w-full font-mono text-xs border-gray-300 rounded-md shadow-sm" rows="6"
                    placeholder='{ "key": "value" }'></textarea>
                  <InputError :message="form.errors.config" class="mt-2" />
                </div>
              </div>
            </div>
          </div>
          <ActionButtons button-label="Guardar cambios" :cancel-href="route('provinces.index')"
            :disabled="form.processing" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
