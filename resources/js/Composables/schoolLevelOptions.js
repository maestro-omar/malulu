import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function schoolLevelOptions() {
  const page = usePage()
  
  const options = computed(() => {
    return page.props.constants?.catalogs?.schoolLevels || {}
  })
  
  const loading = computed(() => false) // Always false since data is available immediately

  return { options, loading }
}