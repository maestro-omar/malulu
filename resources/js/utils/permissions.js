export function hasPermission(props, permission, schoolId = null, levelId = null) {
    if (!props.auth.user.permissionBySchool || !props.auth.user.permissionBySchool[permission]) {
        // console.log('NOT permission ' + permission);
        return false;
    }
    if (schoolId === null) {
        // Has permission in at least one school
        const any = props.auth.user.permissionBySchool[permission].length > 0;
        // console.log((any ? 'YES' : 'NO') + '  ANY permission ' + permission);
        return any;
    }
    // Has permission in the specific school or in the "superadmin", global special school
    if (!props.auth.user.permissionBySchool[permission].includes(schoolId)
        && !props.auth.user.permissionBySchool[permission].includes(props.constants.schoolGlobalId)) {
        // console.log('NOT FOR SCHOOL permission ' + permission);
        // console.log(props.constants.schoolGlobalId);
        // console.log(props.auth.user.permissionBySchool[permission]);
        return false;
    }

    if (levelId !== null) {
        //still working on permission by school level
    }
    return true;
}

export function isAdmin(user) {
    return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
}

export function isCurrentUserAdmin(props) {
    return isAdmin($page.props.auth.user);
}
