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
        href: "/dashboard",
        permissions: [],
        description: "Statistik tiket dan performa harian",
    },
    {
        name: "Tiket",
        icon: Ticket,
        permissions: ['tickets.create', 'tickets.view-own', 'tickets.triage', 'tickets.view-all'],
        dropdown: [
            {
                name: "Tiket Saya",
                href: "/tickets/my-tickets",
                icon: List,
                description: "Tiket yang Anda buat",
                permissions: ['tickets.view-own', 'tickets.create']
            },
            {
                name: "Daftar Tugas",
                href: "/tickets/task-queue",
                icon: CheckSquare,
                description: "Tiket yang memerlukan tindakan Anda",
                permissions: ['tickets.view-own', 'tickets.work', 'tickets.triage', 'tickets.approve']
            },
            {
                name: "Semua Tiket",
                href: "/tickets",
                icon: Database,
                description: "Lihat semua tiket dalam sistem",
                permissions: ['tickets.view-all']
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
                href: "/users",
                icon: Users,
                description: "Manajemen data pengguna sistem",
                permissions: ['users.index']
            },
            {
                name: "Unit Kerja",
                href: "/units",
                icon: Building2,
                description: "Kelola struktur organisasi",
                permissions: ['units.index']
            },
            {
                name: "Kategori",
                href: "/categories",
                icon: Tags,
                description: "Klasifikasi kategori kendala TI",
                permissions: ['categories.index']
            },
            {
                name: "Prioritas",
                href: "/priorities",
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
                href: "/reports",
                icon: FileText,
                description: "Analisis data dan ekspor laporan",
                permissions: ['reports.view']
            },
            {
                name: "Approval",
                href: "/approvals",
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
                href: "/roles",
                icon: Shield,
                description: "Hak akses kelompok pengguna",
                permissions: ['roles.index']
            },
            {
                name: "Permissions",
                href: "/permissions",
                icon: Key,
                description: "Daftar izin fitur aplikasi",
                permissions: ['permissions.index']
            },
            {
                name: "Konfigurasi Sistem",
                href: "/settings",
                icon: Settings,
                description: "Konfigurasi global aplikasi",
                permissions: ['system.config']
            },
            {
                name: "Log Aktivitas",
                href: "/logs",
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