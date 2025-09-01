<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';

const props = defineProps({
  province: Object,
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
    <template #admin-header>
      <AdminHeader :title="`Editar Provincia: ${props.province.name}`" />
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-form__field">
                      <InputLabel for="code" value="Clave" />
                      <TextInput id="code" v-model="form.code" type="text" class="admin-form__input" required
                        autofocus />
                      <InputError :message="form.errors.code" class="admin-form__error" />
                    </div>
                    <div class="admin-form__field">
                      <InputLabel for="order" value="Orden" />
                      <TextInput id="order" v-model="form.order" type="number" class="admin-form__input" />
                      <InputError :message="form.errors.order" class="admin-form__error" />
                    </div>
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="name" value="Nombre" />
                    <TextInput id="name" v-model="form.name" type="text" class="admin-form__input" required />
                    <InputError :message="form.errors.name" class="admin-form__error" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="title" value="Título" />
                    <TextInput id="title" v-model="form.title" type="text" class="admin-form__input" />
                    <InputError :message="form.errors.title" class="admin-form__error" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="subtitle" value="Subtítulo" />
                    <TextInput id="subtitle" v-model="form.subtitle" type="text" class="admin-form__input" />
                    <InputError :message="form.errors.subtitle" class="admin-form__error" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="link" value="Enlace" />
                    <TextInput id="link" v-model="form.link" type="text" class="admin-form__input" />
                    <InputError :message="form.errors.link" class="admin-form__error" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="config" value="Config (JSON libre)" />
                    <textarea id="config" v-model="form.config" class="admin-form__textarea admin-form__textarea--code"
                      rows="6" placeholder='{ "key": "value" }'></textarea>
                    <InputError :message="form.errors.config" class="admin-form__error" />
                  </div>
                </div>
              </q-card-section>
            </q-card>
            <ActionButtons button-label="Guardar cambios" :cancel-href="route('provinces.index')"
              :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>
