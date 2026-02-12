<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { computed, ref, reactive } from 'vue'

// import icons
import { 
    ClipboardList, 
    ClipboardCheck, 
    Wrench, 
    MessageCircle, 
    CheckCircle,
    AlertCircle,
    Eye,
    X,
    MessageSquare
} from 'lucide-vue-next'

// import permissions helper
import { hasPermission } from '@/Utils/Permissions'

// import components
import TicketFilters from './Components/TicketFilters.vue'
import StatusBadge from './Components/StatusBadge.vue'
import PriorityBadge from './Components/PriorityBadge.vue'
import Pagination from '@/Shared/Pagination.vue'

// props
const props = defineProps({
    tickets: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    statuses: {
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
const currentUserId = computed(() => auth.value?.user?.id)

// Modal State
const showConfirmModal = ref(false)
const selectedTicket = ref(null)
const confirmForm = useForm({
    reason: ''
})

// Helper functions to determine action type for each ticket
const getActionType = (ticket) => {
    const status = ticket.status
    const isCreator = ticket.created_by_id === currentUserId.value
    const isAssignee = ticket.assigned_to_id === currentUserId.value

    // Helpdesk: Triage actions
    if (['new', 'reopened'].includes(status) && hasPermission('tickets.triage')) {
        return { type: 'triage', label: 'Verifikasi', color: 'purple', icon: ClipboardCheck }
    }

    // Teknisi: Work actions
    if (status === 'assigned' && isAssignee) {
        return { type: 'accept', label: 'Terima', color: 'blue', icon: Wrench }
    }
    if (['in_progress', 'pending_external'].includes(status) && isAssignee) {
        return { type: 'work', label: 'Kerjakan', color: 'green', icon: Wrench }
    }

    // Manager: Approval actions
    if (status === 'waiting_approval' && hasPermission('tickets.approve')) {
        return { type: 'approve', label: 'Review', color: 'pink', icon: AlertCircle }
    }

    // Creator: Response/Confirmation actions
    if (status === 'pending_user' && isCreator) {
        return { type: 'respond', label: 'Tanggapi', color: 'orange', icon: MessageCircle }
    }
    if (status === 'resolved' && isCreator) {
        return { type: 'confirm', label: 'Konfirmasi', color: 'green', icon: CheckCircle }
    }

    return { type: 'view', label: 'Lihat', color: 'gray', icon: Eye }
}

const handleAction = (ticket) => {
    const action = getActionType(ticket)
    if (action.type === 'confirm') {
        selectedTicket.value = ticket
        showConfirmModal.value = true
        return
    }
    
    // Default: Redirect to detail page using Inertia router
    router.visit(getActionUrl(ticket, action.type))
}

const getActionUrl = (ticket, actionType) => {
    const baseUrl = `/admin/tickets/${ticket.id}`
    
    switch (actionType) {
        case 'triage':
            return `${baseUrl}?action=triage`
        case 'accept':
        case 'work':
            return `${baseUrl}?action=work`
        case 'approve':
            return `${baseUrl}?action=approve`
        case 'confirm':
            return '#' // Handled by handleAction
        default:
            return baseUrl
    }
}

const submitClose = () => {
    confirmForm.post(`/admin/tickets/${selectedTicket.value.id}/close`, {
        onSuccess: () => {
            showConfirmModal.value = false
            selectedTicket.value = null
        }
    })
}

const submitReopen = () => {
    if (!confirmForm.reason || confirmForm.reason.length < 10) {
        Swal.fire({
            title: 'Alasan Kurang Jelas',
            text: 'Mohon masukkan alasan mengapa kendala belum teratasi (minimal 10 karakter).',
            icon: 'warning',
            confirmButtonColor: '#ef4444', // red-500
        })
        return
    }
    
    confirmForm.post(`/admin/tickets/${selectedTicket.value.id}/reopen`, {
        onSuccess: () => {
            showConfirmModal.value = false
            selectedTicket.value = null
            confirmForm.reset()
            
            Swal.fire({
                title: 'Tiket Dibuka Kembali',
                text: 'Tiket telah berhasil dibuka kembali untuk ditindaklanjuti.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })
        }
    })
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

// Action button color classes
const getButtonClasses = (color) => {
    const colors = {
        purple: 'bg-purple-600 hover:bg-purple-700 text-white',
        blue: 'bg-blue-600 hover:bg-blue-700 text-white',
        green: 'bg-green-600 hover:bg-green-700 text-white',
        orange: 'bg-orange-500 hover:bg-orange-600 text-white',
        pink: 'bg-pink-600 hover:bg-pink-700 text-white',
        gray: 'bg-gray-100 hover:bg-gray-200 text-gray-700'
    }
    return colors[color] || colors.gray
}

// Row highlight classes
const getRowClasses = (ticket) => {
    const action = getActionType(ticket)
    const highlights = {
        triage: 'bg-purple-50',
        accept: 'bg-blue-50',
        work: 'bg-green-50/50',
        approve: 'bg-pink-50',
        respond: 'bg-orange-50',
        confirm: 'bg-green-50'
    }
    return highlights[action.type] || ''
}
</script>

<template>
    <Head title="Daftar Tugas" />

    <LayoutAdmin>
        <!-- Page Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <ClipboardList class="w-7 h-7 text-indigo-600" />
                    Daftar Tugas
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Tiket yang memerlukan tindakan dari Anda
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">
                        <ClipboardCheck class="w-3 h-3" /> Verifikasi
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                        <Wrench class="w-3 h-3" /> Pengerjaan
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-medium">
                        <MessageCircle class="w-3 h-3" /> Tanggapan
                    </span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <TicketFilters
            :filters="filters"
            :statuses="statuses"
            route-name="admin.tickets.task-queue"
        />

        <!-- Task Queue Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No. Tiket
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Judul
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pelapor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prioritas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            :class="['hover:bg-gray-50 transition-colors', getRowClasses(ticket)]"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium"
                                >
                                    {{ ticket.ticket_number }}
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                    {{ ticket.title }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ ticket.category?.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">
                                    {{ ticket.reporter?.name || ticket.created_by?.name || '-' }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ ticket.reporter?.unit?.name || '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <PriorityBadge
                                    v-if="ticket.final_priority || ticket.user_priority"
                                    :priority="ticket.final_priority || ticket.user_priority"
                                />
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <StatusBadge
                                    :status="ticket.status"
                                    :label="statuses[ticket.status] || ticket.status"
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(ticket.updated_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button
                                    @click="handleAction(ticket)"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors shadow-sm',
                                        getButtonClasses(getActionType(ticket).color)
                                    ]"
                                >
                                    <component :is="getActionType(ticket).icon" class="w-4 h-4" />
                                    {{ getActionType(ticket).label }}
                                </button>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="!tickets.data || tickets.data.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                        <CheckCircle class="w-8 h-8 text-green-600" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                        Tidak ada tugas pending
                                    </h3>
                                    <p class="text-gray-500 text-sm">
                                        Semua tugas sudah dikerjakan. Kerja bagus! 🎉
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="tickets.data && tickets.data.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Menampilkan
                        <span class="font-semibold">{{ tickets.from }}</span>
                        -
                        <span class="font-semibold">{{ tickets.to }}</span>
                        dari
                        <span class="font-semibold">{{ tickets.total }}</span>
                        data
                    </p>
                    <Pagination :links="tickets.links" />
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircle class="w-6 h-6 text-green-600" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Konfirmasi Penyelesaian</h3>
                        </div>
                        <button @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-600 mb-4">
                            Apakah kendala pada tiket <span class="font-mono font-bold text-blue-600">{{ selectedTicket?.ticket_number }}</span> sudah teratasi dengan baik?
                        </p>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Solusi dari Teknisi:</h4>
                            <p class="text-gray-700 italic">"{{ selectedTicket?.resolution || 'Tidak ada catatan solusi' }}"</p>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Catatan Tambahan (Opsional, wajib diisi jika membuka kembali)
                            </label>
                            <textarea
                                v-model="confirmForm.reason"
                                rows="3"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all"
                                placeholder="Tuliskan alasan jika masalah belum selesai..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            @click="submitClose"
                            :disabled="confirmForm.processing"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-lg shadow-green-200 disabled:opacity-50"
                        >
                            <CheckCircle class="w-5 h-5" />
                            Ya, Masalah Selesai
                        </button>
                        <button
                            @click="submitReopen"
                            :disabled="confirmForm.processing"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-red-600 border-2 border-red-100 font-bold rounded-xl hover:bg-red-50 hover:border-red-200 transition-all disabled:opacity-50"
                        >
                            <X class="w-5 h-5" />
                            Belum, Buka Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
