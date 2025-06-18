<template>
  <div class="bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
      <!-- Left Column (20%) -->
      <div class="col-span-1 text-center">
        <h3 class="text-lg font-semibold mb-4 text-center">
          {{ user.firstname + " " + user.lastname }}
        </h3>
        <div v-if="showPicture" class="flex justify-center">
          <EditableImage
            :model-value="user.picture"
            :default-image="'/images/no-image-person.png'"
            :can-edit="false"
            class="w-32 h-32 rounded-full object-cover"
          />
        </div>
      </div>

      <!-- Right Column (80%) -->
      <div class="col-span-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- ID Number -->
          <div class="bg-white p-3 rounded-lg shadow-sm flex items-center">
            <span class="text-sm font-medium text-gray-700">DNI:</span>
            <span class="ml-2 text-sm text-gray-900">{{ user.id_number }}</span>
          </div>

          <!-- Birthdate -->
          <div class="bg-white p-3 col-span-2 rounded-lg shadow-sm flex items-center">
            <span class="text-sm font-medium text-gray-700">Fecha de Nacimiento:</span>
            <span class="ml-2 text-sm text-gray-900">
              {{ formatDateShort(user.birthdate) }}
              <template v-if="user.birthdate">
                &nbsp;({{ calculateAge(user.birthdate) }} años)
              </template>
            </span>
          </div>

          <!-- Email (if enabled) -->
          <div
            v-if="showContact"
            class="bg-white p-3 col-span-2 rounded-lg shadow-sm flex items-center"
          >
            <span class="ml-2 text-sm text-gray-900">
              <EmailField id="userEmail" :email="user.email" class="inline" />
            </span>
          </div>

          <!-- Phone (if enabled) -->
          <div v-if="showContact" class="bg-white p-3 rounded-lg shadow-sm flex items-center">
            <span class="ml-2 text-sm text-gray-900">
              <PhoneField id="userPhone" :phone="user.phone" class="inline" />
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Roles Section (if enabled) -->
    <div v-if="showRoles" class="mt-6">
      <slot name="roles">
        <SchoolsAndRolesCard
          :title="'Escuelas y roles actuales'"
          :user="user"
          :schools="schools"
          :role-relationships="roleRelationships"
          :workers-relationships="workerRelationships"
          :guardian-relationships="guardianRelationships"
          :student-relationships="studentRelationships"
          :roles="roles"
          :user-id="user.id"
          :show-add-role-button="false"
        />
      </slot>
    </div>
  </div>
</template>

<script setup>
import EditableImage from "@/Components/EditableImage.vue";
import InputLabel from "@/Components/InputLabel.vue";
import EmailField from "@/Components/EmailField.vue";
import PhoneField from "@/Components/PhoneField.vue";
import SchoolsAndRolesCard from "@/Components/SchoolsAndRolesCard.vue";
import { formatDateShort, calculateAge } from "@/utils/date";

const props = defineProps({
  user: { type: Object, required: true },
  showPicture: { type: Boolean, default: true },
  showRoles: { type: Boolean, default: true },
  showContact: { type: Boolean, default: false },
  // The following are only needed if showRoles is true
  schools: { type: Array, default: () => [] },
  roleRelationships: { type: Array, default: () => [] },
  workerRelationships: { type: Array, default: () => [] },
  guardianRelationships: { type: Array, default: () => [] },
  studentRelationships: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
});
</script>