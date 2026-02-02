<script setup>
// import Link dan usePage dari Inertia
import { Link, usePage } from '@inertiajs/vue3'

// import icon dari lucide-vue
import { Ticket } from 'lucide-vue-next'

// get page props "app"
const { app } = usePage().props;
</script>

<template>
    <Link href="/admin/dashboard">
        <div class="flex items-center flex-shrink-0">
            <div class="relative">
                <div class="bg-white rounded-xl border border-gray-200 p-1 shadow-sm">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg"></div>
                        <div class="relative w-10 h-10 flex items-center justify-center">
                            <Ticket
                                :size="28"
                                class="text-blue-600"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <h1 class="text-md uppercase font-bold text-gray-900">
                    TickTrack
                </h1>
                <p class="text-xs text-gray-500 font-medium italic mt-0.5">
                    Helpdesk BP2 Komdigi
                </p>
            </div>
        </div>
    </Link>
</template>
