<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import icons
import { ArrowLeft, Save, UserCog, AlertCircle } from 'lucide-vue-next'

// props
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    units: {
        type: Array,
        default: () => [],
    },
    userRoles: {
        type: Array,
        default: () => [],
    },
})

// form state with existing data
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    nip: props.user.nip,
    phone: props.user.phone || '',
    unit_id: props.user.unit_id || '',
    is_active: props.user.is_active,
    roles: [...props.userRoles],
})

// Toggle role
const toggleRole = (roleId) => {
    const index = form.roles.indexOf(roleId)
    if (index > -1) {
        form.roles.splice(index, 1)
    } else {
        form.roles.push(roleId)
    }
}

// Check if role is selected
const isRoleSelected = (roleId) => {
    return form.roles.includes(roleId)
}

// Submit handler
const submit = () => {
    form.put(`/users/${props.user.id}`, {
        preserveScroll: true,
    })
}

// Role badge colors
const getRoleBadgeClass = (roleName) => {
    const colors = {
        'super_admin': 'bg-red-100 text-red-800 border-red-200',
        'manager': 'bg-purple-100 text-purple-800 border-purple-200',
        'helpdesk': 'bg-blue-100 text-blue-800 border-blue-200',
        'teknisi': 'bg-green-100 text-green-800 border-green-200',
        'ketua_tim': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'pegawai': 'bg-gray-100 text-gray-800 border-gray-200',
    }
    return colors[roleName] || 'bg-slate-100 text-slate-800 border-slate-200'
}
</script>

<template>
    <Head :title="`Edit User - ${user.name}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/users"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar User
            </Link>
        </div>

        <!-- Form Card -->
        <div class="max-w-4xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center">
                        <div class="p-2 bg-amber-100 rounded-lg mr-3">
                            <UserCog class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Edit User
                            </h2>
                            <p class="text-sm text-gray-500">
                                Perbarui data pengguna dan role akses
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIP -->
                        <div>
                            <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">
                                NIP <span class="text-gray-400">(Opsional)</span>
                            </label>
                            <input
                                id="nip"
                                type="text"
                                v-model="form.nip"
                                placeholder="Kosongkan jika tidak memiliki NIP"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.nip }"
                            />
                            <p v-if="form.errors.nip" class="mt-2 text-sm text-red-600 flex items-center">
                                <AlertCircle class="w-4 h-4 mr-1" />
                                {{ form.errors.nip }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                No. Telepon
                            </label>
                            <input
                                id="phone"
                                type="text"
                                v-model="form.phone"
                                placeholder="contoh: 081234567890"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.phone }"
                            />
                            <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600 flex items-center">
                                <AlertCircle class="w-4 h-4 mr-1" />
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            autocomplete="off"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password (Optional) -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="Kosongkan jika tidak ingin mengubah password"
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.password }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Kosongkan jika tidak ingin mengubah password
                        </p>
                    </div>

                    <!-- Unit Kerja -->
                    <div>
                        <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja
                        </label>
                        <select
                            id="unit_id"
                            v-model="form.unit_id"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200"
                            :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.unit_id }"
                        >
                            <option value="">-- Pilih Unit Kerja --</option>
                            <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                {{ unit.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.unit_id" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.unit_id }}
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
                            💡 User yang nonaktif tidak dapat login ke sistem
                        </p>
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Role <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                v-for="role in roles"
                                :key="role.id"
                                class="flex items-center p-3 rounded-lg cursor-pointer transition-all duration-150 border"
                                :class="isRoleSelected(role.id) 
                                    ? 'bg-amber-50 border-amber-200' 
                                    : 'bg-gray-50 border-transparent hover:bg-gray-100'"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isRoleSelected(role.id)"
                                    @change="toggleRole(role.id)"
                                    class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                                />
                                <span
                                    :class="[
                                        'ml-3 inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium border',
                                        getRoleBadgeClass(role.name)
                                    ]"
                                >
                                    {{ role.name }}
                                </span>
                            </label>
                        </div>

                        <p v-if="form.errors.roles" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.roles }}
                        </p>
                        <p v-else class="mt-2 text-sm text-gray-500">
                            💡 Pilih minimal 1 role untuk user ini
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/users"
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
