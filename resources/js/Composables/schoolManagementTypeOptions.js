import { ref, onMounted } from 'vue'
import axios from 'axios'

const options = ref({})

export function schoolManagementTypeOptions() {
  onMounted(async () => {
    const { data } = await axios.get('/json-options/school-management-types')
    options.value = data
  })

  return { options }
}