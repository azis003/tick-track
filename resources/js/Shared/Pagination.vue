<script setup>
//import Link
import { Link } from '@inertiajs/vue3'

// props
defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav class="flex justify-end">
        <ul class="inline-flex items-center -space-x-px">
            <li
                v-for="(link, index) in links"
                :key="index"
                :class="`
                    ${link.url == null ? 'cursor-not-allowed opacity-50' : ''} 
                    ${link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}
                    ${index === 0 ? 'rounded-l-lg' : ''}
                    ${index === links.length - 1 ? 'rounded-r-lg' : ''}
                    relative inline-flex items-center border px-3 py-2 text-sm font-medium focus:z-20
                `"
            >
                <component
                    :is="link.url ? Link : 'span'"
                    :href="link.url"
                    v-html="link.label"
                    :class="`
                        ${link.url == null ? 'pointer-events-none' : ''}
                        ${link.active ? 'text-blue-600' : 'text-gray-700 hover:text-gray-900'}
                        inline-flex items-center
                    `"
                />
            </li>
        </ul>
    </nav>
</template>
