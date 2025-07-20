<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    provinces: Array,
    countries: Array,
    breadcrumbs: Array,
});

const form = useForm({
    name: props.user.name,
    firstname: props.user.firstname || '',
    lastname: props.user.lastname || '',
    id_number: props.user.id_number || '',
    birthdate: props.user.birthdate ? new Date(props.user.birthdate).toISOString().split('T')[0] : '',
    phone: props.user.phone || '',
    address: props.user.address || '',
    locality: props.user.locality || '',
    province_id: props.user.province_id || '',
    country_id: props.user.country_id || '',
    nationality: props.user.nationality || '',
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles?.[0]?.name || '',
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>

    <Head :title="`Editar Usuario: ${props.user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Usuario: ${props.user.name}`"></AdminHeader>
        </template>

        <div class="container">
            <div class="form__wrapper">
                <form @submit.prevent="submit" class="form__container">
                    <!-- Basic Information Card -->
                    <div class="form__card">
                        <h3 class="form__card-title">Información Básica</h3>
                        <div class="form__card-content">
                            <div class="form__field">
                                <InputLabel for="name" value="Nombre de usuario" />
                                <TextInput id="name" type="text" class="form__input" v-model="form.name" required />
                                <InputError class="form__error" :message="form.errors.name" />
                            </div>

                            <div class="form__grid form__grid--2">
                                <div class="form__field">
                                    <InputLabel for="firstname" value="Nombre" />
                                    <TextInput id="firstname" type="text" class="form__input" v-model="form.firstname" />
                                    <InputError class="form__error" :message="form.errors.firstname" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="lastname" value="Apellido" />
                                    <TextInput id="lastname" type="text" class="form__input" v-model="form.lastname" />
                                    <InputError class="form__error" :message="form.errors.lastname" />
                                </div>
                            </div>

                            <div class="form__grid form__grid--3">
                                <div class="form__field">
                                    <InputLabel for="id_number" value="DNI" />
                                    <TextInput id="id_number" type="text" class="form__input" v-model="form.id_number" />
                                    <InputError class="form__error" :message="form.errors.id_number" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="birthdate" value="Fecha de Nacimiento" />
                                    <TextInput id="birthdate" type="date" class="form__input" v-model="form.birthdate" />
                                    <InputError class="form__error" :message="form.errors.birthdate" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="nationality" value="Nacionalidad" />
                                    <TextInput id="nationality" type="text" class="form__input" v-model="form.nationality" />
                                    <InputError class="form__error" :message="form.errors.nationality" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="form__card">
                        <h3 class="form__card-title">Información de Contacto</h3>
                        <div class="form__card-content">
                            <div class="form__grid form__grid--2">
                                <div class="form__field">
                                    <InputLabel for="email" value="Correo electrónico" />
                                    <TextInput id="email" type="email" class="form__input" v-model="form.email" required />
                                    <InputError class="form__error" :message="form.errors.email" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="phone" value="Teléfono" />
                                    <TextInput id="phone" type="text" class="form__input" v-model="form.phone" />
                                    <InputError class="form__error" :message="form.errors.phone" />
                                </div>
                            </div>

                            <div class="form__grid form__grid--2">
                                <div class="form__field">
                                    <InputLabel for="address" value="Dirección" />
                                    <TextInput id="address" type="text" class="form__input" v-model="form.address" />
                                    <InputError class="form__error" :message="form.errors.address" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="locality" value="Localidad" />
                                    <TextInput id="locality" type="text" class="form__input" v-model="form.locality" />
                                    <InputError class="form__error" :message="form.errors.locality" />
                                </div>
                            </div>

                            <div class="form__grid form__grid--2">
                                <div class="form__field">
                                    <InputLabel for="province_id" value="Provincia" />
                                    <select id="province_id" class="form__select" v-model="form.province_id">
                                        <option value="">Seleccione una provincia</option>
                                        <option v-for="province in provinces" :key="province.id" :value="province.id">
                                            {{ province.name }}
                                        </option>
                                    </select>
                                    <InputError class="form__error" :message="form.errors.province_id" />
                                </div>

                                <div class="form__field">
                                    <InputLabel for="country_id" value="País" />
                                    <select id="country_id" class="form__select" v-model="form.country_id">
                                        <option value="">Seleccione un país</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <InputError class="form__error" :message="form.errors.country_id" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <ActionButtons
                        button-label="Guardar Cambios"
                        :cancel-href="route('users.show', props.user.id)"
                        :disabled="form.processing"
                    />
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>