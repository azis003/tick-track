<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head dan Link dari Inertia
import { Head, Link, router, useForm } from "@inertiajs/vue3"
import { ref } from 'vue'

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// import icons
import { 
    ClipboardList, 
    Play, 
    Clock, 
    CheckCircle2,
    AlertTriangle,
    ArrowRight,
    RotateCcw,
    Wrench,
    X
} from 'lucide-vue-next';

// Props dari controller
defineProps({
    stats: Object,
    assignedTickets: Array,
})

// Modal state
const showReturnModal = ref(false)
const selectedTicket = ref(null)
const returnForm = useForm({
    reason: ''
})

// Helper untuk priority badge
const getPriorityClass = (priority) => {
    const classes = {
        'Critical': 'bg-red-100 text-red-700 border-red-200',
        'High': 'bg-orange-100 text-orange-700 border-orange-200',
        'Medium': 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'Low': 'bg-green-100 text-green-700 border-green-200',
    };
    return classes[priority] || 'bg-gray-100 text-gray-700 border-gray-200';
};

// Helper untuk status badge
const getStatusClass = (status) => {
    const classes = {
        'assigned': 'bg-indigo-100 text-indigo-700',
        'in_progress': 'bg-blue-100 text-blue-700',
        'pending_user': 'bg-orange-100 text-orange-700',
        'pending_external': 'bg-orange-100 text-orange-700',
        'waiting_approval': 'bg-pink-100 text-pink-700',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

const getStatusLabel = (status) => {
    const labels = {
        'assigned': 'Ditugaskan',
        'in_progress': 'Dalam Proses',
        'pending_user': 'Pending User',
        'pending_external': 'Pending Vendor',
        'waiting_approval': 'Menunggu Approval',
    };
    return labels[status] || status;
};

// Accept ticket
const acceptTicket = (ticketId, event) => {
    event.preventDefault()
    event.stopPropagation()
    if (confirm('Mulai kerjakan tiket ini?')) {
        router.post(`/admin/tickets/${ticketId}/accept`, {}, {
            preserveScroll: true
        })
    }
}

// Open return modal
const openReturnModal = (ticket, event) => {
    event.preventDefault()
    event.stopPropagation()
    selectedTicket.value = ticket
    returnForm.reset()
    showReturnModal.value = true
}

// Submit return
const submitReturn = () => {
    if (!selectedTicket.value) return
    
    returnForm.post(`/admin/tickets/${selectedTicket.value.id}/return`, {
        preserveScroll: true,
        onSuccess: () => {
            showReturnModal.value = false
            selectedTicket.value = null
            returnForm.reset()
        }
    })
}
</script>

<template>
    <Head :title="`Dashboard Teknisi - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Teknisi</h1>
            <p class="mt-2 text-gray-600">Kelola tiket yang ditugaskan kepada Anda.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Ditugaskan -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-indigo-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-md">
                        <ClipboardList class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Ditugaskan</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.assigned || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Dalam Proses -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-md">
                        <Play class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Dalam Proses</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.inProgress || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-orange-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-md">
                        <Clock class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.pending || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Selesai -->
            <div class="relative overflow-hidden p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-green-100 rounded-full opacity-50 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative flex items-center">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-md">
                        <CheckCircle2 class="w-6 h-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Selesai</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.resolved || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Tickets -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <ClipboardList class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Tiket Ditugaskan</h2>
                        <p class="text-sm text-gray-500">Tiket yang perlu Anda kerjakan</p>
                    </div>
                </div>
                <Link 
                    href="/admin/tickets/assigned" 
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    Lihat Semua →
                </Link>
            </div>
            <div class="p-6">
                <div v-if="assignedTickets && assignedTickets.length > 0" class="space-y-4">
                    <div 
                        v-for="ticket in assignedTickets" 
                        :key="ticket.id"
                        class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all group"
                    >
                        <Link 
                            :href="`/admin/tickets/${ticket.id}`"
                            class="flex-1"
                        >
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                <span 
                                    class="px-2 py-0.5 text-xs font-medium rounded-full border"
                                    :class="getPriorityClass(ticket.priority)"
                                >
                                    {{ ticket.priority || 'Normal' }}
                                </span>
                                <span 
                                    class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    :class="getStatusClass(ticket.status)"
                                >
                                    {{ getStatusLabel(ticket.status) }}
                                </span>
                            </div>
                            <p class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                {{ ticket.title }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Dari: {{ ticket.user }} • {{ ticket.created_at }}
                            </p>
                        </Link>
                        
                        <!-- Actions based on status -->
                        <div class="flex items-center gap-2 ml-4">
                            <!-- Status: assigned - Show Mulai + Kembalikan -->
                            <template v-if="ticket.status === 'assigned'">
                                <button 
                                    @click="acceptTicket(ticket.id, $event)"
                                    class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm hover:shadow-md flex items-center gap-2"
                                >
                                    <Play class="w-4 h-4" />
                                    Mulai
                                </button>
                                <button 
                                    @click="openReturnModal(ticket, $event)"
                                    class="px-3 py-2 text-sm text-orange-600 hover:text-orange-800 hover:bg-orange-50 rounded-lg transition-colors flex items-center gap-1"
                                >
                                    <RotateCcw class="w-4 h-4" />
                                </button>
                            </template>
                            
                            <!-- Status: in_progress/pending - Show Kerjakan -->
                            <template v-else>
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-sm hover:shadow-md flex items-center gap-2"
                                >
                                    <Wrench class="w-4 h-4" />
                                    Kerjakan
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <CheckCircle2 class="w-8 h-8 text-green-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada tiket menunggu</h3>
                    <p class="mt-2 text-gray-500">Semua tiket sudah dikerjakan. Kerja bagus! 🎉</p>
                </div>
            </div>
        </div>

        <!-- Return Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showReturnModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showReturnModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-orange-100 rounded-lg">
                                        <RotateCcw class="w-4 h-4 text-orange-600" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kembalikan Tiket</h3>
                                </div>
                                <button @click="showReturnModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitReturn" class="p-6 space-y-4">
                                <div class="p-4 bg-orange-50 rounded-lg border border-orange-100">
                                    <p class="text-sm text-orange-800">
                                        ⚠️ Tiket akan dikembalikan ke Helpdesk untuk di-reassign.
                                    </p>
                                </div>

                                <div v-if="selectedTicket">
                                    <p class="text-sm text-gray-600 mb-1">Tiket:</p>
                                    <p class="font-medium text-gray-900">
                                        {{ selectedTicket.ticket_number }} - {{ selectedTicket.title }}
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                                    <textarea
                                        v-model="returnForm.reason"
                                        rows="3"
                                        placeholder="Jelaskan alasan tidak bisa mengerjakan tiket ini..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                        required
                                    ></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showReturnModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="returnForm.processing || !returnForm.reason"
                                        class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
                                    >
                                        {{ returnForm.processing ? 'Memproses...' : 'Kembalikan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </LayoutAdmin>
</template>

