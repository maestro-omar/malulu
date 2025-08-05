import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

/**
 * Composable for table search and sort functionality
 * @param {Object} options - Configuration options
 * @param {string} options.routeName - The route name for navigation
 * @param {Object} options.routeParams - Additional route parameters
 * @param {Object} options.filters - Initial filters from props
 * @returns {Object} - Reactive state and methods
 */
export function useTableSearchSort(options = {}) {
    const {
        routeName,
        routeParams = {},
        filters = {}
    } = options;

    // Reactive state
    const search = ref(filters?.search || '');
    const sortField = ref(filters?.sort || '');
    const sortDirection = ref(filters?.direction || 'asc');

    let searchTimeout = null;

    /**
     * Handle search with debouncing
     */
    const handleSearch = () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(
                route(routeName, routeParams),
                {
                    search: search.value,
                    sort: sortField.value,
                    direction: sortDirection.value
                },
                { preserveState: true, preserveScroll: true }
            );
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

        router.get(
            route(routeName, routeParams),
            {
                search: search.value,
                sort: sortField.value,
                direction: sortDirection.value
            },
            { preserveState: true, preserveScroll: true }
        );
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
        router.get(
            route(routeName, routeParams),
            {
                sort: sortField.value,
                direction: sortDirection.value
            },
            { preserveState: true, preserveScroll: true }
        );
    };

    /**
     * Clear all filters and sorting
     */
    const clearAll = () => {
        search.value = '';
        sortField.value = '';
        sortDirection.value = 'asc';
        router.get(
            route(routeName, routeParams),
            {},
            { preserveState: true, preserveScroll: true }
        );
    };

    return {
        // State
        search,
        sortField,
        sortDirection,

        // Methods
        handleSearch,
        handleSort,
        getSortClass,
        clearSearch,
        clearAll
    };
}

/**
 * Simple search-only functionality for tables without sorting
 * @param {Object} options - Configuration options
 * @param {string} options.routeName - The route name for navigation
 * @param {Object} options.routeParams - Additional route parameters
 * @param {Object} options.filters - Initial filters from props
 * @returns {Object} - Reactive state and methods
 */
export function useTableSearch(options = {}) {
    const {
        routeName,
        routeParams = {},
        filters = {}
    } = options;

    // Reactive state
    const search = ref(filters?.search || '');
    let searchTimeout = null;

    /**
     * Handle search with debouncing
     */
    const handleSearch = () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(
                route(routeName, routeParams),
                { search: search.value },
                { preserveState: true, preserveScroll: true }
            );
        }, 300);
    };

    /**
     * Clear search
     */
    const clearSearch = () => {
        search.value = '';
        router.get(
            route(routeName, routeParams),
            {},
            { preserveState: true, preserveScroll: true }
        );
    };

    return {
        // State
        search,

        // Methods
        handleSearch,
        clearSearch
    };
}