<template>

  <Head title="Herramientas de Administración" />

  <GuestLayout title="Herramientas de Administración">
    <q-page class="q-pa-md">
      <q-banner class="bg-warning text-white q-mb-md">
        <template v-slot:avatar>
          <q-icon name="warning" />
        </template>
        <strong>Advertencia:</strong> Esta página permite ejecutar comandos y consultas SQL directamente en el servidor.
        Úsala con precaución.
      </q-banner>

      <!-- Artisan Commands Section -->
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="text-h6 q-mb-md">
            <q-icon name="terminal" class="q-mr-sm" />
            Comandos Artisan
          </div>

          <div class="row q-gutter-md q-mb-md">
            <div class="col-12 col-md-6">
              <q-select v-model="selectedCommand" :options="commandOptions" option-value="value" option-label="label"
                emit-value map-options outlined label="Seleccionar comando" dense>
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-select>
            </div>
            <template v-if="selectedCommand === 'db:seed'">
              <div class="col-12 col-md-6">
                <q-select v-model="selectedSeeder" :options="seederOptions" option-value="value" option-label="label"
                  emit-value map-options outlined label="Seleccionar seeder (opcional)" dense clearable
                  hint="Dejar vacío para ejecutar DatabaseSeeder" @update:model-value="customSeederClass = null">
                  <template v-slot:prepend>
                    <q-icon name="database" />
                  </template>
                </q-select>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="customSeederClass" outlined label="O ingresar clase de seeder personalizada" dense
                  hint="Ej: Database\Seeders\MyCustomSeeder" @update:model-value="selectedSeeder = null">
                  <template v-slot:prepend>
                    <q-icon name="edit" />
                  </template>
                </q-input>
              </div>
              <div class="col-12">
                <q-btn color="primary" label="Ejecutar seeder" icon="play_arrow" :loading="commandLoading"
                  :disable="!selectedCommand || commandLoading" @click="executeCommand" class="full-width" />
              </div>
            </template>
            <div class="col-12 col-md-6" v-else>
              <q-btn color="primary" label="Ejecutar comando" icon="play_arrow" :loading="commandLoading"
                :disable="!selectedCommand || commandLoading" @click="executeCommand" class="full-width" />
            </div>
          </div>

          <!-- Command Output -->
          <q-expansion-item v-if="(commandOutput !== null && commandOutput !== '') || commandLoading"
            :label="commandLoading ? 'Ejecutando comando...' : (commandSuccess === true ? 'Salida del comando (éxito)' : 'Salida del comando (error)')"
            :icon="commandLoading ? 'hourglass_empty' : (commandSuccess === true ? 'check_circle' : 'error')"
            :header-class="commandLoading ? 'text-info' : (commandSuccess === true ? 'text-positive' : 'text-negative')"
            default-opened>
            <q-card>
              <q-card-section>
                <div v-if="commandLoading" class="q-mb-md">
                  <q-linear-progress indeterminate color="primary" />
                  <div class="text-caption q-mt-sm text-grey-6">
                    Ejecutando comando... El resultado se mostrará al finalizar.
                  </div>
                </div>
                <pre ref="outputRef" class="q-pa-md bg-grey-2"
                  style="max-height: 500px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.5;">
            {{ commandOutput || (commandLoading ? 'Esperando resultado...' : '') }}</pre>
              </q-card-section>
            </q-card>
          </q-expansion-item>
        </q-card-section>
      </q-card>

      <!-- SQL Query Section -->
      <q-card>
        <q-card-section>
          <div class="text-h6 q-mb-md">
            <q-icon name="storage" class="q-mr-sm" />
            Consultas SQL
          </div>

          <div class="q-mb-md">
            <q-select v-model="queryType" :options="queryTypeOptions" option-value="value" option-label="label"
              emit-value map-options outlined label="Tipo de consulta" dense class="q-mb-md">
              <template v-slot:prepend>
                <q-icon name="category" />
              </template>
            </q-select>

            <q-input v-model="sqlQuery" type="textarea" outlined label="Consulta SQL" rows="6"
              placeholder="Ejemplo: SELECT * FROM users LIMIT 10;" class="q-mb-md">
              <template v-slot:prepend>
                <q-icon name="code" />
              </template>
            </q-input>

            <q-btn color="secondary" label="Ejecutar consulta" icon="play_arrow" :loading="queryLoading"
              :disable="!sqlQuery.trim() || queryLoading" @click="executeQuery" class="full-width" />
          </div>

          <!-- Query Results -->
          <q-expansion-item v-if="queryResults !== null"
            :label="querySuccess ? `Resultados (${queryRowCount} fila(s))` : 'Error en la consulta'"
            :icon="querySuccess ? 'check_circle' : 'error'"
            :header-class="querySuccess ? 'text-positive' : 'text-negative'" default-opened>
            <q-card>
              <q-card-section>
                <div v-if="querySuccess && queryType === 'select'">
                  <!-- Table for SELECT results -->
                  <q-table v-if="queryResults.length > 0" :rows="queryResults" :columns="queryColumns" row-key="__index"
                    dense :pagination="{ rowsPerPage: 50 }" class="q-mt-md" />
                  <div v-else class="text-grey-6 q-pa-md text-center">
                    No se encontraron resultados
                  </div>
                </div>
                <div v-else-if="querySuccess && queryType !== 'select'">
                  <q-banner class="bg-positive text-white">
                    <template v-slot:avatar>
                      <q-icon name="check_circle" />
                    </template>
                    Consulta ejecutada exitosamente. {{ queryRowCount }} fila(s) afectada(s).
                  </q-banner>
                </div>
                <div v-else>
                  <pre class="q-pa-md bg-negative text-white"
                    style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">{{ queryError
              }}</pre>
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>
        </q-card-section>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layout/GuestLayout.vue';
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

