<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, AlertTriangle, AlertCircle, Palette, TrendingUp, Ticket } from 'lucide-vue-next'

// props
const props = defineProps({
    priority: {
        type: Object,
        required: true,
    },
})

// form state with existing data
const form = useForm({
    name: props.priority.name,
    level: props.priority.level,
    color: props.priority.color || '#EF4444',
    description: props.priority.description || '',
    is_active: props.priority.is_active,
})

// Submit handler
const submit = () => {
    form.put(`/priorities/${props.priority.id}`, {
        preserveScroll: true,
    })
}

// Predefined priorities colors
const predefinedPriorities = [
    { name: 'Critical', level: 4, color: '#EF4444', emoji: '🔴' },
    { name: 'High', level: 3, color: '#F97316', emoji: '🟠' },
    { name: 'Medium', level: 2, color: '#F59E0B', emoji: '🟡' },
    { name: 'Low', level: 1, color: '#10B981', emoji: '🟢' },
]

// Get emoji by level
const getEmojiByLevel = (level) => {
    const emojiMap = {
        4: '🔴',
        3: '🟠',
        2: '🟡',
        1: '🟢'
    }
    return emojiMap[level] || '⚪'
}

// Total tickets using this priority
const totalTickets = props.priority.user_priority_tickets_count + props.priority.final_priority_tickets_count
</script>

<template>
    <Head :title="`Edit Prioritas - ${priority.name}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/priorities"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Prioritas
            </Link>
        </div>

        <!-- Form Card -->
        <div class="max-w-4xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-amber-100 rounded-lg mr-3">
                                <AlertTriangle class="w-5 h-5 text-amber-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Edit Prioritas
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Perbarui informasi prioritas
                                </p>
                            </div>
                        </div>

                        <!-- Tickets Count Badge -->
                        <div class="flex items-center px-3 py-1.5 bg-white rounded-lg border border-amber-200 shadow-sm">
                            <Ticket class="w-4 h-4 text-amber-600 mr-2" />
                            <span class="text-sm font-semibold text-amber-700">
                                {{ totalTickets }} Tiket
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Warning if priority has tickets -->
                    <div v-if="totalTickets > 0" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <AlertCircle class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" />
                            <div>
                                <h4 class="text-sm font-semibold text-amber-900 mb-1">
                                    Prioritas Sedang Digunakan
                                </h4>
                                <p class="text-sm text-amber-700">
                                    Prioritas ini digunakan oleh <strong>{{ totalTickets }} tiket</strong>
                                    (User Priority: {{ priority.user_priority_tickets_count }}, Final Priority: {{ priority.final_priority_tickets_count }}).
                                    Perubahan akan mempengaruhi tampilan tiket-tiket tersebut.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Prioritas <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Level -->
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                            <TrendingUp class="w-4 h-4 inline mr-1" />
                            Level Prioritas <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="level"
                            type="number"
                            v-model.number="form.level"
                            min="1"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.level }"
                        />
                        <p v-if="form.errors.level" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.level }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Level saat ini: <strong>{{ priority.level }}</strong>. Level menentukan urutan prioritas (angka lebih besar = lebih urgent).
                        </p>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <Palette class="w-4 h-4 inline mr-1" />
                            Warna Badge <span class="text-red-500">*</span>
                        </label>

                        <!-- Color Picker -->
                        <div class="flex items-center space-x-3 mb-3">
                            <input
                                type="color"
                                v-model="form.color"
                                class="h-10 w-20 border border-gray-300 rounded-lg cursor-pointer"
                            />
                            <input
                                type="text"
                                v-model="form.color"
                                placeholder="#EF4444"
                                class="flex-1 px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            />
                        </div>

                        <!-- Predefined Colors -->
                        <div class="grid grid-cols-4 md:grid-cols-8 gap-2">
                            <button
                                v-for="preset in predefinedPriorities"
                                :key="preset.color"
                                type="button"
                                @click="form.color = preset.color"
                                :title="preset.name"
                                class="h-10 rounded-lg border-2 transition-all duration-200 hover:scale-110"
                                :class="form.color === preset.color ? 'border-gray-900 ring-2 ring-offset-2 ring-gray-400' : 'border-gray-200'"
                                :style="{ backgroundColor: preset.color }"
                            ></button>
                        </div>

                        <!-- Preview Badge -->
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-2">Preview:</p>
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border"
                                :style="{
                                    backgroundColor: form.color + '20',
                                    color: form.color,
                                    borderColor: form.color + '40'
                                }"
                            >
                                <span class="text-lg mr-2">{{ getEmojiByLevel(form.level) }}</span>
                                {{ form.name || 'Nama Prioritas' }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Deskripsi kapan menggunakan prioritas ini..."
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.description }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            Maksimal 500 karakter
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                            💡 Prioritas yang nonaktif tidak akan muncul saat membuat tiket
                        </p>
                        <p v-if="!form.is_active && totalTickets > 0" class="mt-2 text-sm text-amber-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            ⚠️ Tiket yang sudah ada tetap menggunakan prioritas ini meski dinonaktifkan
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/priorities"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Menyimpan...' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAdmin>
</template>
