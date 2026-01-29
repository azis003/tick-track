// import usePage dari inertia vue
import { usePage } from '@inertiajs/vue3';

/**
 * Fungsi untuk mengecek apakah user memiliki SATU permission tertentu.
 * Contoh penggunaan: hasPermission('create-ticket')
 */
export function hasPermission(permissions) {
    return hasAnyPermission([permissions]);
}

/**
 * Fungsi untuk mengecek apakah user memiliki SALAH SATU dari list permission yang diberikan.
 * Karena di HandleInertiaRequests.php data ada di props.auth.permissions
 */
export function hasAnyPermission(permissions) {
    //1. ambil data permission yang ada di usePage
    const allPermissions = usePage().props.auth.permissions;

    //2. loop setiap permission yang diminta dan cek di list 'allPermissions'
    let hasPermission = false;
    permissions.forEach(function (item) {
        if (allPermissions[item]) {
            hasPermission = true;
        }
    });

    return hasPermission;
}

export function hasRole(role) {
    //1. ambil data roles dari shared data
    const allRoles = usePage().props.auth.roles;

    if (!auth.user || !auth.user.roles) {
        return false;
    }

    //2. cek apakah user memiliki role yang diminta
    return auth.user.roles.includes(roleName);
}
