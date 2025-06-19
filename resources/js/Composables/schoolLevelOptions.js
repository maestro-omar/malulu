import { ref, onMounted } from 'vue'
import axios from 'axios'

const options = ref({})

export function schoolLevelOptions() {
  onMounted(async () => {
    const { data } = await axios.get('/json-options/school-level')
    options.value = data
  })

  return { options }
}