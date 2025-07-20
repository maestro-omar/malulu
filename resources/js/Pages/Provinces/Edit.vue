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

    <div class="container">
      <div class="form__wrapper">
        <form @submit.prevent="submit" class="form__container">
          <div class="form__card">
            <div class="form__card-content">
              <div class="form__grid form__grid--2">
                <div class="form__field">
                  <InputLabel for="code" value="Clave" />
                  <TextInput id="code" v-model="form.code" type="text" class="form__input" required autofocus />
                  <InputError :message="form.errors.code" class="form__error" />
                </div>
                <div class="form__field">
                  <InputLabel for="order" value="Orden" />
                  <TextInput id="order" v-model="form.order" type="number" class="form__input" />
                  <InputError :message="form.errors.order" class="form__error" />
                </div>
              </div>
              
              <div class="form__field">
                <InputLabel for="name" value="Nombre" />
                <TextInput id="name" v-model="form.name" type="text" class="form__input" required />
                <InputError :message="form.errors.name" class="form__error" />
              </div>
              
              <div class="form__field">
                <InputLabel for="title" value="Título" />
                <TextInput id="title" v-model="form.title" type="text" class="form__input" />
                <InputError :message="form.errors.title" class="form__error" />
              </div>
              
              <div class="form__field">
                <InputLabel for="subtitle" value="Subtítulo" />
                <TextInput id="subtitle" v-model="form.subtitle" type="text" class="form__input" />
                <InputError :message="form.errors.subtitle" class="form__error" />
              </div>
              
              <div class="form__field">
                <InputLabel for="link" value="Enlace" />
                <TextInput id="link" v-model="form.link" type="text" class="form__input" />
                <InputError :message="form.errors.link" class="form__error" />
              </div>
              
              <div class="form__field">
                <InputLabel for="config" value="Config (JSON libre)" />
                <textarea id="config" v-model="form.config"
                  class="form__textarea form__textarea--code" rows="6"
                  placeholder='{ "key": "value" }'></textarea>
                <InputError :message="form.errors.config" class="form__error" />
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
