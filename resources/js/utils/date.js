export function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        timeZone: 'UTC'
    });
}

export function formatDateTime(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'UTC'
    });
}

export function formatDateForInput(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}

export function formatDateShort(date) {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

export function calculateAge(birthdate) {
    if (!birthdate) return null;
    const today = new Date();
    const birthDate = new Date(birthdate);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

export function getFullYear(date) {
    return new Date(date).getFullYear();
}

// Recurrence constants for recurrent events
export const recurrenceMonths = [
    { value: 1, label: 'Enero' },
    { value: 2, label: 'Febrero' },
    { value: 3, label: 'Marzo' },
    { value: 4, label: 'Abril' },
    { value: 5, label: 'Mayo' },
    { value: 6, label: 'Junio' },
    { value: 7, label: 'Julio' },
    { value: 8, label: 'Agosto' },
    { value: 9, label: 'Septiembre' },
    { value: 10, label: 'Octubre' },
    { value: 11, label: 'Noviembre' },
    { value: 12, label: 'Diciembre' }
];

export const recurrenceWeekdays = [
    { value: 0, label: 'Domingo' },
    { value: 1, label: 'Lunes' },
    { value: 2, label: 'Martes' },
    { value: 3, label: 'Miércoles' },
    { value: 4, label: 'Jueves' },
    { value: 5, label: 'Viernes' },
    { value: 6, label: 'Sábado' }
];

export const recurrenceWeeks = [
    { value: 1, label: 'Primera semana' },
    { value: 2, label: 'Segunda semana' },
    { value: 3, label: 'Tercera semana' },
    { value: 4, label: 'Cuarta semana' },
    { value: 5, label: 'Quinta semana' },
    { value: -1, label: 'Última semana' },
    { value: -2, label: 'Penúltima semana' },
    { value: -3, label: 'Antepenúltima semana' },
    { value: -4, label: 'Cuarta desde el final' },
    { value: -5, label: 'Quinta desde el final' }
];

// Helper functions for formatting recurrence labels
export function getMonthName(monthNumber) {
    const month = recurrenceMonths.find(m => m.value === monthNumber);
    return month ? month.label : `Mes ${monthNumber}`;
}

export function getWeekdayName(weekdayNumber) {
    const weekday = recurrenceWeekdays.find(w => w.value === weekdayNumber);
    return weekday ? weekday.label : `Día ${weekdayNumber}`;
}

export function getWeekLabel(weekNumber) {
    const week = recurrenceWeeks.find(w => w.value === weekNumber);
    if (week) {
        // Remove "semana" from the label for display in recurrence format
        return week.label.replace(' semana', '');
    }
    return `Semana ${weekNumber}`;
}

// Arrays for backward compatibility with Index.vue and Show.vue
export const monthNames = recurrenceMonths.map(m => m.label);
export const weekdayNames = recurrenceWeekdays.map(w => w.label);
export const weekLabels = {
    1: 'Primer',
    2: 'Segundo',
    3: 'Tercero',
    4: 'Cuarto',
    5: 'Quinto',
    '-1': 'Último',
    '-2': 'Penúltimo',
    '-3': 'Antepenúltimo',
    '-4': 'Cuarto desde el final',
    '-5': 'Quinto desde el final'
};

// Day name arrays (JavaScript format: 0=Sunday, 1=Monday, etc.)
export const dayNamesFull = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
export const dayNames3Letter = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
export const dayNames2Letter = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'];

// Day name objects (Schedule format: 1=Monday, 2=Tuesday, ..., 7=Sunday)
export const dayNamesFullSchedule = {
    1: 'Lunes',
    2: 'Martes',
    3: 'Miércoles',
    4: 'Jueves',
    5: 'Viernes',
    6: 'Sábado',
    7: 'Domingo'
};

export const dayNames2LetterSchedule = {
    1: 'Lu',
    2: 'Ma',
    3: 'Mi',
    4: 'Ju',
    5: 'Vi',
    6: 'Sá',
    7: 'Do'
};

export const dayNames2LetterUppercaseSchedule = {
    1: 'LU',
    2: 'MA',
    3: 'MI',
    4: 'JU',
    5: 'VI',
    6: 'SA',
    7: 'DO'
};

// Helper functions to get day names
// For JavaScript Date.getDay() format (0=Sunday, 1=Monday, etc.)
export function getDayNameFull(dayIndex) {
    return dayNamesFull[dayIndex] || '';
}

export function getDayName3Letter(dayIndex) {
    return dayNames3Letter[dayIndex] || '';
}

export function getDayName2Letter(dayIndex) {
    return dayNames2Letter[dayIndex] || '';
}

// For schedule format (1=Monday, 2=Tuesday, ..., 7=Sunday)
export function getDayNameFullSchedule(dayNumber) {
    return dayNamesFullSchedule[dayNumber] || '';
}

export function getDayName2LetterSchedule(dayNumber) {
    return dayNames2LetterSchedule[dayNumber] || '';
}

export function getDayName2LetterUppercaseSchedule(dayNumber) {
    return dayNames2LetterUppercaseSchedule[dayNumber] || '';
}