<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// import icons
import {
    ArrowLeft,
    Ticket,
    Calendar,
    User,
    Tag,
    Clock,
    Paperclip,
    MessageSquare,
    Phone,
    Mail,
    Shield,
    FileText,
    CheckCircle
} from 'lucide-vue-next'

// import components
import StatusBadge from './Components/StatusBadge.vue'
import PriorityBadge from './Components/PriorityBadge.vue'
import Timeline from './Components/Timeline.vue'
import AttachmentList from './Components/AttachmentList.vue'
import TriagePanel from './Components/TriagePanel.vue'
import TechnicianActionPanel from './Components/TechnicianActionPanel.vue'
import WorkActionPanel from './Components/WorkActionPanel.vue'
import CommentSection from './Components/CommentSection.vue'
import ReporterActionPanel from './Components/ReporterActionPanel.vue'
import ConfirmResolvedSection from './Components/ConfirmResolvedSection.vue'
import ApprovalActionPanel from './Components/ApprovalActionPanel.vue'

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
    },
    priorities: {
        type: Array,
        default: () => []
    },
    technicians: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const auth = computed(() => page.props.auth)

// Is the current user the one who created the ticket (for action permissions)
const isCreator = computed(() => {
    return props.ticket.created_by_id === auth.value?.user?.id
})

// Is the current user the reporter (for display purposes, may differ from creator)
const isReporter = computed(() => {
    return props.ticket.reporter_id === auth.value?.user?.id
})

// Check if current user can perform triage actions
const canTriage = computed(() => {
    // permissions is at auth.permissions, not auth.user.permissions
    const permissions = auth.value?.permissions || {}
    return permissions['tickets.triage'] === true
})

// Check if current user is the assigned technician
const isAssignedTechnician = computed(() => {
    const ticketAssignedId = props.ticket.assigned_to_id || props.ticket.assigned_to?.id
    return ticketAssignedId === auth.value?.user?.id
})

// Check if user can accept tickets (teknisi)
const canAcceptTicket = computed(() => {
    const permissions = auth.value?.permissions || {}
    return permissions['tickets.accept'] === true
})

// Check if user can approve tickets (manager)
const canApprove = computed(() => {
    const permissions = auth.value?.permissions || {}
    return permissions['tickets.approve'] === true
})

// Check if action mode is requested via query param
const actionMode = computed(() => {
    const urlParams = new URLSearchParams(window.location.search)
    return urlParams.get('action')
})

// Check if triage panel should be shown
// Only show if ?action=triage is in URL, OR if user is directly assigned
const showTriagePanel = computed(() => {
    if (!canTriage.value) return false
    if (!['new', 'reopened', 'triage'].includes(props.ticket.status)) return false
    
    // Only show triage panel when explicitly requested via ?action=triage
    return actionMode.value === 'triage'
})

// Check if technician action panel should be shown (for assigned status)
// Only show when ?action=work is in URL
const showTechnicianPanel = computed(() => {
    if (!(isAssignedTechnician.value || canAcceptTicket.value)) return false
    if (props.ticket.status !== 'assigned') return false
    
    // Only show when explicitly requested via ?action=work
    return actionMode.value === 'work'
})

// Check if work action panel should be shown (for in_progress, pending_external, waiting_approval)
// Note: pending_user is excluded because staff must wait for reporter response
// Only show when ?action=work is in URL
const showWorkPanel = computed(() => {
    const workStatuses = ['in_progress', 'pending_external', 'waiting_approval']
    if (!isAssignedTechnician.value) return false
    if (!workStatuses.includes(props.ticket.status)) return false
    
    // Only show when explicitly requested via ?action=work
    return actionMode.value === 'work'
})

// Check if approval panel should be shown (for Manager)
const showApprovalPanel = computed(() => {
    if (!canApprove.value) return false
    if (props.ticket.status !== 'waiting_approval') return false
    return actionMode.value === 'approve'
})

// Show action panel for ticket creator when their response is needed
// Disabled here because creator actions should be done via Task Queue popup
const showReporterActionPanel = computed(() => {
    return false
})

// Show confirmation section for creator when ticket is resolved
// Disabled here because creator actions should be done via Task Queue popup
const showConfirmSection = computed(() => {
    return false
})

// Check if we should use simplified 2-column layout (action mode)
// This includes: Triage, Technician Accept, Work mode, Approval
const isActionMode = computed(() => {
    return showTriagePanel.value || showTechnicianPanel.value || showWorkPanel.value || showApprovalPanel.value
})

