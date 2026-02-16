<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Shield, AlertCircle } from 'lucide-vue-next'

// props
const props = defineProps({
    permission: {
        type: Object,
        required: true,
    },
})

// form state with existing data
const form = useForm({
    name: props.permission.name,
})

// submit handler
const submit = () => {
    form.put(`/permissions/${props.permission.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Edit Permission - ${permission.name}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/permissions"
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
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center">
                        <div class="p-2 bg-amber-100 rounded-lg mr-3">
                            <Shield class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Edit Permission
                            </h2>
                            <p class="text-sm text-gray-500">
                                Perbarui data permission <code class="bg-gray-100 px-1 rounded">{{ permission.name }}</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Current Info -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">ID:</span>
                                <span class="ml-2 font-medium text-gray-900">{{ permission.id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Guard:</span>
                                <span class="ml-2 font-medium text-gray-900">{{ permission.guard_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Name Input -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Permission <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            placeholder="contoh: reports.export"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200 font-mono"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.name }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Gunakan format <strong>module.action</strong> (contoh: tickets.create, reports.view)
                        </p>
                    </div>

                    <!-- Warning Box -->
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Perhatian</h3>
                                <div class="mt-2 text-sm text-amber-700">
                                    <p>Mengubah nama permission dapat mempengaruhi akses user yang menggunakan permission ini. Pastikan Anda memperbarui kode yang mereferensikan nama permission lama.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/permissions"
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
