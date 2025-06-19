import { ref, onMounted } from 'vue'
import axios from 'axios'

const options = ref({})

export function roleOptions() {
  onMounted(async () => {
    const { data } = await axios.get('/json-options/roles')
    options.value = data
  })

  return { options }
} 