import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function schoolShiftOptions() {
  const page = usePage()
  
  const options = computed(() => {
    return page.props.constants?.catalogs?.schoolShifts || {}
  })

  return { options }
}