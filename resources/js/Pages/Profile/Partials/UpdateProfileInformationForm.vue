<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <q-card-section>
        <div class="text-h6 q-mb-md">Información del Perfil</div>
        <p class="text-body2 text-grey-7 q-mb-lg">
            Actualice la información del perfil y la dirección de correo electrónico de su cuenta.
        </p>

        <form @submit.prevent="form.patch(route('profile.update'))">
            <div class="q-mb-md">
                <q-input
                    v-model="form.name"
                    label="Nombre"
                    outlined
                    dense
                    :error="!!form.errors.name"
                    :error-message="form.errors.name"
                    autofocus
                    autocomplete="name"
                    required
                />
            </div>

            <div class="q-mb-md">
                <q-input
                    v-model="form.email"
                    label="Correo Electrónico"
                    type="email"
                    outlined
                    dense
                    :error="!!form.errors.email"
                    :error-message="form.errors.email"
                    autocomplete="username"
                    required
                />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="q-mb-md">
                <q-banner class="bg-orange-1 text-orange-8">
                    <template v-slot:avatar>
                        <q-icon name="warning" color="orange" />
                    </template>
                    Su dirección de correo electrónico no está verificada.
                    <template v-slot:action>
                        <q-btn
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            flat
                            color="orange"
                            label="Reenviar verificación"
                        />
                    </template>
                </q-banner>
                
                <q-banner
                    v-show="status === 'verification-link-sent'"
                    class="bg-positive-1 text-positive-8 q-mt-sm"
                >
                    <template v-slot:avatar>
                        <q-icon name="check_circle" color="positive" />
                    </template>
                    Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.
                </q-banner>
            </div>

            <div class="row items-center q-gutter-sm">
                <q-btn
                    type="submit"
                    :loading="form.processing"
                    :disable="form.processing"
                    color="primary"
                    unelevated
                    label="Guardar"
                />
                
                <q-transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <q-chip
                        v-if="form.recentlySuccessful"
                        color="positive"
                        text-color="white"
                        icon="check"
                        label="Guardado"
                    />
                </q-transition>
            </div>
        </form>
    </q-card-section>
</template>
