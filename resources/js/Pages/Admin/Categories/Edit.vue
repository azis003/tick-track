<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Tag, AlertCircle, Palette, Ticket } from 'lucide-vue-next'

// props
const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
})

// form state with existing data
const form = useForm({
    name: props.category.name,
    icon: props.category.icon || '',
    color: props.category.color || '#3B82F6',
    description: props.category.description || '',
    is_active: props.category.is_active,
})

// Submit handler
const submit = () => {
    form.put(`/admin/categories/${props.category.id}`, {
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
    <Head :title="`Edit Kategori - ${category.name}`" />

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
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-amber-100 rounded-lg mr-3">
                                <Tag class="w-5 h-5 text-amber-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Edit Kategori
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Perbarui informasi kategori
                                </p>
                            </div>
                        </div>

                        <!-- Tickets Count Badge -->
                        <div class="flex items-center px-3 py-1.5 bg-white rounded-lg border border-amber-200 shadow-sm">
                            <Ticket class="w-4 h-4 text-amber-600 mr-2" />
                            <span class="text-sm font-semibold text-amber-700">
                                {{ category.tickets_count || 0 }} Tiket
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Warning if category has tickets -->
                    <div v-if="category.tickets_count > 0" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <AlertCircle class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" />
                            <div>
                                <h4 class="text-sm font-semibold text-amber-900 mb-1">
                                    Kategori Sedang Digunakan
                                </h4>
                                <p class="text-sm text-amber-700">
                                    Kategori ini digunakan oleh <strong>{{ category.tickets_count }} tiket</strong>. 
                                    Perubahan nama atau warna akan mempengaruhi tampilan tiket-tiket tersebut.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
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
                                class="flex-1 px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
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
                                <span v-if="form.icon" class="ml-2">{{ form.icon }}</span>
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
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
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
                            💡 Kategori yang nonaktif tidak akan muncul saat membuat tiket
                        </p>
                        <p v-if="!form.is_active && category.tickets_count > 0" class="mt-2 text-sm text-amber-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            ⚠️ Tiket yang sudah ada tetap menggunakan kategori ini meski dinonaktifkan
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
