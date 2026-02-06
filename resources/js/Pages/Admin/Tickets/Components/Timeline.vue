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
    AlertCircle
} from 'lucide-vue-next'

/**
 * Timeline Component
 * Menampilkan timeline perubahan status tiket dari logs
 */
const props = defineProps({
    logs: {
        type: Array,
        required: true,
        default: () => []
    }
})

const actionIcons = {
    create: PlusCircle,
    triage: CheckCircle,
    assign: ArrowRight,
    start: Play,
    pending: Pause,
    resume: Play,
    resolve: CheckCircle,
    close: XCircle,
    reopen: RotateCcw,
    auto_close: Clock,
    return: RotateCcw,
    request_approval: Send,
    approve: CheckCircle,
    reject: XCircle,
}

const actionColors = {
    create: 'text-blue-500 bg-blue-100',
    triage: 'text-purple-500 bg-purple-100',
    assign: 'text-indigo-500 bg-indigo-100',
    start: 'text-yellow-500 bg-yellow-100',
    pending: 'text-orange-500 bg-orange-100',
    resume: 'text-yellow-500 bg-yellow-100',
    resolve: 'text-green-500 bg-green-100',
    close: 'text-gray-500 bg-gray-100',
    reopen: 'text-red-500 bg-red-100',
    auto_close: 'text-gray-500 bg-gray-100',
    return: 'text-orange-500 bg-orange-100',
    request_approval: 'text-pink-500 bg-pink-100',
    approve: 'text-green-500 bg-green-100',
    reject: 'text-red-500 bg-red-100',
}

const actionLabels = {
    create: 'Tiket Dibuat',
    triage: 'Tiket Diverifikasi',
    assign: 'Ditugaskan',
    start: 'Mulai Dikerjakan',
    pending: 'Set Pending',
    resume: 'Dilanjutkan',
    resolve: 'Diselesaikan',
    close: 'Ditutup',
    reopen: 'Dibuka Kembali',
    auto_close: 'Ditutup Otomatis',
    return: 'Dikembalikan',
    request_approval: 'Minta Persetujuan',
    approve: 'Disetujui',
    reject: 'Ditolak',
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const sortedLogs = computed(() => {
    return [...props.logs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})
</script>

<template>
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            <li v-for="(log, index) in sortedLogs" :key="log.id">
                <div class="relative pb-8">
                    <!-- Connector line -->
                    <span
                        v-if="index !== sortedLogs.length - 1"
                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                        aria-hidden="true"
                    />

                    <div class="relative flex space-x-3">
                        <!-- Icon -->
                        <div>
                            <span
                                :class="[
                                    actionColors[log.action] || 'text-gray-500 bg-gray-100',
                                    'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white'
                                ]"
                            >
                                <component
                                    :is="actionIcons[log.action] || AlertCircle"
                                    class="h-4 w-4"
                                />
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">{{ actionLabels[log.action] || log.action }}</span>
                                    <span v-if="log.user" class="text-gray-500">
                                        oleh {{ log.user.name }}
                                    </span>
                                </p>
                                <p v-if="log.notes" class="mt-1 text-sm text-gray-500">
                                    {{ log.notes }}
                                </p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(log.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Empty state -->
        <div v-if="!logs || logs.length === 0" class="text-center py-6 text-gray-500">
            <Clock class="mx-auto h-8 w-8 text-gray-400 mb-2" />
            <p class="text-sm">Belum ada aktivitas</p>
        </div>
    </div>
</template>
