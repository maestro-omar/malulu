import { slugify } from './strings';

/**
 * Generate route for editing a student in a school context
 * @param {Object} school - School object with slug property
 * @param {Object} student - Student/User object with id, name, and lastname properties
 * @param {string} action - show / edit
 * @returns {string} Generated route URL
 */
export function route_school_staff(school, staff, action) {
    return route('school.staff.'+action, {
        'school': school.slug,
        'idAndName': staff.id + '-' + slugify(staff.name + ' ' + staff.lastname)
    });
}
export function route_school_student(school, student, action) {
    return route('school.student.'+action, {
        'school': school.slug,
        'idAndName': student.id + '-' + slugify(student.name + ' ' + student.lastname)
    });
}