// Command state
const selectedCommand = ref(null);
const selectedSeeder = ref(null);
const customSeederClass = ref(null);
const commandLoading = ref(false);
const commandOutput = ref('');
const commandSuccess = ref(false);
const outputRef = ref(null);

// Query state
const sqlQuery = ref('');
const queryType = ref('select');
const queryLoading = ref(false);
const queryResults = ref(null);
const querySuccess = ref(false);
const queryRowCount = ref(0);
const queryError = ref('');
const queryColumns = ref([]);

// Command options
const commandOptions = [
  { label: 'Migrar base de datos', value: 'migrate' },
  { label: 'Migrar base de datos (fresh)', value: 'migrate:fresh' },
  { label: 'Migrar base de datos (fresh) con seeders', value: 'migrate:fresh:seed' },
  { label: 'Estado de migraciones', value: 'migrate:status' },
  { label: 'Refrescar migraciones', value: 'migrate:refresh' },
  { label: 'Revertir última migración', value: 'migrate:rollback' },
  { label: 'Ejecutar seeders', value: 'db:seed' },
  { label: 'Eliminar todas las tablas', value: 'db:drop-all-tables' },
  { label: 'Limpiar caché', value: 'cache:clear' },
  { label: 'Limpiar configuración', value: 'config:clear' },
  { label: 'Limpiar rutas', value: 'route:clear' },
  { label: 'Limpiar vistas', value: 'view:clear' },
  { label: 'Limpiar optimización', value: 'optimize:clear' },
];

// Seeder options
const seederOptions = [
  { label: 'CoreDataSeeder (Datos esenciales)', value: 'CoreDataSeeder' },
  { label: 'LucioCoursesFixSeeder', value: 'LucioCoursesFixSeeder' },
  { label: 'InitialStaffSeeder (Personal inicial)', value: 'InitialStaffSeeder' },
  { label: 'InitialStudentsSeeder (Estudiantes iniciales)', value: 'InitialStudentsSeeder' },
  { label: 'UpdateDataTo2026Seeder', value: 'UpdateDataTo2026Seeder' },
  { label: 'InitialStudents2026Seeder', value: 'InitialStudents2026Seeder' },
  { label: 'FakeAcademicEventsSeeder', value: 'FakeAcademicEventsSeeder' },
  { label: 'FakeFilesSeeder', value: 'FakeFilesSeeder' },
  { label: 'FakeAttendanceSeeder', value: 'FakeAttendanceSeeder' },
  { label: 'FakeUserDiagnosisSeeder', value: 'FakeUserDiagnosisSeeder' },
];

// Query type options
const queryTypeOptions = [
  { label: 'SELECT (Solo lectura)', value: 'select' },
  { label: 'INSERT', value: 'insert' },
  { label: 'UPDATE', value: 'update' },
  { label: 'DELETE', value: 'delete' },
  { label: 'Otro', value: 'other' },
];

