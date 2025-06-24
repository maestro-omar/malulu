export function hasPermission(props, permission, schoolId = null, levelId = null) {
    if (!props.auth.user.permissionBySchool || !props.auth.user.permissionBySchool[permission]) return false;
    if (schoolId === null) {
        // Has permission in at least one school
        return props.auth.user.permissionBySchool[permission].length > 0;
    }
    // Has permission in the specific school or in the "superadmin", global special school
    if (!props.auth.user.permissionBySchool[permission].includes(schoolId) || props.auth.user.permissionBySchool[permission].includes(props.constants.schoolGlobalId)) return false;

    if (levelId !== null) {

    }
    return true;
}
