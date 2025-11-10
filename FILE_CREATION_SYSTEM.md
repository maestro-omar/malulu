# File Creation System Documentation

## Overview

This document describes the reusable file creation system that has been implemented to avoid code duplication across different contexts (user, school, course, province). The system consists of:

1. **FileForm Component** - A reusable Vue component for file creation
2. **FileControllerTrait** - A PHP trait for handling file operations in controllers
3. **Updated Controllers** - Controllers that use the trait for consistent behavior
4. **Updated Routes** - Routes that support the new file creation flow

## Components

### 1. FileForm Component (`resources/js/Components/admin/FileForm.vue`)

A comprehensive Vue component that handles both file uploads and external URL inputs.

#### Features:
- **Dual Input Types**: Supports both file upload and external URL input
- **Dynamic Subtype Selection**: Automatically loads appropriate file subtypes based on context
- **Auto-naming**: Automatically generates nice names from filenames or URLs
- **Expiration Support**: Handles file expiration dates when required by subtype
- **Validation**: Comprehensive client-side validation
- **Error Handling**: Displays validation errors and handles submission errors

#### Props:
```javascript
{
  subTypes: Array,        // Required - Available file subtypes
  context: String,        // Required - 'user', 'school', 'course', 'province'
  contextId: [String, Number], // Required - ID of the context entity
  storeUrl: String,       // Required - URL to submit the form
  cancelUrl: String,      // Required - URL to redirect on cancel
  initialInputType: String, // Optional - 'file' or 'url' (default: 'file')
  existingFile: Object    // Optional - For editing existing files
}
```

#### Events:
- `@success` - Emitted when file is successfully created
- `@error` - Emitted when there are validation or submission errors

### 2. FileControllerTrait (`app/Traits/FileControllerTrait.php`)

A PHP trait that provides common file handling functionality for controllers.

#### Key Methods:

##### `storeFile(Request $request, string $context, $contextModel)`
Handles file creation for any context. Validates input, processes file upload or external URL, and creates the file record.

##### `getSubtypesForContext(string $context, $contextModel)`
Returns appropriate file subtypes for the given context.

##### `getStoreUrlForContext(string $context, $contextModel)`
Generates the correct store URL for the given context.

##### `getCancelUrlForContext(string $context, $contextModel)`
Generates the correct cancel URL for the given context.

#### Context Support:
- **user**: Files associated with a specific user
- **school**: Files associated with a school
- **course**: Files associated with a course
- **province**: Files associated with a province

### 3. Updated Controllers

#### FileAdminController (`app/Http/Controllers/System/FileAdminController.php`)
- Uses `FileControllerTrait`
- Handles user file creation
- Added `storeForUser()` method

#### FileController (`app/Http/Controllers/School/FileController.php`)
- Uses `FileControllerTrait`
- Handles school and course file creation
- Added `storeForSchoolDirect()` and `storeForSchool()` methods

### 4. Updated Routes

#### System Routes (`routes/system.php`)
```php
Route::post(__('routes.users') . '/{user}/' . __('routes.files'), [FileAdminController::class, 'storeForUser'])->name('users.file.store');
```

#### School Routes (`routes/school.php`)
```php
Route::post(__('routes.files'), [FileController::class, 'storeForSchoolDirect'])->name('school.file.store');
Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.files'), [FileController::class, 'storeForSchool'])->name('school.course.file.store');
```

## Usage Examples

### Creating a User File

1. **Controller Method**:
```php
public function createForUser(Request $request, User $user)
{
    $subTypes = $this->getSubtypesForContext('user', $user);
    $storeUrl = $this->getStoreUrlForContext('user', $user);
    $cancelUrl = $this->getCancelUrlForContext('user', $user);

    return Inertia::render('Files/byUser/Create', [
        'subTypes' => $subTypes,
        'context' => 'user',
        'contextId' => $user->id,
        'storeUrl' => $storeUrl,
        'cancelUrl' => $cancelUrl,
        'breadcrumbs' => Breadcrumbs::generate('users.file.create', $user),
    ]);
}

public function storeForUser(Request $request, User $user)
{
    return $this->storeFile($request, 'user', $user);
}
```

2. **Vue Page**:
```vue
<template>
  <FileForm
    :sub-types="subTypes"
    :context="context"
    :context-id="contextId"
    :store-url="storeUrl"
    :cancel-url="cancelUrl"
    @success="onSuccess"
    @error="onError"
  />
</template>
```

### Creating a School File

Similar pattern but with `context: 'school'` and school-specific URLs.

### Creating a Course File

Similar pattern but with `context: 'course'` and course-specific URLs.

## File Storage

### File Uploads
- Stored in `storage/app/public/files/{context}/`
- Filenames are slugified for consistency
- Original filenames are preserved in the database

### External URLs
- No file storage required
- URL is stored in `external_url` field
- Filename is extracted from URL for display

## Validation

### Client-side (Vue)
- File type selection required
- File or URL required (depending on input type)
- Nice name required
- URL format validation for external links

### Server-side (PHP)
- File subtype must exist
- File size limit (10MB)
- URL format validation
- Context-specific validation

## Error Handling

### Success Flow
1. File is created successfully
2. User is redirected to the file show page
3. Success message is displayed

### Error Flow
1. Validation errors are displayed in the form
2. User is redirected back to create page with errors
3. Form data is preserved for correction

## Extending the System

### Adding New Contexts

1. **Update FileControllerTrait**:
   - Add new context to `setContextForeignKey()`
   - Add new context to `getSubtypesForContext()`
   - Add new context to URL generation methods

2. **Create Controller Methods**:
   - Use the trait methods for consistent behavior
   - Add context-specific logic if needed

3. **Create Vue Page**:
   - Use the FileForm component
   - Pass appropriate props

4. **Add Routes**:
   - Add create and store routes for the new context

### Adding New File Types

1. **Database**: Add new file types and subtypes
2. **Service**: Update FileService to handle new types
3. **Trait**: Update subtype retrieval methods if needed

## Benefits

1. **Code Reuse**: Single component and trait handle all file creation scenarios
2. **Consistency**: Uniform behavior across all contexts
3. **Maintainability**: Changes to file creation logic only need to be made in one place
4. **Extensibility**: Easy to add new contexts or file types
5. **User Experience**: Consistent UI/UX across all file creation forms

## Testing

To test the complete file creation flow:

1. **User Files**: Navigate to user profile → Create file
2. **School Files**: Navigate to school → Create file
3. **Course Files**: Navigate to course → Create file
4. **Province Files**: Navigate to province → Create file

Test both file upload and external URL scenarios for each context.
