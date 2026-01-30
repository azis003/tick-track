<script setup>
//import link
import {Link} from '@inertiajs/vue3'

//import icon
import {Plus} from 'lucide-vue-next'

// import named export dari Permissions.js
import { hasAnyPermission } from '@/Utils/Permissions'

//define props
defineProps({
    showButton: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    action: {
        type: String,
        default: '#',
    },
    actionText: {
        type: String,
        default: '',
    },
    permission: {
        type: [String, Array],
        default: null,
    },
})
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                {{ title }}
            </h1>
            <p v-if="description" class="mt-1 text-sm text-gray-500">
                {{ description }}
            </p>
        </div>

        <div v-if="showButton" class="mt-4 sm:mt-0">
            <!-- Jika ada permission, cek dulu. Jika tidak ada permission, langsung tampilkan -->
            <template v-if="permission ? hasAnyPermission(Array.isArray(permission) ? permission : [permission]) : true">
                <Link
                    :href="action"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm hover:shadow transition-all duration-200"
                >
                    <Plus class="w-5 h-5 mr-2" />
                    {{ actionText }}
                </Link>
            </template>
        </div>
    </div>
</template>