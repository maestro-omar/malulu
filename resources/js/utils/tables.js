import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

/**
 * Composable for table search and sort functionality (client-side, no ajax)
 * @param {Object} options - Configuration options
 * @param {Array} options.rows - The table data rows to filter/sort
 * @param {Object} options.filters - Initial filters from props
 * @returns {Object} - Reactive state and methods
 */


export function useTableSearchSort(options = {}) {
    const {
        rows = [],
        filters = {}
    } = options;

    // Reactive state
    const search = ref(filters?.search || '');
    const sortField = ref(filters?.sort || '');
    const sortDirection = ref(filters?.direction || 'asc');

    let searchTimeout = null;

    /**
     * Handle search with debouncing (updates search value, triggers computed)
     */
    const handleSearch = (value) => {
        if (typeof value === 'string') {
            search.value = value;
        }
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            // No ajax, just update search value
        }, 300);
    };

    /**
     * Handle column sorting with toggle logic
     * @param {string} field - The field to sort by
     */
    const handleSort = (field) => {
        if (sortField.value === field) {
            // Toggle direction if same field
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            // New field, default to ascending
            sortField.value = field;
            sortDirection.value = 'asc';
        }
        // No ajax, just update sort state
    };

    /**
     * Get CSS class for sorted columns
     * @param {string} field - The field to check
     * @returns {string} - CSS class name
     */
    const getSortClass = (field) => {
        if (sortField.value !== field) return '';
        return sortDirection.value === 'asc' ? 'table__th--sorted-asc' : 'table__th--sorted-desc';
    };

    /**
     * Clear search but preserve sorting
     */
    const clearSearch = () => {
        search.value = '';
    };

    /**
     * Clear all filters and sorting
     */
    const clearAll = () => {
        search.value = '';
        sortField.value = '';
        sortDirection.value = 'asc';
    };

    /**
     * Computed filtered and sorted rows
     */
    const filteredRows = computed(() => {
        let filtered = Array.isArray(rows) ? rows.slice() : [];

        // Filter by search (case-insensitive, checks all string fields)
        if (search.value) {
            const s = search.value.toLowerCase();
            filtered = filtered.filter(row => {
                // Check all string fields in the row
                return Object.values(row).some(val => {
                    if (val == null) return false;
                    if (typeof val === 'string') {
                        return val.toLowerCase().includes(s);
                    }
                    // Optionally, check nested user object (for StudentsTable)
                    if (typeof val === 'object' && val !== null) {
                        return Object.values(val).some(v =>
                            typeof v === 'string' && v.toLowerCase().includes(s)
                        );
                    }
                    return false;
                });
            });
        }

        // Sort
        if (sortField.value) {
            filtered.sort((a, b) => {
                let aVal = a[sortField.value];
                let bVal = b[sortField.value];

                // If nested user object (for StudentsTable), try to get from user
                if (aVal === undefined && a.user && a.user[sortField.value] !== undefined) {
                    aVal = a.user[sortField.value];
                }
                if (bVal === undefined && b.user && b.user[sortField.value] !== undefined) {
                    bVal = b.user[sortField.value];
                }

                // Fallback to empty string for undefined/null
                aVal = aVal == null ? '' : aVal;
                bVal = bVal == null ? '' : bVal;

                // Compare numbers or strings
                if (typeof aVal === 'number' && typeof bVal === 'number') {
                    return sortDirection.value === 'asc' ? aVal - bVal : bVal - aVal;
                }
                // Compare as strings
                return sortDirection.value === 'asc'
                    ? String(aVal).localeCompare(String(bVal))
                    : String(bVal).localeCompare(String(aVal));
            });
        }

        return filtered;
    });

    return {
        // State
        search,
        sortField,
        sortDirection,
        filteredRows,

        // Methods
        handleSearch,
        handleSort,
        getSortClass,
        clearSearch,
        clearAll
    };
}