// Execute Artisan command
const executeCommand = async () => {
  if (!selectedCommand.value) {
    $q.notify({
      type: 'negative',
      message: 'Por favor selecciona un comando',
    });
    return;
  }

  // Handle migrate:fresh:seed special case
  let command = selectedCommand.value;
  let options = {};

  if (command === 'migrate:fresh:seed') {
    command = 'migrate:fresh';
    options = { '--seed': true };
  }

  // Handle db:seed with specific seeder class
  if (command === 'db:seed') {
    // Use custom seeder class if provided, otherwise use dropdown selection
    const seederClass = customSeederClass.value?.trim() || selectedSeeder.value;
    if (seederClass) {
      options = { '--class': seederClass };
    }
  }

  // Confirmation for destructive commands
  if (command === 'migrate:fresh' || command === 'migrate:refresh' || command === 'db:drop-all-tables') {
    let message = '';
    if (command === 'migrate:fresh') {
      message = '⚠️ ADVERTENCIA: Este comando eliminará TODAS las tablas y datos de la base de datos y las recreará desde cero.';
      if (options['--seed']) {
        message += ' Luego ejecutará los seeders.';
      }
      message += '\n\n¿Estás seguro de que deseas continuar?';
    } else if (command === 'db:drop-all-tables') {
      message = '🚨 ADVERTENCIA CRÍTICA: Este comando eliminará PERMANENTEMENTE TODAS las tablas y TODOS los datos de la base de datos.\n\nEsta acción NO se puede deshacer.\n\n¿Estás ABSOLUTAMENTE seguro de que deseas continuar?';
    } else {
      message = 'Este comando revertirá todas las migraciones y las volverá a ejecutar. ¿Estás seguro?';
    }

    const confirmed = await $q.dialog({
      title: 'Confirmar ejecución',
      message: message,
      cancel: true,
      persistent: true,
      color: 'negative',
    });

    if (!confirmed) {
      return;
    }
  }

  commandLoading.value = true;
  commandOutput.value = '';
  commandSuccess.value = false;

  // Disable streaming for now - it's complex and may not work in all environments
  // Just use regular execution and show output after completion
  try {
    const response = await axios.post(route('admin-tools.command'), {
      command: command,
      options: options,
    });

    // Debug: Log the response to see what we're receiving
    console.log('Admin Tools Response:', {
      success: response.data.success,
      exit_code: response.data.exit_code,
      success_type: typeof response.data.success,
      output_length: response.data.output_length,
      truncated: response.data.truncated,
      response_keys: Object.keys(response.data),
      // Don't log full response if it's huge
      response_preview: response.data.output ? response.data.output.substring(0, 200) : 'No output',
    });

    // Ensure success is a boolean (handle string "true"/"false" or 1/0)
    let successValue = response.data.success;

    // Debug: Log raw success value
    console.log('Raw success value:', successValue, 'Type:', typeof successValue);

    if (typeof successValue === 'string') {
      successValue = successValue === 'true' || successValue === '1';
    } else if (typeof successValue === 'number') {
      // If it's a number, 1 = true, 0 = false (but check exit_code too)
      successValue = successValue === 1 || (successValue === 0 && response.data.exit_code === 0);
    } else if (successValue === undefined || successValue === null) {
      // Fallback: use exit_code
      successValue = response.data.exit_code === 0;
      console.log('Success was undefined/null, using exit_code:', response.data.exit_code, 'Result:', successValue);
    }

    // Force to boolean
    commandSuccess.value = Boolean(successValue);

    // Debug: Verify it was set correctly
    console.log('After setting commandSuccess.value:', commandSuccess.value, 'Type:', typeof commandSuccess.value);

    // Show output - prefer 'output' field, fallback to 'message'
    const output = response.data.output || response.data.message || '';
    commandOutput.value = output;

    // If there's a note (like "streaming not available"), append it
    if (response.data.note) {
      commandOutput.value = response.data.note + '\n\n' + commandOutput.value;
    }

    console.log('Admin Tools - Final success value:', commandSuccess.value, 'Exit code:', response.data.exit_code);

    if (commandSuccess.value) {
      $q.notify({
        type: 'positive',
        message: 'Comando ejecutado exitosamente',
      });
    } else {
      $q.notify({
        type: 'negative',
        message: 'Error al ejecutar el comando',
      });
    }
  } catch (error) {
    console.error('Admin Tools - Request error:', {
      error: error,
      response: error.response,
      response_data: error.response?.data,
      response_status: error.response?.status,
      message: error.message,
    });

    commandSuccess.value = false;

    // Check if it's a network error vs API error
    if (!error.response) {
      // Network error or timeout
      commandOutput.value = 'Error de conexión: ' + error.message;
      $q.notify({
        type: 'negative',
        message: 'Error de conexión al ejecutar el comando',
        timeout: 5000,
      });
    } else {
      // API returned an error response
      const errorMessage = error.response?.data?.message || error.response?.data?.output || error.message || 'Error desconocido';
      const errorDetails = error.response?.data?.output || error.response?.data?.error || '';
      commandOutput.value = errorMessage + (errorDetails && errorDetails !== errorMessage ? '\n\n' + errorDetails : '');
      $q.notify({
        type: 'negative',
        message: 'Error al ejecutar el comando: ' + errorMessage,
        timeout: 5000,
      });
    }
  } finally {
    commandLoading.value = false;
  }
};

