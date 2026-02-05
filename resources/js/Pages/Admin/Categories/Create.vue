<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Tag, AlertCircle, Palette } from 'lucide-vue-next'

// form state
const form = useForm({
    name: '',
    icon: '',
    color: '#3B82F6', // Default blue
    description: '',
    is_active: true,
})

// Submit handler
const submit = () => {
    form.post('/admin/categories', {
        preserveScroll: true,
    })
}

// Predefined colors
const predefinedColors = [
    { name: 'Biru', value: '#3B82F6' },
    { name: 'Hijau', value: '#10B981' },
    { name: 'Merah', value: '#EF4444' },
    { name: 'Kuning', value: '#F59E0B' },
    { name: 'Ungu', value: '#8B5CF6' },
    { name: 'Pink', value: '#EC4899' },
    { name: 'Cyan', value: '#06B6D4' },
    { name: 'Orange', value: '#F97316' },
]
</script>

<template>
    <Head title="Tambah Kategori" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/admin/categories"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Kategori
            </Link>
        </div>

        <!-- Form Card -->
        <div class="max-w-4xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <Tag class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Tambah Kategori
                            </h2>
                            <p class="text-sm text-gray-500">
                                Buat kategori baru untuk klasifikasi tiket
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            placeholder="contoh: Hardware"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <Palette class="w-4 h-4 inline mr-1" />
                            Warna Badge <span class="text-gray-400">(Opsional)</span>
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
                                placeholder="#3B82F6"
                                class="flex-1 px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                            />
                        </div>

                        <!-- Predefined Colors -->
                        <div class="grid grid-cols-4 md:grid-cols-8 gap-2">
                            <button
                                v-for="color in predefinedColors"
                                :key="color.value"
                                type="button"
                                @click="form.color = color.value"
                                :title="color.name"
                                class="h-10 rounded-lg border-2 transition-all duration-200 hover:scale-110"
                                :class="form.color === color.value ? 'border-gray-900 ring-2 ring-offset-2 ring-gray-400' : 'border-gray-200'"
                                :style="{ backgroundColor: color.value }"
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
                                <Tag class="w-4 h-4 mr-2" />
                                {{ form.name || 'Nama Kategori' }}
                            </span>
                        </div>
                    </div>

                    <!-- Icon (Optional) -->
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                            Icon/Emoji <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <input
                            id="icon"
                            type="text"
                            v-model="form.icon"
                            placeholder="contoh: 💻 atau 🔧"
                            maxlength="10"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            💡 Gunakan emoji untuk membuat kategori lebih menarik
                        </p>
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
                            placeholder="Deskripsi singkat tentang kategori ini..."
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 resize-none"
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
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                            💡 Kategori yang nonaktif tidak akan muncul saat membuat tiket
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/admin/categories"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LayoutAdmin>
</template>
