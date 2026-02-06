<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Ticket, AlertCircle, Paperclip, Loader2 } from 'lucide-vue-next'

// import components
import AttachmentUpload from './Components/AttachmentUpload.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

// props
const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    priorities: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
    isHelpdesk: {
        type: Boolean,
        default: false
    }
})

// form state
const form = useForm({
    title: '',
    description: '',
    category_id: '',
    user_priority_id: '',
    reporter_id: '', // Hanya untuk helpdesk
    attachments: [],
})

// Submit handler
const submit = () => {
    form.post('/admin/tickets', {
        preserveScroll: true,
        forceFormData: true,
    })
}

// Handle attachment update
const handleAttachmentsUpdate = (files) => {
    form.attachments = files
}
</script>

<template>
    <Head title="Buat Tiket" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/admin/tickets/my-tickets"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Tiket Saya
            </Link>
        </div>

        <!-- Form Card -->
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <Ticket class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Buat Tiket Baru
                            </h2>
                            <p class="text-sm text-gray-500">
                                Laporkan kendala TI untuk ditangani oleh tim support
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Reporter Selection (Hanya untuk Helpdesk) -->
                    <div v-if="isHelpdesk">
                        <label for="reporter_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Pelapor <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-3">Cari dan pilih pegawai yang melaporkan kendala ini</p>
                        
                        <SearchableSelect
                            v-model="form.reporter_id"
                            :options="users"
                            placeholder="Cari nama atau email pelapor..."
                            label="name"
                            value="id"
                            :error="form.errors.reporter_id"
                        />
                        
                        <p v-if="form.errors.reporter_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.reporter_id }}
                        </p>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Judul Kendala <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            type="text"
                            v-model="form.title"
                            placeholder="Contoh: Komputer tidak bisa menyala"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <!-- Empty state -->
                        <div v-if="!categories || categories.length === 0" class="p-6 border-2 border-dashed border-gray-200 rounded-lg text-center">
                            <div class="text-gray-400 mb-2">
                                <svg class="mx-auto h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Belum ada kategori tersedia</p>
                            <p class="text-xs text-gray-400 mt-1">Hubungi admin untuk menambahkan kategori</p>
                        </div>
                        <!-- Category buttons -->
                        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                type="button"
                                @click="form.category_id = category.id"
                                class="p-4 rounded-lg border-2 text-center transition-all duration-200 hover:shadow-md"
                                :class="[
                                    form.category_id === category.id
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <span v-if="category.icon" class="text-2xl block mb-2">{{ category.icon }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Urgensi <span class="text-red-500">*</span>
                        </label>
                        <!-- Empty state -->
                        <div v-if="!priorities || priorities.length === 0" class="p-6 border-2 border-dashed border-gray-200 rounded-lg text-center">
                            <div class="text-gray-400 mb-2">
                                <svg class="mx-auto h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Belum ada tingkat urgensi tersedia</p>
                            <p class="text-xs text-gray-400 mt-1">Hubungi admin untuk menambahkan prioritas</p>
                        </div>
                        <!-- Priority buttons -->
                        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button
                                v-for="priority in priorities"
                                :key="priority.id"
                                type="button"
                                @click="form.user_priority_id = priority.id"
                                class="p-4 rounded-lg border-2 text-center transition-all duration-200 hover:shadow-md"
                                :class="[
                                    form.user_priority_id === priority.id
                                        ? `border-${priority.color}-500 bg-${priority.color}-50`
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                                :style="form.user_priority_id === priority.id ? { borderColor: priority.color, backgroundColor: priority.color + '10' } : {}"
                            >
                                <span v-if="priority.icon" class="text-xl block mb-1">{{ priority.icon }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ priority.name }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.user_priority_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.user_priority_id }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi Kendala <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            placeholder="Jelaskan kendala yang Anda alami secara detail...

Contoh:
- Apa yang terjadi?
- Kapan mulai terjadi?
- Sudah coba langkah apa saja?"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.description }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            Maksimal 5000 karakter
                        </p>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <Paperclip class="w-4 h-4 inline mr-1" />
                            Lampiran <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <AttachmentUpload @update:files="handleAttachmentsUpdate" />
                        <p v-if="form.errors.attachments" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.attachments }}
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/admin/tickets/my-tickets"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                            <Save v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Sedang Mengirim...' : 'Kirim Tiket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAdmin>
</template>