// Dynamic back URL based on context and action mode
const backUrl = computed(() => {
    // If coming from action mode, determine back URL based on action type
    if (actionMode.value === 'work') {
        // Staff working on ticket - go back to Task Queue
        return '/tickets/task-queue'
    }
    if (actionMode.value === 'triage') {
        // Helpdesk triaging - go back to Task Queue
        return '/tickets/task-queue'
    }
    if (actionMode.value === 'approve') {
        // Manager reviewing approval - go back to Task Queue
        return '/tickets/task-queue'
    }
    
    // For regular view mode:
    // Creator viewing their ticket - go to Tiket Saya
    if (isCreator.value) {
        return '/tickets/my-tickets'
    }
    // Staff viewing ticket (not action mode) - go to Task Queue
    if (canTriage.value || isAssignedTechnician.value) {
        return '/tickets/task-queue'
    }
    return '/tickets/my-tickets'
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
                :href="backUrl"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Tiket
            </Link>
        </div>

        <!-- ACTION MODE: Simple 2-column layout for Triage/Accept/Work -->
        <div v-if="isActionMode" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Column 1: Ticket Details Only -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Header: Judul Tiket -->
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ ticket.title }}
                        </h1>
                    </div>

                    <div class="p-6 space-y-8">
                        <!-- 1. Nomor Tiket (Kiri) + Info Detail (Kanan) -->
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Nomor Tiket Card -->
                            <div class="flex-shrink-0">
                                <div class="w-48 h-36 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex flex-col items-center justify-center text-white shadow-lg">
                                    <Ticket class="w-8 h-8 mb-2 opacity-80" />
                                    <span class="text-2xl font-bold font-mono tracking-wider">
                                        {{ ticket.ticket_number }}
                                    </span>
                                    <span class="text-xs mt-1 text-blue-100">Nomor Tiket</span>
                                </div>
                            </div>

                            <!-- Detail Info Grid -->
                            <div class="flex-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                                    <!-- Pelapor -->
                                    <div class="flex items-start gap-3">
                                        <User class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Pelapor</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.name || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Dibuat -->
                                    <div class="flex items-start gap-3">
                                        <Calendar class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Dibuat</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.created_at) }}</p>
                                        </div>
                                    </div>

                                    <!-- Nomor Telepon -->
                                    <div class="flex items-start gap-3">
                                        <Phone class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Nomor Telepon</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.phone || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="flex items-start gap-3">
                                        <Mail class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.email || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Kategori -->
                                    <div class="flex items-start gap-3">
                                        <Tag class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Kategori</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.category?.name || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Dampak / Urgensi -->
                                    <div class="flex items-start gap-3">
                                        <Shield class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Dampak / Urgensi</p>
                                            <PriorityBadge
                                                v-if="ticket.final_priority || ticket.user_priority"
                                                :priority="ticket.final_priority || ticket.user_priority"
                                            />
                                            <span v-else class="text-sm text-gray-400">-</span>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="flex items-start gap-3">
                                        <Clock class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Status</p>
                                            <StatusBadge
                                                :status="ticket.status"
                                                :label="statuses[ticket.status] || ticket.status"
                                            />
                                        </div>
                                    </div>

                                    <!-- Petugas -->
                                    <div class="flex items-start gap-3">
                                        <User class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Petugas</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.assigned_to?.name || 'Belum ditugaskan' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <hr class="border-gray-100" />

                        <!-- 2. Deskripsi Tiket -->
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-4 flex items-center gap-2">
                                <FileText class="w-4 h-4 text-gray-500" />
                                Deskripsi Tiket
                            </h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                {{ ticket.description }}
                            </p>
                        </div>

                        <!-- Pending Reason -->
                        <div v-if="ticket.pending_reason">
                            <hr class="border-gray-100 mb-6" />
                            <h3 class="text-sm font-bold uppercase tracking-wider text-orange-700 mb-4 flex items-center gap-2">
                                <Clock class="w-4 h-4" />
                                Sedang Pending
                            </h3>
                            <p class="text-sm text-orange-600">{{ ticket.pending_reason }}</p>
                        </div>

                        <!-- Bukti Dukung Penyelesaian Tiket -->
                        <div>
                            <hr class="border-gray-100 mb-6" />
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-4 flex items-center gap-2">
                                <Paperclip class="w-4 h-4 text-gray-500" />
                                Bukti Dukung Penyelesaian Tiket
                            </h3>
                            <AttachmentList :attachments="ticket.attachments || []" />
                        </div>
                    </div>
                </div>
                
                <!-- Comment Section (read-only for work mode) -->
                <CommentSection
                    v-if="showWorkPanel || showTechnicianPanel"
                    :ticket="ticket"
                    :comments="ticket.comments || []"
                    :read-only="true"
                    class="mt-6"
                />
            </div>

            <!-- Column 2: Action Panel -->
            <div class="lg:col-span-5">
                <div class="sticky top-6">
                    <!-- Triage Panel -->
                    <TriagePanel
                        v-if="showTriagePanel"
                        :ticket="ticket"
                        :priorities="priorities"
                        :technicians="technicians"
                    />
                    <!-- Technician Accept Panel -->
                    <TechnicianActionPanel
                        v-if="showTechnicianPanel"
                        :ticket="ticket"
                    />
                    <!-- Work Action Panel -->
                    <WorkActionPanel
                        v-if="showWorkPanel"
                        :ticket="ticket"
                    />
                    <!-- Approval Action Panel -->
                    <ApprovalActionPanel
                        v-if="showApprovalPanel"
                        :ticket="ticket"
                    />
                    <!-- Reporter Response: Comment Section for replying -->
                    <CommentSection
                        v-if="showReporterActionPanel"
                        :ticket="ticket"
                        :comments="ticket.comments || []"
                    />
                </div>
            </div>
        </div>

        <!-- VIEW MODE: Full layout with all sections (for viewing, confirm) -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Main Content (Column 1) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Confirm Resolved Section -->
                <ConfirmResolvedSection
                    v-if="showConfirmSection"
                    :ticket="ticket"
                />

                <!-- Card 1: Ticket Detail -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Header: Judul Tiket -->
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ ticket.title }}
                        </h1>
                    </div>

                    <div class="p-6 space-y-8">
                        <!-- 1. Nomor Tiket (Kiri) + Info Detail (Kanan) -->
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Nomor Tiket Card -->
                            <div class="flex-shrink-0">
                                <div class="w-48 h-36 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex flex-col items-center justify-center text-white shadow-lg">
                                    <Ticket class="w-8 h-8 mb-2 opacity-80" />
                                    <span class="text-2xl font-bold font-mono tracking-wider">
                                        {{ ticket.ticket_number }}
                                    </span>
                                    <span class="text-xs mt-1 text-blue-100">Nomor Tiket</span>
                                </div>
                            </div>

                            <!-- Detail Info Grid -->
                            <div class="flex-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                                    <!-- Pelapor -->
                                    <div class="flex items-start gap-3">
                                        <User class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Pelapor</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.name || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Dibuat -->
                                    <div class="flex items-start gap-3">
                                        <Calendar class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Dibuat</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.created_at) }}</p>
                                        </div>
                                    </div>

                                    <!-- Nomor Telepon -->
                                    <div class="flex items-start gap-3">
                                        <Phone class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Nomor Telepon</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.phone || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="flex items-start gap-3">
                                        <Mail class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.reporter?.email || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Kategori -->
                                    <div class="flex items-start gap-3">
                                        <Tag class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Kategori</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.category?.name || '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Dampak / Urgensi -->
                                    <div class="flex items-start gap-3">
                                        <Shield class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Dampak / Urgensi</p>
                                            <PriorityBadge
                                                v-if="ticket.final_priority || ticket.user_priority"
                                                :priority="ticket.final_priority || ticket.user_priority"
                                            />
                                            <span v-else class="text-sm text-gray-400">-</span>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="flex items-start gap-3">
                                        <Clock class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Status</p>
                                            <StatusBadge
                                                :status="ticket.status"
                                                :label="statuses[ticket.status] || ticket.status"
                                            />
                                        </div>
                                    </div>

                                    <!-- Petugas -->
                                    <div class="flex items-start gap-3">
                                        <User class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-500">Petugas</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ticket.assigned_to?.name || 'Belum ditugaskan' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <hr class="border-gray-100" />

                        <!-- 2. Alasan / Deskripsi -->
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-4 flex items-center gap-2">
                                <FileText class="w-4 h-4 text-gray-500" />
                                Deskripsi Tiket
                            </h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                {{ ticket.description }}
                            </p>
                        </div>

                        <!-- Pending Reason -->
                        <div v-if="ticket.pending_reason">
                            <hr class="border-gray-100 mb-6" />
                            <h3 class="text-sm font-bold uppercase tracking-wider text-orange-700 mb-4 flex items-center gap-2">
                                <!-- <Clock class="w-4 h-4" /> -->
                                Alasan Dikembalikan
                            </h3>
                            <p class="text-sm text-orange-600">{{ ticket.pending_reason }}</p>
                        </div>

                        <!-- 3. Penyelesaian -->
                        <div v-if="ticket.resolution">
                            <hr class="border-gray-100 mb-6" />
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-4 flex items-center gap-2">
                                <CheckCircle class="w-4 h-4 text-gray-500" />
                                Penyelesaian
                            </h3>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                {{ ticket.resolution }}
                            </p>
                            <p v-if="ticket.resolved_at" class="text-xs text-gray-400 mt-3">
                                Diselesaikan: {{ formatDate(ticket.resolved_at) }}
                            </p>
                        </div>

                        <!-- 4. Bukti Dukung Penyelesaian Tiket -->
                        <div>
                            <hr class="border-gray-100 mb-6" />
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-4 flex items-center gap-2">
                                <Paperclip class="w-4 h-4 text-gray-500" />
                                Bukti Dukung Penyelesaian Tiket
                            </h3>
                            <AttachmentList :attachments="ticket.attachments || []" />
                        </div>
                    </div>
                </div>

                <!-- Card 2: Comments Section -->
                <CommentSection
                    :ticket="ticket"
                    :comments="ticket.comments || []"
                />
            </div>

            <!-- Sidebar (Column 2) -->
            <div class="lg:col-span-4">
                <!-- Card 1: Riwayat Tiket -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            Riwayat Tiket
                        </h3>
                    </div>
                    <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                        <Timeline :logs="ticket.logs || []" />
                    </div>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>

