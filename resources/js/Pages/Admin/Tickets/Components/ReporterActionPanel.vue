<script setup>
import { router } from '@inertiajs/vue3'
import { Info, Send } from 'lucide-vue-next'

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
})

const resumeTicket = () => {
    if (confirm('Sudah memberikan informasi yang dibutuhkan? Tiket akan dikembalikan ke teknisi.')) {
        router.post(`/admin/tickets/${props.ticket.id}/resume`, {
            notes: 'User mengonfirmasi informasi telah diberikan melalui tombol aksi.'
        }, {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <div class="bg-orange-50 border border-orange-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 bg-orange-100/50 border-b border-orange-200 flex items-center gap-2">
            <Info class="w-5 h-5 text-orange-600" />
            <h3 class="font-bold text-orange-900">Butuh Respon Anda</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-orange-800 mb-4 font-medium italic">
                "{{ ticket.pending_reason }}"
            </p>
            <p class="text-sm text-gray-600 mb-6">
                Silakan berikan informasi atau tanggapan melalui kolom komentar di bawah. Setelah itu, klik tombol di bawah untuk mengembalikan tiket ke teknisi.
            </p>
            
            <button 
                @click="resumeTicket"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-bold transition-all shadow-md active:scale-[0.98]"
            >
                <Send class="w-4 h-4" />
                Kirim & Lanjutkan Tiket
            </button>
        </div>
    </div>
</template>
