<script setup>
/**
 * StatusBadge Component
 * Menampilkan badge status tiket dengan warna sesuai status
 */
defineProps({
    status: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    }
})

const colorClasses = {
    blue: 'bg-blue-100 text-blue-800 border-blue-200',
    purple: 'bg-purple-100 text-purple-800 border-purple-200',
    indigo: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    yellow: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    orange: 'bg-orange-100 text-orange-800 border-orange-200',
    pink: 'bg-pink-100 text-pink-800 border-pink-200',
    green: 'bg-green-100 text-green-800 border-green-200',
    gray: 'bg-gray-100 text-gray-800 border-gray-200',
    red: 'bg-red-100 text-red-800 border-red-200',
}

const statusColors = {
    new: 'blue',
    triage: 'purple',
    assigned: 'indigo',
    in_progress: 'yellow',
    pending_user: 'orange',
    pending_external: 'orange',
    waiting_approval: 'pink',
    resolved: 'green',
    closed: 'gray',
    reopened: 'red',
}
</script>

<template>
    <span
        :class="[
            colorClasses[statusColors[status]] || colorClasses.gray,
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border'
        ]"
    >
        {{ label }}
    </span>
</template>
