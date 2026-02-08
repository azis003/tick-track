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
 * Naming Convention: module.action
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
        permissions: ['tickets.create', 'tickets.view-own', 'tickets.triage', 'tickets.view-all'],
        dropdown: [
            {
                name: "Semua Tiket",
                href: "/admin/tickets",
                icon: Database,
                description: "Database seluruh tiket sistem",
                permissions: ['tickets.view-all']
            },
            {
                name: "Tiket Saya",
                href: "/admin/tickets/my-tickets",
                icon: List,
                description: "Tiket yang menjadi tanggung jawab Anda",
                permissions: ['tickets.view-own', 'tickets.work', 'tickets.triage']
            },
        ],
    },
    {
        name: "Master Data",
        icon: Database,
        permissions: ['users.index', 'units.index', 'categories.index', 'priorities.index'],
        dropdown: [
            {
                name: "User",
                href: "/admin/users",
                icon: Users,
                description: "Manajemen data pengguna sistem",
                permissions: ['users.index']
            },
            {
                name: "Unit Kerja",
                href: "/admin/units",
                icon: Building2,
                description: "Kelola struktur organisasi",
                permissions: ['units.index']
            },
            {
                name: "Kategori",
                href: "/admin/categories",
                icon: Tags,
                description: "Klasifikasi kategori kendala TI",
                permissions: ['categories.index']
            },
            {
                name: "Prioritas",
                href: "/admin/priorities",
                icon: Shield,
                description: "Atur skala urgensi & SLA tiket",
                permissions: ['priorities.index']
            },
        ],
    },
    {
        name: "Laporan",
        icon: BarChart3,
        permissions: ['reports.view', 'approvals.approve', 'approvals.request'],
        dropdown: [
            {
                name: "Laporan Tiket",
                href: "/admin/reports",
                icon: FileText,
                description: "Analisis data dan ekspor laporan",
                permissions: ['reports.view']
            },
            {
                name: "Approval",
                href: "/admin/approvals",
                icon: CheckSquare,
                description: "Persetujuan permintaan khusus IT",
                permissions: ['approvals.approve', 'approvals.request']
            },
        ],
    },
    {
        name: "Pengaturan",
        icon: Settings,
        permissions: ['roles.index', 'permissions.index', 'system.config', 'system.logs'],
        dropdown: [
            {
                name: "Roles",
                href: "/admin/roles",
                icon: Shield,
                description: "Hak akses kelompok pengguna",
                permissions: ['roles.index']
            },
            {
                name: "Permissions",
                href: "/admin/permissions",
                icon: Key,
                description: "Daftar izin fitur aplikasi",
                permissions: ['permissions.index']
            },
            {
                name: "Konfigurasi Sistem",
                href: "/admin/settings",
                icon: Settings,
                description: "Konfigurasi global aplikasi",
                permissions: ['system.config']
            },
            {
                name: "Log Aktivitas",
                href: "/admin/logs",
                icon: Activity,
                description: "Audit trail aktivitas sistem",
                permissions: ['system.logs']
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