<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link } from '@inertiajs/vue3'

// import icons
import {
    ArrowLeft,
    Ticket,
    Calendar,
    User,
    Tag,
    Clock,
    Paperclip,
    MessageSquare
} from 'lucide-vue-next'

// import components
import StatusBadge from './Components/StatusBadge.vue'
import PriorityBadge from './Components/PriorityBadge.vue'
import Timeline from './Components/Timeline.vue'
import AttachmentList from './Components/AttachmentList.vue'

// props
const props = defineProps({
    ticket: {
        type: Object,
        required: true
    },
    statuses: {
        type: Object,
        default: () => ({})
    },
    statusColors: {
        type: Object,
        default: () => ({})
    }
})

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head :title="`${ticket.ticket_number} - ${ticket.title}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/admin/tickets/my-tickets"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Tiket
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ticket Header Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                    <Ticket class="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <span class="text-sm font-mono text-blue-600">
                                        {{ ticket.ticket_number }}
                                    </span>
                                    <h1 class="text-xl font-semibold text-gray-900 mt-1">
                                        {{ ticket.title }}
                                    </h1>
                                </div>
                            </div>
                            <StatusBadge
                                :status="ticket.status"
                                :label="statuses[ticket.status] || ticket.status"
                            />
                        </div>
                    </div>

                    <!-- Ticket Body -->
                    <div class="p-6">
                        <!-- Meta Info -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Kategori</p>
                                <p class="text-sm font-medium text-gray-900 flex items-center gap-1">
                                    <Tag class="w-4 h-4 text-gray-400" />
                                    {{ ticket.category?.name || '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Prioritas</p>
                                <PriorityBadge
                                    v-if="ticket.final_priority || ticket.user_priority"
                                    :priority="ticket.final_priority || ticket.user_priority"
                                />
                                <span v-else class="text-sm text-gray-400">-</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Dibuat</p>
                                <p class="text-sm text-gray-900 flex items-center gap-1">
                                    <Calendar class="w-4 h-4 text-gray-400" />
                                    {{ formatDate(ticket.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Ditugaskan Ke</p>
                                <p class="text-sm text-gray-900 flex items-center gap-1">
                                    <User class="w-4 h-4 text-gray-400" />
                                    {{ ticket.assigned_to?.name || 'Belum ditugaskan' }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi</h3>
                            <div class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap">
                                {{ ticket.description }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900 flex items-center gap-2">
                            <Paperclip class="w-4 h-4 text-gray-500" />
                            Lampiran
                            <span class="text-sm text-gray-400">({{ ticket.attachments?.length || 0 }})</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <AttachmentList :attachments="ticket.attachments || []" />
                    </div>
                </div>

                <!-- Comments Card (Placeholder for Phase 3) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900 flex items-center gap-2">
                            <MessageSquare class="w-4 h-4 text-gray-500" />
                            Komentar
                            <span class="text-sm text-gray-400">({{ ticket.comments?.length || 0 }})</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <div v-if="!ticket.comments || ticket.comments.length === 0" class="text-center py-8 text-gray-500">
                            <MessageSquare class="mx-auto h-8 w-8 text-gray-400 mb-2" />
                            <p class="text-sm">Belum ada komentar</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="comment in ticket.comments"
                                :key="comment.id"
                                class="p-4 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-sm text-gray-900">
                                        {{ comment.user?.name }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ formatDate(comment.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700">{{ comment.content }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Reporter Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Pelapor</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium">
                                    {{ ticket.reporter?.name?.charAt(0) || '?' }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ ticket.reporter?.name }}</p>
                                <p class="text-sm text-gray-500">{{ ticket.reporter?.unit?.name || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900 flex items-center gap-2">
                            <Clock class="w-4 h-4 text-gray-500" />
                            Riwayat Aktivitas
                        </h3>
                    </div>
                    <div class="p-6">
                        <Timeline :logs="ticket.logs || []" />
                    </div>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
