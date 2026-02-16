<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, Building2, AlertCircle, Users } from 'lucide-vue-next'

// props
const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
})

// form state with existing data
const form = useForm({
    name: props.unit.name,
    description: props.unit.description || '',
    head_user_id: props.unit.head_user_id || '',
    is_active: props.unit.is_active,
})

// Submit handler
const submit = () => {
    form.put(`/units/${props.unit.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Edit Unit - ${unit.name}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/units"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Unit Kerja
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
                                <Building2 class="w-5 h-5 text-amber-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Edit Unit Kerja
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Perbarui informasi unit kerja
                                </p>
                            </div>
                        </div>

                        <!-- Members Count Badge -->
                        <div class="flex items-center px-3 py-1.5 bg-white rounded-lg border border-amber-200 shadow-sm">
                            <Users class="w-4 h-4 text-amber-600 mr-2" />
                            <span class="text-sm font-semibold text-amber-700">
                                {{ unit.members?.length || 0 }} Anggota
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Unit Kerja <span class="text-red-500">*</span>
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

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Deskripsi singkat tentang unit kerja ini..."
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

                    <!-- Head User (Ketua Tim) -->
                    <div>
                        <label for="head_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Ketua Tim <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <select
                            id="head_user_id"
                            v-model="form.head_user_id"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.head_user_id }"
                        >
                            <option value="">-- Pilih Ketua Tim --</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.head_user_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.head_user_id }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Ketua Tim saat ini: <span class="font-semibold">{{ unit.head?.name || 'Belum ada' }}</span>
                        </p>
                    </div>

                    <!-- Members List (Read-only Info) -->
                    <div v-if="unit.members && unit.members.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                            <Users class="w-4 h-4 mr-2" />
                            Anggota Unit ({{ unit.members.length }})
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div
                                v-for="member in unit.members"
                                :key="member.id"
                                class="flex items-center text-sm text-blue-800 bg-white rounded px-3 py-2 border border-blue-100"
                            >
                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                {{ member.name }}
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-blue-700">
                            ⚠️ Unit tidak dapat dihapus selama masih memiliki anggota
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
                            💡 Unit yang nonaktif akan disembunyikan dari sistem
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/units"
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
