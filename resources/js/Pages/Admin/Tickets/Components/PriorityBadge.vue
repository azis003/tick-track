<script setup>
/**
 * PriorityBadge Component
 * Menampilkan badge prioritas dengan warna dan icon
 */
defineProps({
    priority: {
        type: Object,
        required: true,
        validator: (value) => value && value.name
    }
})

const colorClasses = {
    red: 'bg-red-100 text-red-800 border-red-300',
    orange: 'bg-orange-100 text-orange-800 border-orange-300',
    yellow: 'bg-yellow-100 text-yellow-800 border-yellow-300',
    green: 'bg-green-100 text-green-800 border-green-300',
    blue: 'bg-blue-100 text-blue-800 border-blue-300',
    gray: 'bg-gray-100 text-gray-800 border-gray-300',
}
</script>

<template>
    <span
        :class="[
            colorClasses[priority.color] || colorClasses.gray,
            'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium border'
        ]"
    >
        <component
            v-if="priority.icon"
            :is="priority.icon"
            class="w-3 h-3"
        />
        {{ priority.name }}
    </span>
</template>
