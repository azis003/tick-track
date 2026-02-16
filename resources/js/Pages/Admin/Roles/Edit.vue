<script setup>
// import layout
import LayoutAdmin from '@/Layouts/LayoutAdmin.vue'

// import inertia
import { Head, Link, useForm } from '@inertiajs/vue3'

// import vue
import { computed } from 'vue'

// import icons
import { ArrowLeft, Save, Shield, AlertCircle, Lock } from 'lucide-vue-next'

// props
const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Array,
        default: () => [],
    },
    rolePermissions: {
        type: Array,
        default: () => [],
    },
    groupLabels: {
        type: Object,
        default: () => ({}),
    },
    isProtected: {
        type: Boolean,
        default: false,
    },
})

// form state with existing data
const form = useForm({
    name: props.role.name,
    permissions: [...props.rolePermissions],
})

// Auto-group permissions berdasarkan prefix (module.action)
const groupedPermissions = computed(() => {
    return (props.permissions || []).reduce((groups, permission) => {
        const [module] = permission.name.split('.')
        const groupKey = module.toLowerCase()

        if (!groups[groupKey]) {
            groups[groupKey] = []
        }

        groups[groupKey].push(permission)
        return groups
    }, {})
})

// Get group label (dari config atau capitalize)
const getGroupLabel = (groupKey) => {
    return props.groupLabels[groupKey] || 
           groupKey.charAt(0).toUpperCase() + groupKey.slice(1)
}

// submit handler
const submit = () => {
    form.put(`/roles/${props.role.id}`, {
        preserveScroll: true,
    })
}

// Toggle permission
const togglePermission = (id) => {
    const index = form.permissions.indexOf(id)
    if (index > -1) {
        form.permissions.splice(index, 1)
    } else {
        form.permissions.push(id)
    }
}

// Check if permission is selected
const isSelected = (id) => {
    return form.permissions.includes(id)
}

// Select all in group
const selectAllInGroup = (groupPermissions) => {
    groupPermissions.forEach(permission => {
        if (!form.permissions.includes(permission.id)) {
            form.permissions.push(permission.id)
        }
    })
}

// Deselect all in group
const deselectAllInGroup = (groupPermissions) => {
    groupPermissions.forEach(permission => {
        const index = form.permissions.indexOf(permission.id)
        if (index > -1) {
            form.permissions.splice(index, 1)
        }
    })
}

// Count selected in group
const selectedCountInGroup = (groupPermissions) => {
    return groupPermissions.filter(permission => form.permissions.includes(permission.id)).length
}

// Total permissions count
const totalPermissions = computed(() => props.permissions.length)

// Role badge color
const roleBadgeClass = computed(() => {
    const colors = {
        'super_admin': 'bg-red-100 text-red-800 border-red-200',
        'manager': 'bg-purple-100 text-purple-800 border-purple-200',
        'helpdesk': 'bg-blue-100 text-blue-800 border-blue-200',
        'teknisi': 'bg-green-100 text-green-800 border-green-200',
        'ketua_tim': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'pegawai': 'bg-gray-100 text-gray-800 border-gray-200',
    }
    return colors[props.role.name] || 'bg-slate-100 text-slate-800 border-slate-200'
})
</script>

<template>
    <Head :title="`Edit Role - ${role.name}`" />

    <LayoutAdmin>
        <!-- Back Link -->
        <div class="mb-6">
            <Link
                href="/roles"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali ke Daftar Role
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
                                <Shield class="w-5 h-5 text-amber-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    Edit Role
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Perbarui data dan permissions role
                                </p>
                            </div>
                        </div>
                        <span :class="['inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold border', roleBadgeClass]">
                            {{ role.name }}
                        </span>
                    </div>
                </div>

                <!-- Protected Role Warning -->
                <div v-if="isProtected" class="px-6 py-4 bg-red-50 border-b border-red-100">
                    <div class="flex items-start">
                        <Lock class="w-5 h-5 text-red-500 mr-3 mt-0.5" />
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Role Sistem Terproteksi</h3>
                            <p class="mt-1 text-sm text-red-700">
                                Nama role <strong>{{ role.name }}</strong> tidak dapat diubah karena merupakan role sistem utama. Anda hanya dapat mengubah permissions.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name Input -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            placeholder="contoh: supervisor"
                            :disabled="isProtected"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all duration-200 font-mono"
                            :class="{ 
                                'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name,
                                'bg-gray-100 cursor-not-allowed': isProtected
                            }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                            <AlertCircle class="w-4 h-4 mr-1" />
                            {{ form.errors.name }}
                        </p>
                        <p v-else-if="!isProtected" class="mt-2 text-sm text-gray-500">
                            💡 Gunakan format <strong>snake_case</strong> (huruf kecil, pisah dengan _)
                        </p>
                    </div>

                    <!-- Permissions Section -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Permissions
                            </label>
                            <span class="text-sm text-gray-500">
                                {{ form.permissions.length }} dari {{ totalPermissions }} dipilih
                            </span>
                        </div>

                        <!-- Permission Groups - Auto-grouped! -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(permissions, groupKey) in groupedPermissions"
                                :key="groupKey"
                                class="border border-gray-200 rounded-lg overflow-hidden"
                            >
                                <!-- Group Header -->
                                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">{{ getGroupLabel(groupKey) }}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            ({{ selectedCountInGroup(permissions) }}/{{ permissions.length }})
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button
                                            type="button"
                                            @click="selectAllInGroup(permissions)"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Semua
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button
                                            type="button"
                                            @click="deselectAllInGroup(permissions)"
                                            class="text-xs text-gray-600 hover:text-gray-800 font-medium"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <!-- Permission Checkboxes -->
                                <div class="p-4 space-y-2">
                                    <label
                                        v-for="permission in permissions"
                                        :key="permission.id"
                                        class="flex items-center p-2 rounded-lg cursor-pointer transition-all duration-150"
                                        :class="isSelected(permission.id) 
                                            ? 'bg-amber-50 border border-amber-200' 
                                            : 'bg-gray-50 border border-transparent hover:bg-gray-100'"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="isSelected(permission.id)"
                                            @change="togglePermission(permission.id)"
                                            class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                                        />
                                        <span class="ml-2 text-sm font-mono" :class="isSelected(permission.id) ? 'text-amber-700' : 'text-gray-700'">
                                            {{ permission.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link
                            href="/roles"
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
