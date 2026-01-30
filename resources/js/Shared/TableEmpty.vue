<script setup>
//import icons
import {Inbox} from 'lucide-vue-next'

//define props
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    colSpan: {
        type: Number,
        required: true,
    },
})
</script>

<template>
    <tr>
        <td :colspan="colSpan" class="px-6 py-16 text-center">
            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                <div class="p-4 bg-gray-50 rounded-full mb-4">
                    <Inbox class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-1">
                    {{ title }}
                </h3>
                <p v-if="description" class="text-sm text-gray-500 leading-relaxed">
                    {{ description }}
                </p>
            </div>
        </td>
    </tr>
</template>
