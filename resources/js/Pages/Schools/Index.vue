<template>
  <Head title="Schools" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Schools</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold">School List</h3>
              <Link
                v-if="can('create schools')"
                :href="route('schools.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                Add School
              </Link>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Locality</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Levels</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="school in schools.data" :key="school.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ school.name }}</div>
                      <div class="text-sm text-gray-500">{{ school.short }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ school.key }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ school.locality?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div v-for="level in school.school_levels" :key="level.id" class="inline-block mr-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                          {{ level.name }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link
                        v-if="can('view schools')"
                        :href="route('schools.show', school.id)"
                        class="text-blue-600 hover:text-blue-900 mr-3"
                      >
                        View
                      </Link>
                      <Link
                        v-if="can('edit schools')"
                        :href="route('schools.edit', school.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        Edit
                      </Link>
                      <Link
                        v-if="can('delete schools')"
                        :href="route('schools.destroy', school.id)"
                        method="delete"
                        as="button"
                        class="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination :links="schools.links" class="mt-6" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { usePage } from '@inertiajs/vue3';

const { can } = usePage().props.auth;
const props = defineProps({
  schools: Object
});
</script> 