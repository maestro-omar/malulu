import { ref, onMounted } from 'vue'
import axios from 'axios'

const options = ref({})

export function schoolLevelOptions() {
  onMounted(async () => {
    const { data } = await axios.get('/json-options/school-levels')
    options.value = data
  })

  return { options }
}