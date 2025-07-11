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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information Card -->
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Nombre de usuario" />
                                <TextInput id="name" type="text" class="mt-1 block form__input--full-width" v-model="form.name"
                                    required />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="firstname" value="Nombre" />
                                    <TextInput id="firstname" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.firstname" />
                                    <InputError class="mt-2" :message="form.errors.firstname" />
                                </div>

                                <div>
                                    <InputLabel for="lastname" value="Apellido" />
                                    <TextInput id="lastname" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.lastname" />
                                    <InputError class="mt-2" :message="form.errors.lastname" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="id_number" value="DNI" />
                                    <TextInput id="id_number" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.id_number" />
                                    <InputError class="mt-2" :message="form.errors.id_number" />
                                </div>

                                <div>
                                    <InputLabel for="birthdate" value="Fecha de Nacimiento" />
                                    <TextInput id="birthdate" type="date" class="mt-1 block form__input--full-width"
                                        v-model="form.birthdate" />
                                    <InputError class="mt-2" :message="form.errors.birthdate" />
                                </div>

                                <div>
                                    <InputLabel for="nationality" value="Nacionalidad" />
                                    <TextInput id="nationality" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.nationality" />
                                    <InputError class="mt-2" :message="form.errors.nationality" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h3>
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="email" value="Correo electrónico" />
                                    <TextInput id="email" type="email" class="mt-1 block form__input--full-width" v-model="form.email"
                                        required />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="Teléfono" />
                                    <TextInput id="phone" type="text" class="mt-1 block form__input--full-width" v-model="form.phone" />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="address" value="Dirección" />
                                    <TextInput id="address" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.address" />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>

                                <div>
                                    <InputLabel for="locality" value="Localidad" />
                                    <TextInput id="locality" type="text" class="mt-1 block form__input--full-width"
                                        v-model="form.locality" />
                                    <InputError class="mt-2" :message="form.errors.locality" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="province_id" value="Provincia" />
                                    <select id="province_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        v-model="form.province_id">
                                        <option value="">Seleccione una provincia</option>
                                        <option v-for="province in provinces" :key="province.id" :value="province.id">
                                            {{ province.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.province_id" />
                                </div>

                                <div>
                                    <InputLabel for="country_id" value="País" />
                                    <select id="country_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        v-model="form.country_id">
                                        <option value="">Seleccione un país</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.country_id" />
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