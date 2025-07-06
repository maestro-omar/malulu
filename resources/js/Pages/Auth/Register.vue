<script setup>
import MinimalAuthLayout from '@/Layouts/MinimalAuthLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SelectInput from '@/Components/admin/SelectInput.vue';

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
    <MinimalAuthLayout>
        <Head title="Register" />

        <form @submit.prevent="submit" class="auth__form">
            <div class="auth__field">
                <InputLabel for="name" value="Nombre Completo" />
                <TextInput
                    id="name"
                    type="text"
                    name="name"
                    v-model="form.name"
                    class="form__input--full-width"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="auth__field">
                <InputLabel for="firstname" value="Nombre" />
                <TextInput
                    id="firstname"
                    type="text"
                    name="firstname"
                    v-model="form.firstname"
                    class="form__input--full-width"
                    required
                    autocomplete="given-name"
                />
                <InputError :message="form.errors.firstname" />
            </div>

            <div class="auth__field">
                <InputLabel for="lastname" value="Apellido" />
                <TextInput
                    id="lastname"
                    type="text"
                    name="lastname"
                    v-model="form.lastname"
                    class="form__input--full-width"
                    required
                    autocomplete="family-name"
                />
                <InputError :message="form.errors.lastname" />
            </div>

            <div class="auth__field">
                <InputLabel for="id_number" value="DNI" />
                <TextInput
                    id="id_number"
                    type="text"
                    name="id_number"
                    v-model="form.id_number"
                    class="form__input--full-width"
                    required
                />
                <InputError :message="form.errors.id_number" />
            </div>

            <div class="auth__field">
                <InputLabel for="birthdate" value="Fecha de Nacimiento" />
                <TextInput
                    id="birthdate"
                    type="date"
                    name="birthdate"
                    v-model="form.birthdate"
                    class="form__input--full-width"
                    required
                />
                <InputError :message="form.errors.birthdate" />
            </div>

            <div class="auth__field">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    class="form__input--full-width"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="auth__field">
                <InputLabel for="phone" value="Teléfono" />
                <TextInput
                    id="phone"
                    type="tel"
                    name="phone"
                    v-model="form.phone"
                    class="form__input--full-width"
                    required
                    autocomplete="tel"
                />
                <InputError :message="form.errors.phone" />
            </div>

            <div class="auth__field">
                <InputLabel for="address" value="Dirección" />
                <TextInput
                    id="address"
                    type="text"
                    name="address"
                    v-model="form.address"
                    class="form__input--full-width"
                    required
                    autocomplete="street-address"
                />
                <InputError :message="form.errors.address" />
            </div>

            <div class="auth__field">
                <InputLabel for="locality" value="Localidad" />
                <TextInput
                    id="locality"
                    type="text"
                    name="locality"
                    v-model="form.locality"
                    class="form__input--full-width"
                    required
                />
                <InputError :message="form.errors.locality" />
            </div>

            <div class="auth__field">
                <InputLabel for="province_id" value="Provincia" />
                <SelectInput
                    id="province_id"
                    name="province_id"
                    v-model="form.province_id"
                    class="form__input--full-width"
                    required
                >
                    <option value="">Seleccione una provincia</option>
                    <option v-for="province in provinces" :key="province.id" :value="province.id">
                        {{ province.name }}
                    </option>
                </SelectInput>
                <InputError :message="form.errors.province_id" />
            </div>

            <div class="auth__field">
                <InputLabel for="country_id" value="País" />
                <SelectInput
                    id="country_id"
                    name="country_id"
                    v-model="form.country_id"
                    class="form__input--full-width"
                    required
                >
                    <option value="">Seleccione un país</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                        {{ country.name }}
                    </option>
                </SelectInput>
                <InputError :message="form.errors.country_id" />
            </div>

            <div class="auth__field">
                <InputLabel for="nationality" value="Nacionalidad" />
                <TextInput
                    id="nationality"
                    type="text"
                    name="nationality"
                    v-model="form.nationality"
                    class="form__input--full-width"
                    required
                />
                <InputError :message="form.errors.nationality" />
            </div>

            <div class="auth__field">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    v-model="form.password"
                    class="form__input--full-width"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="auth__field">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    class="form__input--full-width"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="auth__actions">
                <Link
                    :href="route('login')"
                    class="auth__link"
                >
                    ¿Ya está registrado?
                </Link>

                <PrimaryButton :processing="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </MinimalAuthLayout>
</template>