// Execute command with streaming output
const executeCommandStreaming = async (command, options) => {
  return new Promise((resolve, reject) => {
    // Build query string for POST data
    const params = new URLSearchParams();
    params.append('command', command);
    params.append('stream', 'true');
    if (options && Object.keys(options).length > 0) {
      params.append('options', JSON.stringify(options));
    }

    // Use fetch with POST for streaming
    fetch(route('admin-tools.command'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'text/event-stream',
      },
      body: params.toString(),
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        const readStream = () => {
          reader.read().then(({ done, value }) => {
            if (done) {
              commandLoading.value = false;
              resolve();
              return;
            }

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
              if (line.startsWith('data: ')) {
                try {
                  const data = JSON.parse(line.slice(6));

                  if (data.type === 'complete') {
                    commandLoading.value = false;
                    commandSuccess.value = data.success;
                    if (data.output) {
                      commandOutput.value += data.output;
                    }

                    // Scroll to bottom
                    if (outputRef.value) {
                      outputRef.value.scrollTop = outputRef.value.scrollHeight;
                    }

                    if (data.success) {
                      $q.notify({
                        type: 'positive',
                        message: 'Comando ejecutado exitosamente',
                      });
                    } else {
                      $q.notify({
                        type: 'negative',
                        message: 'Comando completado con errores',
                      });
                    }
                    resolve();
                    return;
                  } else if (data.type === 'output' || data.type === 'error') {
                    // Append output in real-time
                    commandOutput.value += data.data;

                    // Auto-scroll to bottom (use nextTick to ensure DOM is updated)
                    setTimeout(() => {
                      if (outputRef.value) {
                        outputRef.value.scrollTop = outputRef.value.scrollHeight;
                      }
                    }, 50);
                  }
                } catch (e) {
                  console.error('Error parsing SSE data:', e, line);
                }
              }
            }

            readStream();
          }).catch(error => {
            commandLoading.value = false;
            commandSuccess.value = false;
            commandOutput.value += '\n\n[ERROR] ' + error.message;
            $q.notify({
              type: 'negative',
              message: 'Error en la conexión de streaming: ' + error.message,
            });
            reject(error);
          });
        };

        readStream();
      })
      .catch(error => {
        commandLoading.value = false;
        commandSuccess.value = false;
        commandOutput.value = 'Error al iniciar streaming: ' + error.message;
        $q.notify({
          type: 'negative',
          message: 'Error al ejecutar el comando: ' + error.message,
        });
        reject(error);
      });
  });
};

// Execute SQL query
const executeQuery = async () => {
  if (!sqlQuery.value.trim()) {
    $q.notify({
      type: 'negative',
      message: 'Por favor ingresa una consulta SQL',
    });
    return;
  }

  // Confirmation for non-SELECT queries
  if (queryType.value !== 'select') {
    const confirmed = await $q.dialog({
      title: 'Confirmar ejecución',
      message: `Estás a punto de ejecutar una consulta ${queryType.value.toUpperCase()}. Esto puede modificar datos en la base de datos. ¿Estás seguro?`,
      cancel: true,
      persistent: true,
    });

    if (!confirmed) {
      return;
    }
  }

  queryLoading.value = true;
  queryResults.value = null;
  querySuccess.value = false;
  queryError.value = '';
  queryRowCount.value = 0;
  queryColumns.value = [];

  try {
    const response = await axios.post(route('admin-tools.query'), {
      query: sqlQuery.value.trim(),
      type: queryType.value,
    });

    querySuccess.value = response.data.success;

    if (querySuccess.value) {
      if (queryType.value === 'select') {
        queryResults.value = response.data.results || [];
        queryRowCount.value = response.data.count || 0;

        // Generate columns from first row if results exist
        if (queryResults.value.length > 0) {
          queryColumns.value = Object.keys(queryResults.value[0]).map(key => ({
            name: key,
            required: false,
            label: key,
            align: 'left',
            field: key,
            sortable: true,
          }));
        }
      } else {
        queryRowCount.value = response.data.affected_rows || 0;
      }

      $q.notify({
        type: 'positive',
        message: response.data.message || 'Consulta ejecutada exitosamente',
      });
    } else {
      queryError.value = response.data.error || response.data.message || 'Error desconocido';
      $q.notify({
        type: 'negative',
        message: 'Error al ejecutar la consulta',
      });
    }
  } catch (error) {
    querySuccess.value = false;
    queryError.value = error.response?.data?.error || error.response?.data?.message || error.message || 'Error desconocido';
    $q.notify({
      type: 'negative',
      message: 'Error al ejecutar la consulta',
    });
  } finally {
    queryLoading.value = false;
  }
};
</script>
