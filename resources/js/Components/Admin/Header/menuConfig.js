// import helper
import { hasAnyPermission } from '@/Utils/Permissions';

// import icons
import {
    LayoutDashboard,
    Ticket,
    PlusCircle,
    List,
    AlertCircle,
    UserCheck,
    Users2,
    Database,
    Building2,
    Tags,
    Shield,
    CheckSquare,
    BarChart3,
    UserCog,
    Key,
    Users,
    Settings,
    Activity,
    FileText
} from "lucide-vue-next";

/**
 * Menu items configuration - TickTrack Helpdesk
 * Struktur yang lebih ringkas agar tidak terlalu panjang ke samping
 */
export const menuItems = [
    {
        name: "Dashboard",
        icon: LayoutDashboard,
        href: "/admin/dashboard",
        permissions: [],
        description: "Statistik tiket dan performa harian",
    },
    {
        name: "Tiket",
        icon: Ticket,
        permissions: ['create-ticket', 'view-own-ticket', 'triage-ticket', 'view-all-ticket'],
        dropdown: [
            {
                name: "Semua Tiket",
                href: "/admin/tickets",
                icon: Database,
                description: "Database seluruh tiket sistem",
                permissions: ['view-all-ticket']
            },
            {
                name: "Buat Tiket",
                href: "/admin/tickets/create",
                icon: PlusCircle,
                description: "Laporkan kendala IT baru",
                permissions: ['create-ticket']
            },
            {
                name: "Tiket Saya",
                href: "/admin/tickets/my-tickets",
                icon: List,
                description: "Daftar laporan yang Anda buat",
                permissions: ['view-own-ticket']
            },
            {
                name: "Perlu Triage",
                href: "/admin/tickets/triage",
                icon: AlertCircle,
                description: "Verifikasi tiket oleh Helpdesk",
                permissions: ['triage-ticket']
            },
        ],
    },
    {
        name: "Master Data",
        icon: Database,
        permissions: ['manage-users', 'manage-units', 'manage-categories', 'manage-priorities'],
        dropdown: [
            {
                name: "User",
                href: "/admin/users",
                icon: Users,
                description: "Manajemen data pengguna sistem",
                permissions: ['manage-users']
            },
            {
                name: "Unit Kerja",
                href: "/admin/units",
                icon: Building2,
                description: "Kelola struktur organisasi",
                permissions: ['manage-units']
            },
            {
                name: "Kategori",
                href: "/admin/categories",
                icon: Tags,
                description: "Klasifikasi kategori kendala TI",
                permissions: ['manage-categories']
            },
            {
                name: "Prioritas",
                href: "/admin/priorities",
                icon: Shield,
                description: "Atur skala urgensi & SLA tiket",
                permissions: ['manage-priorities']
            },
        ],
    },
    {
        name: "Laporan",
        icon: BarChart3,
        permissions: ['view-reports', 'approve-request', 'request-approval'],
        dropdown: [
            {
                name: "Laporan Tiket",
                href: "/admin/reports",
                icon: FileText,
                description: "Analisis data dan ekspor laporan",
                permissions: ['view-reports']
            },
            {
                name: "Approval",
                href: "/admin/approvals",
                icon: CheckSquare,
                description: "Persetujuan permintaan khusus IT",
                permissions: ['approve-request', 'request-approval']
            },
        ],
    },
    {
        name: "Pengaturan",
        icon: Settings,
        permissions: ['manage-roles', 'manage-permissions', 'system-config', 'view-system-logs'],
        dropdown: [
            {
                name: "Roles",
                href: "/admin/roles",
                icon: Shield,
                description: "Hak akses kelompok pengguna",
                permissions: ['manage-roles']
            },
            {
                name: "Permissions",
                href: "/admin/permissions",
                icon: Key,
                description: "Daftar izin fitur aplikasi",
                permissions: ['manage-permissions']
            },
            {
                name: "Konfigurasi Sistem",
                href: "/admin/settings",
                icon: Settings,
                description: "Konfigurasi global aplikasi",
                permissions: ['system-config']
            },
            {
                name: "Log Aktivitas",
                href: "/admin/logs",
                icon: Activity,
                description: "Audit trail aktivitas sistem",
                permissions: ['view-system-logs']
            },
        ],
    },
];

/**
 * Filter menu utama berdasarkan permission
 */
export const getFilteredMenuItems = () => {
    return menuItems.filter(item => {
        if (!item.permissions || item.permissions.length === 0) {
            return true;
        }
        return hasAnyPermission(item.permissions);
    });
};

/**
 * Filter item dropdown berdasarkan permission
 */
export const getFilteredDropdown = (dropdownItems) => {
    if (!dropdownItems) return [];

    return dropdownItems.filter(subItem => {
        if (subItem.permissions && subItem.permissions.length > 0) {
            return hasAnyPermission(subItem.permissions);
        }
        return true;
    });
};