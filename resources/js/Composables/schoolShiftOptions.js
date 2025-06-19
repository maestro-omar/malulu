import { ref, onMounted } from 'vue'
import axios from 'axios'

const options = ref({})

export function schoolShiftOptions() {
  onMounted(async () => {
    const { data } = await axios.get('/json-options/school-shifts')
    options.value = data
  })

  return { options }
}