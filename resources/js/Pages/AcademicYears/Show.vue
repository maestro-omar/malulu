<template>

    <Head :title="`Ciclo Lectivo ${academicYear.year}`" />

    <AuthenticatedLayout>
        <template #admin-header>
            <AdminHeader :title="`Detalles del Ciclo Lectivo ${academicYear.year}`" :edit="{
                show: hasPermission($page.props, 'academic-year.manage'),
                href: route('academic-years.edit', { 'academicYear': academicYear.id }),
                label: 'Editar'
            }" :del="{
        show: hasPermission($page.props, 'academic-year.manage'),
        onClick: destroy,
        label: 'Eliminar'
    }">
            </AdminHeader>
        </template>

        <template #main-page-content>
            <div class="container">
                <div class="admin-detail__wrapper">
                    <div class="admin-detail__card">
                        <div class="admin-detail__content">
                            <div class="admin-detail__section">
                                <h2 class="admin-detail__section-title">Información del Ciclo Lectivo</h2>
                                <div class="admin-detail__field-grid">
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Año</label>
                                        <div class="admin-detail__value">{{ academicYear.year }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Fecha de Inicio</label>
                                        <div class="admin-detail__value">{{ formatDate(academicYear.start_date) }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Fecha de Fin</label>
                                        <div class="admin-detail__value">{{ formatDate(academicYear.end_date) }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Inicio de Vacaciones de Invierno</label>
                                        <div class="admin-detail__value">{{ academicYear.winter_break_start ?
                                            formatDate(academicYear.winter_break_start) : '-' }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Fin de Vacaciones de Invierno</label>
                                        <div class="admin-detail__value">{{ academicYear.winter_break_end ?
                                            formatDate(academicYear.winter_break_end) : '-' }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Estado</label>
                                        <div class="admin-detail__value">
                                            <span :class="{
                                                'admin-detail__status': true,
                                                'admin-detail__status--active': academicYear.active,
                                                'admin-detail__status--inactive': !academicYear.active,
                                            }">
                                                {{ academicYear.active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-detail__card admin-detail__card--mt">
                        <div class="admin-detail__content">
                            <div class="admin-detail__section">
                                <h2 class="admin-detail__section-title">Información del Sistema</h2>
                                <div class="admin-detail__field-grid">
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Fecha de Creación</label>
                                        <div class="admin-detail__value">{{ formatDate(academicYear.created_at) }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Última Actualización</label>
                                        <div class="admin-detail__value">{{ formatDate(academicYear.updated_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { hasPermission } from '@/Utils/permissions'
import { formatDate } from '@/Utils/date'
import { Head, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    academicYear: {
        type: Object,
        required: true
    },
    breadcrumbs: Array
})

const $page = usePage()

const destroy = () => {
    if (confirm("¿Está seguro que desea eliminar este ciclo lectivo?")) {
        router.delete(route("academic-years.destroy", props.academicYear.id))
    }
}
</script>