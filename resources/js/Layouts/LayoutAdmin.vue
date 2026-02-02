<script setup>
// import ref, onMounted, onBeforeUnmount dari vue
import { ref, onMounted, onBeforeUnmount } from 'vue'

// import usePage dan router dari Inertia
import { usePage, router } from '@inertiajs/vue3'

// import components Header
import Logo from '@/Components/Admin/Header/Logo.vue'
import DesktopMenu from '@/Components/Admin/Header/DesktopMenu.vue'
import MobileMenu from '@/Components/Admin/Header/MobileMenu.vue'
import UserDropdown from '@/Components/Admin/Header/UserDropdown.vue'

// import icons dari lucide vue
import { Menu, X } from 'lucide-vue-next'

// import Sweet Alert
import Swal from 'sweetalert2'

// destruct auth dan flash dari props
const { auth, flash } = usePage().props

// state mobile menu open
const isMobileMenuOpen = ref(false)

// state active dropdown
const activeDropdown = ref(null)

// ref untuk dropdown container (click outside detection)
const dropdownRef = ref(null)
const userDropdownRef = ref(null)

/**
 * Menutup semua dropdown
 */
const closeAllDropdowns = () => {
    activeDropdown.value = null
}

/**
 * Toggle dropdown menu
 */
const toggleDropdown = (dropdownName) => {
    activeDropdown.value = activeDropdown.value === dropdownName ? null : dropdownName
}

/**
 * Handle klik item dropdown (untuk mobile)
 */
const handleDropdownItemClick = () => {
    activeDropdown.value = null
    isMobileMenuOpen.value = false
}

/**
 * Handle klik di luar dropdown untuk menutup
 */
const handleClickOutside = (event) => {
    const dropdownEl = dropdownRef.value
    const userEl = userDropdownRef.value

    // Tutup dropdown jika klik di luar area dropdown
    if (dropdownEl && !dropdownEl.contains(event.target)) {
        if (userEl && !userEl.contains(event.target)) {
            closeAllDropdowns()
        }
    }
}

/**
 * Handle ESC key untuk menutup dropdown dan mobile menu
 */
const handleEscapeKey = (event) => {
    if (event.key === 'Escape') {
        closeAllDropdowns()
        isMobileMenuOpen.value = false
    }
}

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)
    document.addEventListener('keydown', handleEscapeKey)

    // SweetAlert untuk flash message (sesuai HandleInertiaRequests)
    router.on('success', (event) => {
        const flash = event.detail.page.props.flash;
        
        if (flash?.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flash.success,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }

        if (flash?.error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: flash.error,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }

        if (flash?.warning) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: flash.warning,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }
    });
})

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside)
    document.removeEventListener('keydown', handleEscapeKey)
});
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left Section: Logo & Desktop Menu -->
                    <div class="flex items-center">
                        <!-- Logo TickTrack -->
                        <Logo />

                        <!-- Desktop Navigation Menu -->
                        <div ref="dropdownRef" class="hidden lg:flex lg:ml-10">
                            <DesktopMenu
                                :activeDropdown="activeDropdown"
                                :toggleDropdown="toggleDropdown"
                                :closeAllDropdowns="closeAllDropdowns"
                            />
                        </div>
                    </div>

                    <!-- Right Section: User Dropdown & Mobile Toggle -->
                    <div class="flex items-center gap-3">
                        <!-- User Dropdown (Desktop) -->
                        <div ref="userDropdownRef" class="hidden lg:block">
                            <UserDropdown
                                :auth="auth"
                                :activeDropdown="activeDropdown"
                                :toggleDropdown="toggleDropdown"
                            />
                        </div>

                        <!-- Mobile Menu Toggle Button -->
                        <button
                            type="button"
                            @click="() => { isMobileMenuOpen = !isMobileMenuOpen; activeDropdown = null }"
                            class="inline-flex items-center justify-center p-2 text-gray-600 rounded-xl lg:hidden hover:text-gray-900 hover:bg-gray-100 transition-all duration-200"
                        >
                            <X v-if="isMobileMenuOpen" class="w-6 h-6" />
                            <Menu v-else class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu (Muncul saat toggle aktif) -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <MobileMenu
                    v-if="isMobileMenuOpen"
                    :auth="auth"
                    :activeDropdown="activeDropdown"
                    :toggleDropdown="toggleDropdown"
                    :handleDropdownItemClick="handleDropdownItemClick"
                    :setIsMobileMenuOpen="(v) => (isMobileMenuOpen = v)"
                />
            </transition>
        </nav>

        <!-- Main Content Area -->
        <main class="py-8">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <!-- Footer (Optional) -->
        <footer class="py-6 mt-auto border-t border-gray-200 bg-white">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} <span class="font-bold">TickTrack</span> - Helpdesk BP2 Komdigi
                </p>
            </div>
        </footer>
    </div>
</template>
