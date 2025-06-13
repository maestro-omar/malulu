<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    provinces: {
        type: Array,
        required: true
    },
    countries: {
        type: Array,
        required: true
    }
});

const form = useForm({
    name: '',
    firstname: '',
    lastname: '',
    id_number: '',
    birthdate: '',
    email: '',
    phone: '',
    address: '',
    locality: '',
    province_id: '',
    country_id: '',
    nationality: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nombre Completo" />
                <TextInput
                    id="name"
                    type="text"
                    name="name"
                    v-model="form.name"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="firstname" value="Nombre" />
                <TextInput
                    id="firstname"
                    type="text"
                    name="firstname"
                    v-model="form.firstname"
                    class="mt-1 block w-full"
                    required
                    autocomplete="given-name"
                />
                <InputError class="mt-2" :message="form.errors.firstname" />
            </div>

            <div class="mt-4">
                <InputLabel for="lastname" value="Apellido" />
                <TextInput
                    id="lastname"
                    type="text"
                    name="lastname"
                    v-model="form.lastname"
                    class="mt-1 block w-full"
                    required
                    autocomplete="family-name"
                />
                <InputError class="mt-2" :message="form.errors.lastname" />
            </div>

            <div class="mt-4">
                <InputLabel for="id_number" value="DNI" />
                <TextInput
                    id="id_number"
                    type="text"
                    name="id_number"
                    v-model="form.id_number"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.id_number" />
            </div>

            <div class="mt-4">
                <InputLabel for="birthdate" value="Fecha de Nacimiento" />
                <TextInput
                    id="birthdate"
                    type="date"
                    name="birthdate"
                    v-model="form.birthdate"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.birthdate" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="Teléfono" />
                <TextInput
                    id="phone"
                    type="tel"
                    name="phone"
                    v-model="form.phone"
                    class="mt-1 block w-full"
                    required
                    autocomplete="tel"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="address" value="Dirección" />
                <TextInput
                    id="address"
                    type="text"
                    name="address"
                    v-model="form.address"
                    class="mt-1 block w-full"
                    required
                    autocomplete="street-address"
                />
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <div class="mt-4">
                <InputLabel for="locality" value="Localidad" />
                <TextInput
                    id="locality"
                    type="text"
                    name="locality"
                    v-model="form.locality"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.locality" />
            </div>

            <div class="mt-4">
                <InputLabel for="province_id" value="Provincia" />
                <SelectInput
                    id="province_id"
                    name="province_id"
                    v-model="form.province_id"
                    class="mt-1 block w-full"
                    required
                >
                    <option value="">Seleccione una provincia</option>
                    <option v-for="province in provinces" :key="province.id" :value="province.id">
                        {{ province.name }}
                    </option>
                </SelectInput>
                <InputError class="mt-2" :message="form.errors.province_id" />
            </div>

            <div class="mt-4">
                <InputLabel for="country_id" value="País" />
                <SelectInput
                    id="country_id"
                    name="country_id"
                    v-model="form.country_id"
                    class="mt-1 block w-full"
                    required
                >
                    <option value="">Seleccione un país</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                        {{ country.name }}
                    </option>
                </SelectInput>
                <InputError class="mt-2" :message="form.errors.country_id" />
            </div>

            <div class="mt-4">
                <InputLabel for="nationality" value="Nacionalidad" />
                <TextInput
                    id="nationality"
                    type="text"
                    name="nationality"
                    v-model="form.nationality"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.nationality" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    v-model="form.password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    ¿Ya está registrado?
                </Link>

                <PrimaryButton class="ml-4" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
