<template>
  <div class="q-pa-md">
    <h2 class="text-h4 q-mb-lg">EditableImage Component Test</h2>
    
    <div class="row q-gutter-md">
      <!-- Test with q-avatar -->
      <div class="col-12 col-md-4">
        <h3 class="text-h6 q-mb-md">Inside q-avatar (100px)</h3>
        <q-avatar size="100px" class="q-mb-md">
          <EditableImage 
            v-model="avatarImage" 
            type="avatar" 
            :can-edit="true"
            upload-route="test.avatar.upload"
            delete-route="test.avatar.delete"
            :model-id="1"
          />
        </q-avatar>
      </div>

      <!-- Test with q-avatar (smaller) -->
      <div class="col-12 col-md-4">
        <h3 class="text-h6 q-mb-md">Inside q-avatar (60px)</h3>
        <q-avatar size="60px" class="q-mb-md">
          <EditableImage 
            v-model="avatarImage" 
            type="avatar" 
            :can-edit="true"
            upload-route="test.avatar.upload"
            delete-route="test.avatar.delete"
            :model-id="1"
          />
        </q-avatar>
      </div>

      <!-- Test with custom container -->
      <div class="col-12 col-md-4">
        <h3 class="text-h6 q-mb-md">Custom container (200x150px)</h3>
        <div style="width: 200px; height: 150px; border: 2px solid #ccc; border-radius: 8px;">
          <EditableImage 
            v-model="pictureImage" 
            type="picture" 
            :can-edit="true"
            upload-route="test.picture.upload"
            delete-route="test.picture.delete"
            :model-id="1"
          />
        </div>
      </div>

      <!-- Test with logo container -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Logo container (300x100px)</h3>
        <div style="width: 300px; height: 100px; border: 2px solid #ccc; border-radius: 8px; background: #f5f5f5;">
          <EditableImage 
            v-model="logoImage" 
            type="logo" 
            :can-edit="true"
            upload-route="test.logo.upload"
            delete-route="test.logo.delete"
            :model-id="1"
          />
        </div>
      </div>

      <!-- Test with custom object-fit -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Custom object-fit: contain (200x200px)</h3>
        <div style="width: 200px; height: 200px; border: 2px solid #ccc; border-radius: 8px;">
          <EditableImage 
            v-model="containImage" 
            type="picture" 
            object-fit="contain"
            :can-edit="true"
            upload-route="test.picture.upload"
            delete-route="test.picture.delete"
            :model-id="1"
          />
        </div>
      </div>
    </div>

    <!-- Current values display -->
    <div class="q-mt-xl">
      <h3 class="text-h6 q-mb-md">Current Values:</h3>
      <div class="row q-gutter-md">
        <div class="col-12 col-md-3">
          <strong>Avatar:</strong> {{ avatarImage || 'No image' }}
        </div>
        <div class="col-12 col-md-3">
          <strong>Picture:</strong> {{ pictureImage || 'No image' }}
        </div>
        <div class="col-12 col-md-3">
          <strong>Logo:</strong> {{ logoImage || 'No image' }}
        </div>
        <div class="col-12 col-md-3">
          <strong>Contain:</strong> {{ containImage || 'No image' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import EditableImage from '@/Components/admin/EditableImage.vue';

const avatarImage = ref(null);
const pictureImage = ref(null);
const logoImage = ref(null);
const containImage = ref(null);
</script>
