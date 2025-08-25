<template>

  <Head :title="`Escuela: ${school.short}`" />

  <SchoolLayout>
    <GuestHeader :title="school.name">
    </GuestHeader>

    <div class="school-public__container">
      <div class="school-public__card">
        <div class="school-public__section">
          <div v-if="school.picture" class="school-public__image-container">
            <img :src="school.picture" class="school-public__image" />
          </div>
          <div class="school-public__grid school-public__grid--2 school-public__grid--md-3">
            <div>
              <label class="school-public__label">CUE</label>
              <p class="school-public__value">{{ school.cue }}</p>
            </div>
            <div>
              <label class="school-public__label">Tipo de Gestión</label>
              <div>
                <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
              </div>
            </div>
            <div>
              <label class="school-public__label">Niveles</label>
              <div class="school-public__badge-group">
                <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
              </div>
            </div>
            <div>
              <label class="school-public__label">Turnos</label>
              <div class="school-public__badge-group">
                <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
              </div>
            </div>
          </div>
        </div>

        <div class="school-public__section">
          <div class="school-public__section-title">Ubicación y Contacto</div>
          <div class="school-public__grid">
            <div class="school-public__coordinates">
              <p class="school-public__value">
                {{ school.address }} ({{ school.zip_code }}), {{ school.locality.name }}
              </p>
              <iframe :src="`https://maps.google.com/maps?output=embed&q=${school.coordinates}`" width="100%"
                height="350" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          </div>
          <div class="school-public__grid school-public__grid--2 school-public__grid--md-2">
            <div>
              <label class="school-public__label">Teléfono</label>
              <PhoneField :phone="school.phone" />
            </div>
            <div>
              <label class="school-public__label">Email</label>
              <EmailField :email="school.email" />
            </div>
          </div>
        </div>
        <div class="school-public__section school-public__section--social">
          <h3 class="school-public__section-title">Redes sociales</h3>
          <div class="school-public__social-list">
            <div v-for="(social, index) in school.social" :key="index" class="school-public__social-item">
              <div class="school-public__social-col">
                <a :href="social.link" target="_blank" class="school-public__social-link">
                  {{ social.label }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SchoolLayout>
</template>

<script setup>
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import ManagementTypeBadge from "@/Components/Badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/Badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/Badges/SchoolShiftBadge.vue";
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import SchoolLayout from '@/Layouts/SchoolLayout.vue';
import GuestHeader from "@/Sections/GuestHeader.vue";
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
  school: Object,
  
});

const copyToClipboard = (text) => {
  // Create a temporary input element
  const input = document.createElement("input");
  input.setAttribute("value", text);
  document.body.appendChild(input);
  input.select();

  try {
    // Execute copy command
    document.execCommand("copy");
    // Optional: Add a toast notification here
  } catch (err) {
    console.error("Failed to copy text: ", err);
  }

  // Clean up
  document.body.removeChild(input);
};
</script>