<template>
  <div class="user-information">
    <div class="user-information__grid">
      <!-- Left Column (20%) -->
      <div class="user-information__profile">
        <h3 class="user-information__name">
          {{ user.firstname + " " + user.lastname }}
        </h3>
        <div v-if="showPicture" class="user-information__picture">
          <EditableImage
            :model-value="user.picture"
            :default-image="'/images/no-image-person.png'"
            :can-edit="false"
            class="user-information__image"
          />
        </div>
      </div>

      <!-- Right Column (80%) -->
      <div class="user-information__details">
        <div class="user-information__details-grid">
          <!-- ID Number -->
          <div class="user-information__field">
            <span class="user-information__field-label">DNI:</span>
            <span class="user-information__field-value">{{ user.id_number }}</span>
          </div>

          <!-- Birthdate -->
          <div class="user-information__field user-information__field--span-2">
            <span class="user-information__field-label">Fecha de Nacimiento:</span>
            <span class="user-information__field-value">
              {{ formatDateShort(user.birthdate) }}
              <template v-if="user.birthdate">
                &nbsp;({{ calculateAge(user.birthdate) }} años)
              </template>
            </span>
          </div>

          <!-- Email (if enabled) -->
          <div
            v-if="showContact"
            class="user-information__field user-information__field--span-2"
          >
            <span class="user-information__field-value">
              <EmailField id="userEmail" :email="user.email" class="user-information__contact-field" />
            </span>
          </div>

          <!-- Phone (if enabled) -->
          <div v-if="showContact" class="user-information__field">
            <span class="user-information__field-value">
              <PhoneField id="userPhone" :phone="user.phone" class="user-information__contact-field" />
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Roles Section (if enabled) -->
    <div v-if="showRoles" class="user-information__roles">
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
import EditableImage from "@/Components/admin/EditableImage.vue";
import InputLabel from "@/Components/admin/InputLabel.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import SchoolsAndRolesCard from "@/Components/admin/SchoolsAndRolesCard.vue";
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