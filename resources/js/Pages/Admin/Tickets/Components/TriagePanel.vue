<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { AlertTriangle, CheckCircle, UserPlus, Wrench, Send } from 'lucide-vue-next'

/**
 * TriagePanel Component
 * Panel untuk melakukan triage tiket - set final priority dan langsung assign/self-handle
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

// Action type: 'assign' or 'self_handle'
const selectedAction = ref('assign')

const triageForm = useForm({
    final_priority_id: props.ticket.user_priority_id || '',
    notes: '',
    action: 'assign',
    technician_id: ''
})

// Sync action with form
watch(selectedAction, (val) => {
    triageForm.action = val
    if (val === 'self_handle') {
        triageForm.technician_id = ''
    }
})

const canSubmit = computed(() => {
    if (!triageForm.final_priority_id) return false
    if (selectedAction.value === 'assign' && !triageForm.technician_id) return false
    return true
})

const submitTriage = () => {
    triageForm.post(`/admin/tickets/${props.ticket.id}/triage`, {
        preserveScroll: true,
        onSuccess: () => {
            triageForm.reset()
            emit('close')
        }
    })
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
                <h3 class="font-medium text-gray-900">Verifikasi & Penugasan</h3>
            </div>
        </div>

        <form @submit.prevent="submitTriage" class="p-6 space-y-6">
            <!-- Section 1: Final Priority -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 mr-2 text-xs font-bold text-white bg-purple-600 rounded-full">1</span>
                    Prioritas Final
                </label>
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

            <!-- Section 2: Action Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 mr-2 text-xs font-bold text-white bg-purple-600 rounded-full">2</span>
                    Tindakan
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Assign to Technician -->
                    <label
                        class="flex items-center gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all"
                        :class="selectedAction === 'assign' 
                            ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20' 
                            : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input
                            type="radio"
                            value="assign"
                            v-model="selectedAction"
                            class="sr-only"
                        />
                        <div class="p-2 rounded-lg" :class="selectedAction === 'assign' ? 'bg-indigo-100' : 'bg-gray-100'">
                            <UserPlus class="w-5 h-5" :class="selectedAction === 'assign' ? 'text-indigo-600' : 'text-gray-500'" />
                        </div>
                        <div>
                            <h5 class="font-medium" :class="selectedAction === 'assign' ? 'text-indigo-900' : 'text-gray-900'">Tugaskan ke Teknisi</h5>
                            <p class="text-sm text-gray-500">Pilih teknisi untuk menangani</p>
                        </div>
                    </label>

                    <!-- Self Handle -->
                    <label
                        class="flex items-center gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all"
                        :class="selectedAction === 'self_handle' 
                            ? 'border-green-500 bg-green-50 ring-2 ring-green-500/20' 
                            : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input
                            type="radio"
                            value="self_handle"
                            v-model="selectedAction"
                            class="sr-only"
                        />
                        <div class="p-2 rounded-lg" :class="selectedAction === 'self_handle' ? 'bg-green-100' : 'bg-gray-100'">
                            <Wrench class="w-5 h-5" :class="selectedAction === 'self_handle' ? 'text-green-600' : 'text-gray-500'" />
                        </div>
                        <div>
                            <h5 class="font-medium" :class="selectedAction === 'self_handle' ? 'text-green-900' : 'text-gray-900'">Tangani Sendiri</h5>
                            <p class="text-sm text-gray-500">Kerjakan langsung sebagai Helpdesk</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Section 3: Technician Selection (only if assign) -->
            <div v-if="selectedAction === 'assign'">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 mr-2 text-xs font-bold text-white bg-purple-600 rounded-full">3</span>
                    Pilih Teknisi
                </label>
                <select
                    v-model="triageForm.technician_id"
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
                <p v-if="triageForm.errors.technician_id" class="mt-1 text-sm text-red-600">
                    {{ triageForm.errors.technician_id }}
                </p>
            </div>

            <!-- Section 4: Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea
                    v-model="triageForm.notes"
                    rows="2"
                    placeholder="Catatan untuk teknisi atau hasil verifikasi..."
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                ></textarea>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="triageForm.processing || !canSubmit"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                :class="selectedAction === 'assign' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-green-600 hover:bg-green-700'"
            >
                <Send class="w-4 h-4" />
                <span v-if="triageForm.processing">Memproses...</span>
                <span v-else-if="selectedAction === 'assign'">Verifikasi & Tugaskan</span>
                <span v-else>Verifikasi & Tangani Sendiri</span>
            </button>
        </form>
    </div>
</template>
