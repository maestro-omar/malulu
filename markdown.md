# Project: School Management System - Malulu

This is a school management system built using Laravel 10, Inertia.js, Vue 3, and Vite.

## General Structure

- Routes are written in Spanish and grouped by user type:
  - `/sistema` for authenticated users
  - `routes/system-admin.php` contains superadmin routes
  - `routes/school-admin.php` contains routes for most users because users are always related to one or more schools

- We use `spatie/laravel-permission` to manage roles and permissions
- Controllers are organized under `app/Http/Controllers` in subfolders:
  - `Auth/` for authentication
  - `System/` for views and logic for system-admin routes
  - `School/` for views and logic for school-admin routes

- We use `diglactic/laravel-breadcrumbs` for breadcrumb navigation
- Frontend pages are located in `resources/js/Pages`, organized by entity, each with `Index`, `Create`, `Edit`, `Show`, etc.

## Main Models (in `app/Models`)

- `User`: may have multiple roles (e.g., teacher, guardian, student, admin)
- `School`, `Course`, `AcademicYear`, `ClassSubject`, `SchoolLevel`, `SchoolShift`: define academic structure
- `RoleRelationship`: defines user-role-school-(level) relation (a user with some role in a school may be for specific level: primary/secondary...)
- `StudentCourse`, `TeacherCourse`: link courses with RoleRelationship (so a user-teacher is assigned to a course; a user-student is enrolled to a course)
- `File`, `FileType`, `FileSubtype`: file/document management system
- Base models include `BaseModel` and `BaseCatalogModel` for shared functionality

## Services

- Business logic is encapsulated in services (e.g., `UserService`, `SchoolService`, `CourseService`) located in `app/Services`

## Frontend (Vue 3 + Inertia)

- Shared components live in `resources/js/Components`
- Entity-specific views are in `resources/js/Pages`
- User dashboards vary by role: see `DashboardPanels/TeacherPanel.vue`, `StudentPanel.vue`, etc.
- Utility functions are in `resources/js/utils/` (e.g., `date.js`, `permissions.js`)

## Conventions and Style

- Class and file names are in English; route names and UI text are in Spanish
- Pages and forms use Inertia modals and reusable Vue components styled with Tailwind CSS
- Relationships should prioritize clarity, knowing a user can have multiple roles (e.g., `user-student -> studentCourses -> course`)

## Additional Notes

- Migrations and seeders are organized by entity
- Some controllers are scoped (e.g., `School/UserController.php` vs. `System/UserAdminController.php`) depending on admin level or context
