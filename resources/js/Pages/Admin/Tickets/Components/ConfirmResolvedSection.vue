<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { CheckCircle2, XCircle, AlertCircle, Send } from 'lucide-vue-next'

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const showReopenForm = ref(false)
const reopenForm = useForm({
    reason: ''
})

const confirmSolution = () => {
    if (confirm('Apakah Anda yakin solusi ini sudah meresolve kendala Anda? Tiket akan ditutup.')) {
        router.post(`/admin/tickets/${props.ticket.id}/close`, {}, {
            preserveScroll: true
        })
    }
}

const submitReopen = () => {
    reopenForm.post(`/admin/tickets/${props.ticket.id}/reopen`, {
        preserveScroll: true,
        onSuccess: () => {
            showReopenForm.value = false
            reopenForm.reset()
        }
    })
}
</script>

<template>
    <div class="bg-white border-2 border-green-200 rounded-xl overflow-hidden shadow-md">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-50 border-b border-green-100 flex items-center gap-3">
            <div class="p-2 bg-green-100 rounded-lg">
                <CheckCircle2 class="w-5 h-5 text-green-600" />
            </div>
            <div>
                <h3 class="font-bold text-green-900">Konfirmasi Penyelesaian</h3>
                <p class="text-xs text-green-700">Teknisi telah menandai tiket ini sebagai selesai.</p>
            </div>
        </div>

        <div class="p-6">
            <!-- Solution Detail Recap -->
            <div class="mb-6 p-4 bg-gray-50 border border-gray-100 rounded-lg text-sm">
                <p class="text-xs font-black uppercase text-gray-400 mb-2 tracking-wider">Solusi dari Teknisi:</p>
                <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.resolution }}</p>
            </div>

            <div v-if="!showReopenForm" class="space-y-3">
                <p class="text-sm text-gray-600 mb-4">
                    Apakah kendala Anda sudah teratasi? Jika tidak ada aksi dalam 3 hari, tiket akan ditutup secara otomatis oleh sistem.
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button 
                        @click="confirmSolution"
                        class="flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold transition-all shadow-sm active:scale-[0.98]"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        Ya, Masalah Selesai
                    </button>
                    <button 
                        @click="showReopenForm = true"
                        class="flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-red-200 text-red-600 rounded-lg hover:bg-red-50 font-bold transition-all active:scale-[0.98]"
                    >
                        <XCircle class="w-4 h-4" />
                        Belum, Buka Kembali
                    </button>
                </div>
            </div>

            <!-- Reopen Form -->
            <div v-else class="animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="flex items-center gap-2 mb-3 text-red-700">
                    <AlertCircle class="w-4 h-4" />
                    <span class="text-sm font-bold">Mengapa kendala belum selesai?</span>
                </div>
                
                <textarea
                    v-model="reopenForm.reason"
                    rows="3"
                    placeholder="Jelaskan alasan mengapa solusi belum berhasil atau kendala masih terjadi..."
                    class="w-full px-4 py-3 border border-red-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 resize-none mb-3"
                ></textarea>
                <p v-if="reopenForm.errors.reason" class="text-xs text-red-600 mb-3">{{ reopenForm.errors.reason }}</p>

                <div class="flex gap-2">
                    <button 
                        @click="submitReopen"
                        :disabled="reopenForm.processing || reopenForm.reason.length < 10"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold transition-all disabled:opacity-50"
                    >
                        <Send class="w-4 h-4" />
                        {{ reopenForm.processing ? 'Memproses...' : 'Kirim & Buka Kembali' }}
                    </button>
                    <button 
                        @click="showReopenForm = false"
                        class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
