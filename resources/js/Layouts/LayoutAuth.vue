<script setup>
import { usePage } from '@inertiajs/vue3'
import { Building2 } from 'lucide-vue-next'

// Ambil data aplikasi dari shared props
const { app } = usePage().props
</script>

<template>
    <div class="relative min-h-screen bg-white">
        <div class="flex flex-col lg:flex-row min-h-screen">
            
            <!-- Sisi Kiri: Slot untuk Halaman (Login) -->
            <div class="w-full lg:w-[45%] flex flex-col bg-white">
                <slot />
            </div>

            <!-- Sisi Kanan: Branding (Dark Side) -->
            <div class="hidden lg:flex w-full lg:w-[55%] relative overflow-hidden bg-[#0a0f1c]">
                <!-- Decorative Grids -->
                <div class="absolute inset-0 opacity-10 pointer-events-none">
                    <div class="absolute inset-0" 
                         style="background-image: radial-gradient(#3a4a6e 1px, transparent 1px); background-size: 40px 40px;">
                    </div>
                </div>
                
                <div class="relative w-full flex flex-col items-center justify-center p-12 text-center">
                    <!-- Icon Card -->
                    <div class="mb-8 w-24 h-24 bg-[#1e293b] rounded-2xl flex items-center justify-center shadow-2xl border border-white/5">
                        <Building2 :size="48" class="text-white" />
                    </div>
                    
                    <!-- Branding Text -->
                    <div class="max-w-md">
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Sistem Manajemen Keluhan dan Tiket IT <span class="text-white font-semibold">BP2 Komdigi</span> - Hak Cipta {{ new Date().getFullYear() }} © 
                            <br>
                            Dilindungi Undang-Undang.
                        </p>
                    </div>

                    <!-- Bottom Glow -->
                    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/10 blur-[120px] rounded-full"></div>
                </div>
            </div>

        </div>
    </div>
</template>
