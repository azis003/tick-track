<script setup>
import { computed } from 'vue'
import { CheckCircle, Clock } from 'lucide-vue-next'

/**
 * Timeline Component
 * Menampilkan riwayat tiket dengan format 2 kolom spacious:
 * [Icon] Deskripsi aksi                    Tanggal Jam
 *                                          Oleh: Nama User
 */
const props = defineProps({
    logs: {
        type: Array,
        required: true,
        default: () => []
    }
})

/**
 * Generate deskripsi singkat berdasarkan action
 */
const getDescription = (log) => {
    switch (log.action) {
        case 'create':          return 'Tiket Dibuat'
        case 'triage':          return 'Tiket Diverifikasi'
        case 'assign':          return 'Tiket Ditugaskan'
        case 'self_handle':     return 'Tiket Dikerjakan'
        case 'accept':          return 'Tiket Diterima'
        case 'start':           return 'Tiket Dikerjakan'
        case 'pending':
            return log.to_status === 'pending_user'
                ? 'Dikembalikan Ke Pelapor'
                : 'Menunggu Pihak Eksternal'
        case 'resume':          return 'Tiket Dilanjutkan'
        case 'resolve':         return 'Tiket Diselesaikan'
        case 'close':           return 'Tiket Ditutup'
        case 'auto_close':      return 'Tiket Ditutup Otomatis'
        case 'reopen':          return 'Tiket Dibuka Kembali'
        case 'return':          return 'Tiket Dikembalikan'
        case 'request_approval': return 'Approval Diajukan'
        case 'approve':
        case 'approval_approved': return 'Approval Disetujui'
        case 'reject':
        case 'approval_rejected': return 'Approval Ditolak'
        default:                return log.action
    }
}

/**
 * Format tanggal saja: 2026-02-12
 */
const formatDateOnly = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

/**
 * Format jam saja: 16:39:16
 */
const formatTimeOnly = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    const seconds = String(date.getSeconds()).padStart(2, '0')
    return `${hours}:${minutes}:${seconds}`
}

const sortedLogs = computed(() => {
    return [...props.logs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})
</script>

<template>
    <div class="flow-root">
        <div class="space-y-6">
            <div
                v-for="log in sortedLogs"
                :key="log.id"
                class="flex items-center gap-5"
            >
                <!-- Icon: Filled Green Circle with White Check -->
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-sm">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                </div>

                <!-- Left: Deskripsi + Oleh -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">
                        {{ getDescription(log) }}
                    </p>
                    <p v-if="log.user" class="text-xs text-gray-500 mt-0.5">
                        Oleh: {{ log.user.name }}
                    </p>
                </div>

                <!-- Right: Tanggal + Jam -->
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-500">
                        {{ formatDateOnly(log.created_at) }}
                    </p>
                    <p class="text-xs font-semibold text-gray-700 mt-0.5">
                        {{ formatTimeOnly(log.created_at) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!logs || logs.length === 0" class="text-center py-8 text-gray-400">
            <Clock class="mx-auto h-10 w-10 text-gray-300 mb-3" />
            <p class="text-sm font-medium">Belum ada riwayat</p>
            <p class="text-xs mt-1">Aktivitas tiket akan tercatat di sini</p>
        </div>
    </div>
</template>
