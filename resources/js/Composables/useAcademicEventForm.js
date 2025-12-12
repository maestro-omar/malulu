import { ref, computed } from 'vue';

/**
 * Composable for Academic Event form logic
 * Shared between Create and Edit pages
 */
export function useAcademicEventForm(props, form) {
  // Event source type: 'recurrent' or 'scope'
  const eventSourceType = ref('scope');
  
  const eventSourceTypeOptions = [
    { label: 'Desde evento recurrente flexible', value: 'recurrent' },
    { label: 'Por alcance', value: 'scope' }
  ];

  // Scope selection for scope-based events
  const selectedScope = ref('escolar');

  const scopeOptions = [
    { label: 'Nacional', value: 'nacional' },
    { label: 'Provincial', value: 'provincial' },
    { label: 'Escolar', value: 'escolar' },
    { label: 'Cursos', value: 'cursos' }
  ];

  // Find San Luis province
  const sanLuisProvince = computed(() => {
    return props.provinces.find(p => p.code === 'sl') || props.provinces.find(p => p.name === 'San Luis');
  });

  const sanLuisProvinceId = computed(() => {
    return sanLuisProvince.value?.id || null;
  });

  const sanLuisProvinceName = computed(() => {
    return sanLuisProvince.value?.name || 'San Luis';
  });

  // Filter event types based on selected scope
  const filteredEventTypes = computed(() => {
    if (eventSourceType.value !== 'scope') return [];

    // For 'cursos', use 'escolar' scope but enable course selection
    const scopeToUse = selectedScope.value === 'cursos' ? 'escolar' : selectedScope.value;

    const types = props.eventTypesByScope[scopeToUse] || [];
    return types.map(type => ({
      id: type.id,
      label: type.label,
      code: type.code,
      scope: type.scope
    }));
  });

  const handleEventSourceTypeChange = (value) => {
    if (value === 'recurrent') {
      // Clear scope-based fields
      form.event_type_id = '';
      form.province_id = '';
      form.courses = [];
      selectedScope.value = 'escolar';
    } else if (value === 'scope') {
      // Clear recurrent event
      form.recurrent_event_id = null;
    }
  };

  const calculateMondayDate = (recurrentEvent) => {
    if (!recurrentEvent.date) return null;

    // Parse the date from the recurrent event (format: YYYY-MM-DD)
    const eventDate = new Date(recurrentEvent.date + 'T00:00:00');
    const month = eventDate.getMonth(); // 0-11
    const day = eventDate.getDate();

    // Determine year: current year if today is before the date, otherwise next year
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const currentYear = today.getFullYear();
    // Create date in current year with the same month/day
    const dateThisYear = new Date(currentYear, month, day);
    dateThisYear.setHours(0, 0, 0, 0);

    let targetYear = currentYear;
    if (dateThisYear < today) {
      // Date has already passed this year, use next year
      targetYear = currentYear + 1;
    }

    // Create the date in the target year
    const targetDate = new Date(targetYear, month, day);
    targetDate.setHours(0, 0, 0, 0);
    const dayOfWeek = targetDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

    // Determine which date to use:
    // - If Monday (1), Saturday (6), or Sunday (0) -> keep the date
    // - If Tuesday (2) or Wednesday (3) -> previous Monday
    // - If Thursday (4) or Friday (5) -> next Monday
    let finalDate = new Date(targetDate);

    if (dayOfWeek === 1) {
      // Monday -> keep it
      // No change needed
    } else if (dayOfWeek === 6 || dayOfWeek === 0) {
      // Saturday or Sunday -> keep it
      // No change needed
    } else if (dayOfWeek >= 2 && dayOfWeek <= 3) {
      // Tuesday or Wednesday -> previous Monday
      const mondayOffset = -(dayOfWeek - 1);
      finalDate.setDate(day + mondayOffset);
    } else if (dayOfWeek >= 4 && dayOfWeek <= 5) {
      // Thursday or Friday -> next Monday
      const mondayOffset = 8 - dayOfWeek;
      finalDate.setDate(day + mondayOffset);
    }

    // Format as YYYY-MM-DD for the date input
    const year = finalDate.getFullYear();
    const finalMonth = String(finalDate.getMonth() + 1).padStart(2, '0');
    const finalDay = String(finalDate.getDate()).padStart(2, '0');
    return {
      date: `${year}-${finalMonth}-${finalDay}`,
      year: year
    };
  };

  const handleRecurrentEventSelected = (recurrentEventId) => {
    if (!recurrentEventId) {
      form.title = '';
      form.date = '';
      form.event_type_id = '';
      return;
    }

    // Convert to number for comparison (select inputs return strings)
    const eventId = Number(recurrentEventId);
    const recurrentEvent = props.recurrentEvents.find(re => Number(re.id) === eventId);

    if (recurrentEvent) {
      form.title = recurrentEvent.title;
      if (recurrentEvent.event_type) {
        form.event_type_id = recurrentEvent.event_type.id;
      }

      // Calculate Monday date based on the recurrent event date
      const dateResult = calculateMondayDate(recurrentEvent);
      if (dateResult) {
        form.date = dateResult.date;

        // Auto-select academic year that matches the calculated year
        const matchingAcademicYear = props.academicYears.find(ay => ay.year === dateResult.year);
        if (matchingAcademicYear) {
          form.academic_year_id = matchingAcademicYear.id;
        }
      }
    }
  };

  const handleScopeChange = (scope) => {
    // Reset event type when scope changes
    form.event_type_id = '';

    // Handle province selection for provincial scope
    if (scope === 'provincial') {
      form.province_id = sanLuisProvinceId.value || '';
      form.courses = [];
    } else if (scope === 'nacional') {
      form.province_id = '';
      form.courses = [];
    } else if (scope === 'escolar') {
      form.province_id = '';
      form.courses = [];
    } else if (scope === 'cursos') {
      form.province_id = '';
      // Keep courses selection available
    }
  };

  return {
    eventSourceType,
    eventSourceTypeOptions,
    selectedScope,
    scopeOptions,
    sanLuisProvinceId,
    sanLuisProvinceName,
    filteredEventTypes,
    handleEventSourceTypeChange,
    handleRecurrentEventSelected,
    handleScopeChange,
    calculateMondayDate
  };
}

