<script setup>
import { useForm } from '@inertiajs/vue3'
import { CheckCircle, ArrowLeft, AlertCircle } from 'lucide-vue-next'
import { ref } from 'vue'

/**
 * TechnicianActionPanel Component
 * Panel aksi untuk Teknisi: Accept (mulai mengerjakan) atau Return (kembalikan ke Helpdesk)
 */
const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const showReturnModal = ref(false)
const returnForm = useForm({
    reason: ''
})

const acceptTicket = () => {
    if (confirm('Anda yakin ingin mulai mengerjakan tiket ini?')) {
        useForm({}).post(`/admin/tickets/${props.ticket.id}/accept`, {
            preserveScroll: true,
            onSuccess: () => emit('close')
        })
    }
}

const submitReturn = () => {
    returnForm.post(`/admin/tickets/${props.ticket.id}/return`, {
        preserveScroll: true,
        onSuccess: () => {
            returnForm.reset()
            showReturnModal.value = false
        }
    })
}
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-green-100 rounded-lg">
                    <CheckCircle class="w-4 h-4 text-green-600" />
                </div>
                <h3 class="font-medium text-gray-900">Panel Aksi</h3>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <!-- Status: Assigned - Show Accept Button -->
            <div v-if="ticket.status === 'assigned'" class="space-y-3">
                <p class="text-sm text-gray-600">
                    Tiket ini telah ditugaskan kepada Anda. Klik tombol di bawah untuk mulai mengerjakan.
                </p>
                <button
                    @click="acceptTicket"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                    <CheckCircle class="w-5 h-5" />
                    <span>Terima & Mulai Kerjakan</span>
                </button>
            </div>

            <!-- Status: In Progress - Show Return Option -->
            <div v-if="ticket.status === 'assigned' || ticket.status === 'in_progress'" class="pt-2">
                <button
                    @click="showReturnModal = true"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-orange-300 text-orange-600 rounded-lg hover:bg-orange-50 transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Kembalikan ke Helpdesk</span>
                </button>
                <p class="text-xs text-gray-500 text-center mt-2">
                    Gunakan jika tiket tidak sesuai dengan keahlian Anda
                </p>
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
                            <div class="px-6 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-orange-100 rounded-lg">
                                        <AlertCircle class="w-4 h-4 text-orange-600" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kembalikan Tiket</h3>
                                </div>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitReturn" class="p-6 space-y-4">
                                <p class="text-sm text-gray-600">
                                    Tiket akan dikembalikan ke Helpdesk untuk ditugaskan ulang. Mohon jelaskan alasan pengembalian.
                                </p>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pengembalian *</label>
                                    <textarea
                                        v-model="returnForm.reason"
                                        rows="3"
                                        placeholder="Contoh: Tiket ini memerlukan keahlian jaringan yang tidak saya kuasai..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                        required
                                    ></textarea>
                                    <p v-if="returnForm.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ returnForm.errors.reason }}
                                    </p>
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
                                        class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
    </div>
</template>
