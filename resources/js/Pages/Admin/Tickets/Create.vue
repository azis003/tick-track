<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Send, Ticket, AlertCircle, Paperclip, Loader2 } from 'lucide-vue-next'

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
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Buat Tiket Baru
                            </h2>
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
                        <p class="text-xs text-gray-500 mb-3">Cari dan pilih pegawai yang melaporkan kendala</p>
                        
                        <SearchableSelect
                            v-model="form.reporter_id"
                            :options="users"
                            placeholder="Cari nama pelapor"
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
                            placeholder="Tuliskan judul kendala"
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
                        <SearchableSelect
                            v-model="form.category_id"
                            :options="categories"
                            placeholder="Pilih kategori kendala"
                            label="name"
                            value="id"
                            :error="form.errors.category_id"
                        />
                        <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dampak / Urgensi <span class="text-red-500">*</span>
                        </label>
                        <SearchableSelect
                            v-model="form.user_priority_id"
                            :options="priorities"
                            placeholder="Pilih dampak keluhan"
                            label="name"
                            value="id"
                            :error="form.errors.user_priority_id"
                        />
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
                            placeholder="Jelaskan kendala yang Anda alami secara detail"
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
                            <Send v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Sedang Mengirim...' : 'Kirim Tiket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAdmin>
</template>
