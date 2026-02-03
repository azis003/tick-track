<script setup>
// import Link dari Inertia
import { Link } from '@inertiajs/vue3'

// import icons dari lucide-vue-next
import { AlertCircle, ArrowRight, Clock } from 'lucide-vue-next'

// props
const props = defineProps({
    triageQueue: {
        type: Array,
        default: () => [],
    },
})

// Fungsi untuk mendapatkan warna prioritas
const getPriorityColor = (color) => {
    const colors = {
        'red': 'bg-red-100 text-red-800 border-red-200',
        'orange': 'bg-orange-100 text-orange-800 border-orange-200',
        'yellow': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'green': 'bg-green-100 text-green-800 border-green-200',
    };
    return colors[color] || 'bg-gray-100 text-gray-800 border-gray-200';
};

// Fungsi untuk mendapatkan emoji prioritas
const getPriorityEmoji = (priority) => {
    const emojis = {
        'Critical': '🔴',
        'High': '🟠',
        'Medium': '🟡',
        'Low': '🟢',
    };
    return emojis[priority] || '⚪';
};
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-yellow-100 rounded-lg">
                        <AlertCircle class="w-5 h-5 text-yellow-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Perlu Triage
                        </h3>
                        <p class="text-sm text-gray-600">
                            Tiket yang memerlukan verifikasi
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                    {{ triageQueue.length }} tiket
                </span>
            </div>
        </div>

        <div class="p-6">
            <!-- Triage Queue List -->
            <div v-if="triageQueue.length > 0" class="space-y-3">
                <div
                    v-for="ticket in triageQueue"
                    :key="ticket.id"
                    class="flex items-center justify-between p-4 transition-colors bg-yellow-50 hover:bg-yellow-100 rounded-xl"
                >
                    <div class="flex items-center flex-1 min-w-0">
                        <!-- Priority Badge -->
                        <div class="flex-shrink-0 mr-3">
                            <span class="text-2xl">
                                {{ getPriorityEmoji(ticket.priority) }}
                            </span>
                        </div>

                        <!-- Ticket Info -->
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">
                                {{ ticket.ticket_number }}
                            </p>
                            <p class="text-sm text-gray-600 truncate">
                                {{ ticket.title }}
                            </p>
                        </div>
                    </div>

                    <!-- Time & Action -->
                    <div class="flex items-center ml-4 space-x-3">
                        <div class="flex items-center text-sm text-gray-500">
                            <Clock class="w-4 h-4 mr-1" />
                            {{ ticket.created_at }}
                        </div>
                        <Link
                            :href="`/admin/tickets/${ticket.id}/triage`"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-yellow-700 transition-all duration-200 bg-yellow-100 border border-yellow-200 rounded-lg hover:bg-yellow-200 hover:border-yellow-300"
                        >
                            Triage
                            <ArrowRight class="w-3 h-3 ml-1" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-8 text-center">
                <AlertCircle class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p class="text-gray-500">Semua tiket sudah di-triage</p>
                <p class="mt-1 text-sm text-gray-400">
                    Tidak ada tiket baru yang memerlukan verifikasi
                </p>
            </div>

            <!-- View All Button -->
            <div v-if="triageQueue.length > 0" class="pt-4 mt-4 border-t border-gray-100">
                <Link
                    href="/admin/tickets/triage"
                    class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-yellow-600 transition-colors bg-yellow-50 hover:bg-yellow-100 rounded-lg"
                >
                    Lihat Semua Tiket Perlu Triage
                    <ArrowRight class="w-4 h-4 ml-2" />
                </Link>
            </div>
        </div>
    </div>
</template>
