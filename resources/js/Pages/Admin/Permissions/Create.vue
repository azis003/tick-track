<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Shield, AlertCircle } from 'lucide-vue-next'

// form state
const form = useForm({
    name: '',
})

// submit handler
const submit = () => {
    form.post('/admin/permissions', {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Tambah Permission" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/admin/permissions"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Permission
            </Link>
        </div>

        <!-- Form Card -->
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center">
                        <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                            <Shield class="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Tambah Permission Baru
                            </h2>
                            <p class="text-sm text-gray-500">
                                Buat permission baru untuk mengatur akses sistem
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name Input -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Permission <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            placeholder="contoh: export-reports"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all duration-200 font-mono"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.name }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Gunakan format <strong>kebab-case</strong> (huruf kecil, pisah dengan tanda -)
                        </p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Contoh nama permission</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li><code class="bg-blue-100 px-1 rounded">create-ticket</code></li>
                                        <li><code class="bg-blue-100 px-1 rounded">view-all-users</code></li>
                                        <li><code class="bg-blue-100 px-1 rounded">export-reports</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/admin/permissions"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
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
