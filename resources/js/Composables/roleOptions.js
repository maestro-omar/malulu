import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function roleOptions() {
  const page = usePage()
  
  const options = computed(() => {
    return page.props.constants?.catalogs?.roles || {}
  })

  return { options }
} 