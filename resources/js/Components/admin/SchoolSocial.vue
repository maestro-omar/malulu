<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="share" size="sm" color="warning" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>
    
    <div class="row q-col-gutter-sm">
      <div class="col-12">
        <div class="row q-gutter-md">
          <div v-for="(social, index) in school.social" :key="index" class="col-auto">
            <q-btn :href="social.link" target="_blank" :label="social.label" color="accent"
              unelevated />
          </div>
          <div v-if="!school.social || school.social.length === 0" class="col-12">
            <div class="text-body1 text-grey-6 text-center q-py-md">
              No hay redes sociales configuradas
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script setup>
const props = defineProps({
  title: { type: String, default: 'Redes sociales' },
  school: { type: Object, required: true },
})
</script>
