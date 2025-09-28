<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="schedule" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>
    
    <div class="row q-col-gutter-sm">
      <div class="col-12">
        <div class="row q-col-gutter-sm">
          <div class="col-12">
            <div class="text-h4 q-mb-sm">Horarios</div>
            <div class="row q-col-gutter-sm">
              <!-- Placeholder for schedule content -->
              <div class="col-12">
                <div class="text-body1 text-grey-6">
                  LU MA MI JU VI
                </div>
                <div class="text-caption text-grey-5 q-mt-sm">
                  Horarios pendientes de implementación
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script setup>
const props = defineProps({
  title: { type: String, default: 'Horarios' },
  course: { type: Object, required: true },
})
</script>
