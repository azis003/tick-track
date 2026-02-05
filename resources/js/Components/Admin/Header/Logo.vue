<script setup>
// import Link dan usePage dari Inertia
import { Link, usePage } from '@inertiajs/vue3'

// get page props "app"
const { app } = usePage().props;
</script>

<template>
    <Link href="/admin/dashboard">
        <div class="flex items-center flex-shrink-0">
            <!-- Logo Image -->
            <div class="relative">
                <div class="bg-white rounded-xl border border-gray-200 p-2 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <img 
                        src="/images/logo-idth.png" 
                        alt="IDTH Logo" 
                        class="w-10 h-10 object-contain"
                    />
                </div>
            </div>

            <!-- App Name & Tagline -->
            <div class="ml-3">
                <h1 class="text-base font-bold text-gray-900 leading-tight">
                    IDTH
                </h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Indonesia Digital Test House
                </p>
            </div>
        </div>
    </Link>
</template>
