<script setup>
import { computed } from 'vue'
import {
    PlusCircle,
    CheckCircle,
    Clock,
    ArrowRight,
    Play,
    Pause,
    Send,
    XCircle,
    RotateCcw,
    AlertCircle,
    UserCheck,
    ShieldCheck,
    ShieldX
} from 'lucide-vue-next'

/**
 * Timeline Component
 * Menampilkan riwayat tiket (histori keluhan) dengan format:
 * [Icon] Deskripsi aksi .............. Waktu & Oleh Siapa
 *   |
 * [Icon] Deskripsi aksi .............. Waktu & Oleh Siapa
 */
const props = defineProps({
    logs: {
        type: Array,
        required: true,
        default: () => []
    }
})

// Icon per action
const actionIcons = {
    create: PlusCircle,
    triage: CheckCircle,
    assign: ArrowRight,
    self_handle: UserCheck,
    accept: UserCheck,
    start: Play,
    pending: Pause,
    resume: Play,
    resolve: CheckCircle,
    close: CheckCircle,
    reopen: RotateCcw,
    auto_close: Clock,
    return: RotateCcw,
    request_approval: Send,
    approve: ShieldCheck,
    reject: ShieldX,
    approval_approved: ShieldCheck,
    approval_rejected: ShieldX,
}

// Warna icon per action — dipisah text & bg agar bisa dipakai di border juga
const actionStyles = {
    create: { text: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-200' },
    triage: { text: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-200' },
    assign: { text: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-200' },
    self_handle: { text: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-200' },
    accept: { text: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-200' },
    start: { text: 'text-yellow-600', bg: 'bg-yellow-50', border: 'border-yellow-200' },
    pending: { text: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-200' },
    resume: { text: 'text-yellow-600', bg: 'bg-yellow-50', border: 'border-yellow-200' },
    resolve: { text: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' },
    close: { text: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' },
    reopen: { text: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' },
    auto_close: { text: 'text-gray-600', bg: 'bg-gray-50', border: 'border-gray-200' },
    return: { text: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-200' },
    request_approval: { text: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-200' },
    approve: { text: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' },
    reject: { text: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' },
    approval_approved: { text: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' },
    approval_rejected: { text: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' },
}

const defaultStyle = { text: 'text-gray-600', bg: 'bg-gray-50', border: 'border-gray-200' }

/**
 * Generate deskripsi singkat berdasarkan action
 * Format simpel: "Tiket dibuat", "Tiket diverifikasi", "Tiket dikerjakan"
 */
const getDescription = (log) => {
    switch (log.action) {
        case 'create':          return 'Tiket dibuat'
        case 'triage':          return 'Tiket diverifikasi'
        case 'assign':          return 'Tiket ditugaskan'
        case 'self_handle':     return 'Tiket dikerjakan'
        case 'accept':          return 'Tiket diterima'
        case 'start':           return 'Tiket dikerjakan'
        case 'pending':
            return log.to_status === 'pending_user'
                ? 'Dikembalikan kepelapor'
                : 'Menunggu pihak eksternal'
        case 'resume':          return 'Tiket dilanjutkan'
        case 'resolve':         return 'Tiket diselesaikan'
        case 'close':           return 'Tiket ditutup'
        case 'auto_close':      return 'Tiket ditutup otomatis'
        case 'reopen':          return 'Tiket dibuka kembali'
        case 'return':          return 'Tiket dikembalikan'
        case 'request_approval': return 'Approval diajukan'
        case 'approve':
        case 'approval_approved': return 'Approval disetujui'
        case 'reject':
        case 'approval_rejected': return 'Approval ditolak'
        default:                return log.action
    }
}

/**
 * Format tanggal ke format: 2026-02-12 12:22:21
 */
const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    const seconds = String(date.getSeconds()).padStart(2, '0')

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}

const sortedLogs = computed(() => {
    return [...props.logs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})
</script>

<template>
    <div class="flow-root">
        <ul role="list">
            <li v-for="(log, index) in sortedLogs" :key="log.id">
                <div class="relative pb-6 last:pb-0">
                    <!-- Connector line (vertical) -->
                    <span
                        v-if="index !== sortedLogs.length - 1"
                        class="absolute left-[18px] top-10 bottom-0 w-0.5 bg-gray-200"
                        aria-hidden="true"
                    />

                    <!-- Row: Icon + Description + Timestamp -->
                    <div class="relative flex items-start gap-4">
                        <!-- Icon Circle -->
                        <div class="flex-shrink-0 relative z-10">
                            <div
                                :class="[
                                    (actionStyles[log.action] || defaultStyle).bg,
                                    (actionStyles[log.action] || defaultStyle).border,
                                    'w-9 h-9 rounded-full flex items-center justify-center border-2'
                                ]"
                            >
                                <component
                                    :is="actionIcons[log.action] || AlertCircle"
                                    :class="[
                                        (actionStyles[log.action] || defaultStyle).text,
                                        'w-4 h-4'
                                    ]"
                                />
                            </div>
                        </div>

                        <!-- Info (stacked) -->
                        <div class="flex-1 min-w-0 pt-1">
                            <p
                                :class="[
                                    'text-sm font-semibold',
                                    (actionStyles[log.action] || defaultStyle).text
                                ]"
                            >
                                {{ getDescription(log) }}
                            </p>
                            <p v-if="log.user" class="text-xs text-gray-500 mt-0.5">
                                Oleh {{ log.user.name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ formatDate(log.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Empty state -->
        <div v-if="!logs || logs.length === 0" class="text-center py-8 text-gray-400">
            <Clock class="mx-auto h-10 w-10 text-gray-300 mb-3" />
            <p class="text-sm font-medium">Belum ada riwayat</p>
            <p class="text-xs mt-1">Aktivitas tiket akan tercatat di sini</p>
        </div>
    </div>
</template>
