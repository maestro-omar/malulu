<template>

    <Head title="Provincias" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Provincias`" />
        </template>

        <div class="container">
            <!-- Flash Messages -->
            <FlashMessages :flash="flash" />

            <div class="table__wrapper">
                <div class="table__container">
                    <!-- Desktop Table View -->
                    <div class="table__desktop">
                        <table class="table__table">
                            <thead class="table__thead">
                                <tr>
                                    <th class="table__th">Escudo</th>
                                    <th class="table__th">Nombre</th>
                                    <th class="table__th">Título</th>
                                    <th class="table__th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="table__tbody">
                                <tr v-for="(province, index) in provinces" :key="province.id" :class="{
                                    'table__tr--even': index % 2 === 0,
                                    'table__tr--odd': index % 2 === 1
                                }">
                                    <td class="table__td table__logo">
                                        <img v-if="province.logo1" :src="province.logo1" alt="Escudo" />
                                    </td>
                                    <td class="table__td table__name">{{ province.name }}</td>
                                    <td class="table__td table__title">{{ province.title }}</td>
                                    <td class="table__td table__actions">
                                        <Link :href="route('provinces.show', province.code)">Ver</Link>
                                        <Link :href="route('provinces.edit', province.code)">Editar</Link>
                                        <button @click="deleteProvince(province.id)">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="table__mobile">
                        <div v-for="(province, index) in provinces" :key="province.id" :class="{
                            'table__card--even': index % 2 === 0,
                            'table__card--odd': index % 2 === 1
                        }" class="table__card">
                            <div class="table__card-header">
                                <div class="table__card-user">
                                    <img v-if="province.logo1" :src="province.logo1" alt="Escudo" />
                                    <div class="table__card-info">
                                        <h3>{{ province.name }}</h3>
                                        <p>{{ province.title }}</p>
                                    </div>
                                </div>
                                <div class="table__card-actions">
                                    <Link :href="route('provinces.show', province.code)">Ver</Link>
                                    <Link :href="route('provinces.edit', province.code)">Editar</Link>
                                    <button @click="deleteProvince(province.id)">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    provinces: Array,
    breadcrumbs: Array
});

const deleteProvince = (id) => {
    if (confirm('¿Está seguro que desea eliminar esta provincia?')) {
        router.delete(route('provinces.destroy', id));
    }
};
</script>