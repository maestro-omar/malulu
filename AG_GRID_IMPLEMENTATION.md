# AG Grid Implementation for Students Table

## Overview
This implementation adds AG Grid functionality to display students data in a responsive, feature-rich table with Spanish labels and proper navigation.

## Features Implemented

### 1. AG Grid Setup
- **Package**: `ag-grid-vue3` (already installed)
- **Modules**: All Community modules registered in `resources/js/app.js`
- **Theme**: Alpine theme with custom styling

### 2. Column Configuration
The table displays the following columns with Spanish labels:

| Column | Field | Description |
|--------|-------|-------------|
| Foto | photo | Student photo with fallback to default image |
| Apellido | lastname | Student's last name |
| Nombre | firstname | Student's first name |
| Número de Identificación | id_number | Student's ID number |
| Género | gender | Gender (M/F) displayed as "Masculino"/"Femenino" |
| Fecha de Nacimiento | birthdate | Birth date formatted in Spanish locale |
| Edad | age | Calculated age |
| Fecha de Inicio | rel_start_date | Enrollment start date |
| Motivo de Salida | rel_end_reason | Reason for leaving (if applicable) |
| Acciones | - | "Ver Detalle" button linking to student detail page |

### 3. Grid Features
- **Sorting**: All columns are sortable (except photo and actions)
- **Filtering**: Text and number filters available
- **Pagination**: 10 items per page
- **Responsive**: Auto-sizing columns
- **Hover effects**: Row highlighting on hover
- **Custom styling**: Tailwind CSS integration

### 4. Navigation
The "Ver Detalle" button generates proper Inertia routes using:
- Route name: `school.course.view-student`
- Parameters: school slug, school level, course ID, and student ID/name

## Usage

### In Vue Component
```vue
<template>
  <StudentsTable 
    :students="students" 
    :courseId="getCourseSlug(course)"
    :schoolLevel="selectedLevel.code"
    :schoolSlug="school.slug"
  />
</template>

<script setup>
import StudentsTable from '@/Components/admin/StudentsTable.vue';
</script>
```

### Required Props
- `students`: Array of student objects from `CourseService::getStudents()`
- `courseId`: Course identifier (from `getCourseSlug()`)
- `schoolLevel`: School level code
- `schoolSlug`: School slug for route generation

### Data Structure
The component expects student objects with the following structure (from `CourseService::parseRelatedStudent()`):
```javascript
{
  id: number,
  photo: string,
  firstname: string,
  lastname: string,
  id_number: string,
  gender: 'M' | 'F',
  birthdate: 'YYYY-MM-DD',
  age: number,
  rel_start_date: 'YYYY-MM-DD',
  rel_end_reason: string | null,
  // ... other fields
}
```

## Customization

### Styling
The component uses CSS custom properties for theming:
```css
:deep(.ag-theme-alpine) {
  --ag-header-height: 50px;
  --ag-row-height: 50px;
  --ag-header-background-color: #f8fafc;
  --ag-header-foreground-color: #374151;
  --ag-border-color: #e5e7eb;
  --ag-row-hover-color: #f3f4f6;
}
```

### Column Configuration
To modify columns, edit the `columnDefs` computed property in the component:
- Add/remove columns
- Change column widths
- Modify cell renderers
- Update sorting/filtering behavior

### Route Generation
The component automatically generates Inertia routes for the "Ver Detalle" button using the Ziggy route helper.

## Dependencies
- `ag-grid-vue3`: Vue 3 wrapper for AG Grid
- `ag-grid-community`: Core AG Grid functionality
- `@inertiajs/vue3`: For route generation
- Tailwind CSS: For styling

## Notes
- The component is already integrated into the Course Show page
- AG Grid modules are registered globally in `app.js`
- The implementation follows the existing code patterns in the project
- All text is in Spanish as requested
- The grid is responsive and works well on different screen sizes
