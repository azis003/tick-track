<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { X, AlertTriangle, CheckCircle, UserPlus, Wrench } from 'lucide-vue-next'

/**
 * TriagePanel Component
 * Panel untuk melakukan triage tiket - set final priority dan pilih aksi lanjutan
 */
const props = defineProps({
    ticket: {
        type: Object,
        required: true
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

const emit = defineEmits(['close'])

const showAssignModal = ref(false)
const triageForm = useForm({
    final_priority_id: props.ticket.user_priority_id || '',
    notes: ''
})

const assignForm = useForm({
    technician_id: '',
    notes: ''
})

const isTriaged = computed(() => props.ticket.status === 'triage')

const submitTriage = () => {
    triageForm.post(`/admin/tickets/${props.ticket.id}/triage`, {
        preserveScroll: true,
        onSuccess: () => {
            triageForm.reset()
        }
    })
}

const submitAssign = () => {
    assignForm.post(`/admin/tickets/${props.ticket.id}/assign`, {
        preserveScroll: true,
        onSuccess: () => {
            assignForm.reset()
            showAssignModal.value = false
            emit('close')
        }
    })
}

const selfHandle = () => {
    if (confirm('Anda yakin ingin mengerjakan tiket ini sendiri?')) {
        useForm({}).post(`/admin/tickets/${props.ticket.id}/self-handle`, {
            preserveScroll: true,
            onSuccess: () => emit('close')
        })
    }
}

const getPriorityColor = (color) => {
    const colorMap = {
        'gray': 'bg-gray-100 text-gray-800 border-gray-300',
        'blue': 'bg-blue-100 text-blue-800 border-blue-300',
        'yellow': 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'orange': 'bg-orange-100 text-orange-800 border-orange-300',
        'red': 'bg-red-100 text-red-800 border-red-300',
    }
    return colorMap[color] || colorMap['gray']
}
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-indigo-50">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-purple-100 rounded-lg">
                    <AlertTriangle class="w-4 h-4 text-purple-600" />
                </div>
                <h3 class="font-medium text-gray-900">Panel Triage</h3>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Step 1: Set Final Priority (only if not triaged yet) -->
            <div v-if="!isTriaged">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Step 1: Tentukan Prioritas Final</h4>
                <form @submit.prevent="submitTriage" class="space-y-4">
                    <!-- Priority Selection -->
                    <div>
                        <label class="block text-sm text-gray-600 mb-2">Prioritas Final</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label
                                v-for="priority in priorities"
                                :key="priority.id"
                                class="relative flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-all"
                                :class="[
                                    triageForm.final_priority_id === priority.id 
                                        ? `${getPriorityColor(priority.color)} border-current ring-2 ring-offset-2` 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input
                                    type="radio"
                                    :value="priority.id"
                                    v-model="triageForm.final_priority_id"
                                    class="sr-only"
                                />
                                <span class="text-sm font-medium">{{ priority.name }}</span>
                            </label>
                        </div>
                        <p v-if="triageForm.errors.final_priority_id" class="mt-1 text-sm text-red-600">
                            {{ triageForm.errors.final_priority_id }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm text-gray-600 mb-2">Catatan Triage (Opsional)</label>
                        <textarea
                            v-model="triageForm.notes"
                            rows="2"
                            placeholder="Catatan hasil verifikasi..."
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="triageForm.processing || !triageForm.final_priority_id"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <CheckCircle class="w-4 h-4" />
                        <span>{{ triageForm.processing ? 'Memproses...' : 'Verifikasi Tiket' }}</span>
                    </button>
                </form>
            </div>

            <!-- Step 2: Choose Action (only if triaged) -->
            <div v-else>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Step 2: Pilih Tindakan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Assign to Technician -->
                    <button
                        @click="showAssignModal = true"
                        class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition-all text-left"
                    >
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <UserPlus class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Tugaskan ke Teknisi</h5>
                            <p class="text-sm text-gray-500">Pilih teknisi untuk menangani</p>
                        </div>
                    </button>

                    <!-- Self Handle -->
                    <button
                        @click="selfHandle"
                        class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-all text-left"
                    >
                        <div class="p-2 bg-green-100 rounded-lg">
                            <Wrench class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Tangani Sendiri</h5>
                            <p class="text-sm text-gray-500">Kerjakan langsung sebagai Helpdesk</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showAssignModal = false"></div>
                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Tugaskan ke Teknisi</h3>
                                <button @click="showAssignModal = false" class="p-1 rounded hover:bg-gray-100">
                                    <X class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submitAssign" class="p-6 space-y-4">
                                <!-- Technician Select -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Teknisi</label>
                                    <select
                                        v-model="assignForm.technician_id"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                    >
                                        <option value="">-- Pilih Teknisi --</option>
                                        <option 
                                            v-for="tech in technicians" 
                                            :key="tech.id" 
                                            :value="tech.id"
                                        >
                                            {{ tech.name }} ({{ tech.assigned_tickets_count || 0 }} tiket aktif)
                                        </option>
                                    </select>
                                    <p v-if="assignForm.errors.technician_id" class="mt-1 text-sm text-red-600">
                                        {{ assignForm.errors.technician_id }}
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                    <textarea
                                        v-model="assignForm.notes"
                                        rows="2"
                                        placeholder="Instruksi khusus untuk teknisi..."
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                    ></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="showAssignModal = false"
                                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="assignForm.processing || !assignForm.technician_id"
                                        class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ assignForm.processing ? 'Memproses...' : 'Tugaskan' }}
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
