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
    MessageSquare
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
        return '/admin/tickets/task-queue'
    }
    if (actionMode.value === 'triage') {
        // Helpdesk triaging - go back to Task Queue
        return '/admin/tickets/task-queue'
    }
    if (actionMode.value === 'approve') {
        // Manager reviewing approval - go back to Task Queue
        return '/admin/tickets/task-queue'
    }
    
    // For regular view mode:
    // Creator viewing their ticket - go to Tiket Saya
    if (isCreator.value) {
        return '/admin/tickets/my-tickets'
    }
    // Staff viewing ticket (not action mode) - go to Task Queue
    if (canTriage.value || isAssignedTechnician.value) {
        return '/admin/tickets/task-queue'
    }
    return '/admin/tickets/my-tickets'
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
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50 flex items-start justify-between">
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

                    <div class="p-6 space-y-8">
                        <!-- Meta Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                                <p class="text-xs text-gray-500 mb-1">Ditugaskan</p>
                                <p class="text-sm text-gray-900 flex items-center gap-1">
                                    <User class="w-4 h-4 text-gray-400" />
                                    {{ ticket.assigned_to?.name || '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Dibuat</p>
                                <p class="text-sm text-gray-900 flex items-center gap-1">
                                    <Calendar class="w-4 h-4 text-gray-400" />
                                    {{ formatDate(ticket.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-2">Deskripsi Tiket</h3>
                            <div class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap leading-relaxed border border-gray-100">
                                {{ ticket.description }}
                            </div>
                        </div>

                        <!-- Pending Reason (if applicable) -->
                        <div v-if="ticket.pending_reason" class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                            <h3 class="text-sm font-medium text-orange-800 mb-2 flex items-center gap-2">
                                <Clock class="w-4 h-4" />
                                Alasan Pending
                            </h3>
                            <p class="text-sm text-orange-700">{{ ticket.pending_reason }}</p>
                        </div>

                        <!-- Pelapor Info -->
                        <div class="pt-6 border-t border-gray-100">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-4 tracking-widest">Informasi Pelapor</h3>
                            <div class="flex items-center gap-4 bg-gray-50/50 p-4 rounded-xl border border-dashed border-gray-200">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                    <span class="text-blue-600 font-bold text-lg">
                                        {{ ticket.reporter?.name?.charAt(0) || '?' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ ticket.reporter?.name }}</p>
                                    <p class="text-sm text-gray-500 flex items-center gap-1">
                                        <Tag class="w-3 h-3" />
                                        {{ ticket.reporter?.unit?.name || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Lampiran -->
                        <div v-if="ticket.attachments?.length > 0" class="pt-6 border-t border-gray-100">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-4 flex items-center justify-between tracking-widest">
                                Lampiran File
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                    {{ ticket.attachments?.length || 0 }} FILES
                                </span>
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

                <!-- Card 1: Ticket Details + Pelapor + Attachment -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50 flex items-start justify-between">
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

                    <div class="p-6 space-y-8">
                        <!-- 1. Meta Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

                        <!-- 2. Deskripsi & Solusi -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-2">Deskripsi Tiket</h3>
                                <div class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap leading-relaxed border border-gray-100">
                                    {{ ticket.description }}
                                </div>
                            </div>

                            <div v-if="ticket.pending_reason" class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                                <h3 class="text-sm font-medium text-orange-800 mb-2 flex items-center gap-2">
                                    <Clock class="w-4 h-4" />
                                    Sedang Pending
                                </h3>
                                <p class="text-sm text-orange-700">{{ ticket.pending_reason }}</p>
                            </div>

                            <div v-if="ticket.resolution" class="p-4 bg-green-50 rounded-lg border border-green-100">
                                <h3 class="text-sm font-medium text-green-800 mb-2 flex items-center gap-2">
                                    ✓ Solusi/Penyelesaian
                                </h3>
                                <div class="text-sm text-green-700 whitespace-pre-wrap">
                                    {{ ticket.resolution }}
                                </div>
                                <p v-if="ticket.resolved_at" class="text-xs text-green-600 mt-2">
                                    Diselesaikan: {{ formatDate(ticket.resolved_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- 3. Pelapor Info (Merged into Detail Card) -->
                        <div class="pt-6 border-t border-gray-100">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-4 tracking-widest">Informasi Pelapor</h3>
                            <div class="flex items-center gap-4 bg-gray-50/50 p-4 rounded-xl border border-dashed border-gray-200">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                    <span class="text-blue-600 font-bold text-lg">
                                        {{ ticket.reporter?.name?.charAt(0) || '?' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ ticket.reporter?.name }}</p>
                                    <p class="text-sm text-gray-500 flex items-center gap-1">
                                        <Tag class="w-3 h-3" />
                                        {{ ticket.reporter?.unit?.name || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Lampiran (Merged into Detail Card) -->
                        <div class="pt-6 border-t border-gray-100">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-4 flex items-center justify-between tracking-widest">
                                Lampiran File
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                    {{ ticket.attachments?.length || 0 }} FILES
                                </span>
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
                            <Clock class="w-4 h-4 text-blue-600" />
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

