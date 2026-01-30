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
    const allPermissions = usePage().props.auth.permissions;

    if (!allPermissions || !permissions) return false;

    let hasPermission = false;
    permissions.forEach(function (item) {
        if (allPermissions[item]) {
            hasPermission = true;
        }
    });

    return hasPermission;
}

export function hasRole(role) {
    const allRoles = usePage().props.auth.roles;

    if (!allRoles) return false;

    return allRoles.includes(role);
}
