<script setup>
import LayoutAuth from '@/Layouts/LayoutAuth.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Mail, Lock, Eye, EyeOff, LogIn, MoveLeft } from 'lucide-vue-next'
import { ref } from 'vue'

const showPassword = ref(false)

const form = useForm({
    email: '',
    password: '',
    remember: false
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <LayoutAuth>
        <Head title="Login - TickTrack" />

        <div class="flex flex-col h-full p-8 lg:p-12">
            <!-- Back to Home Button -->
            <div class="mb-12">
                <Link
                    href="/"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors group"
                >
                    <MoveLeft class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" />
                    Kembali ke Home
                </Link>
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-[400px] w-full mx-auto">
                <!-- Header -->
                <div class="text-center mb-10">
                    <h1 class="text-[32px] font-bold text-slate-900 mb-3">Selamat Datang</h1>
                    <p class="text-slate-500 text-sm">
                        Masuk ke akun Anda untuk mengakses <span class="font-medium">Aplikasi TickTrack</span>
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <Mail :size="20" />
                            </div>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 text-slate-900"
                                placeholder="contoh@gmail.com"
                                :class="{ 'border-red-500 ring-red-500/10': form.errors.email }"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-slate-700">Password</label>
                            <Link href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Lupa password?
                            </Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <Lock :size="20" />
                            </div>
                            <input 
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full pl-12 pr-12 py-3 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 text-slate-900"
                                placeholder="Masukkan password"
                                :class="{ 'border-red-500 ring-red-500/10': form.errors.password }"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer group select-none">
                            <input v-model="form.remember" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer" />
                            <span class="ml-3 text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full h-12 bg-[#1d4ed8] hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-[0.98] disabled:opacity-70 flex items-center justify-center space-x-2"
                    >
                        <LogIn v-if="!form.processing" class="w-5 h-5 rotate-180" />
                        <span>{{ form.processing ? 'MEMPROSES...' : 'Masuk' }}</span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-12 text-center">
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium">
                        © {{ new Date().getFullYear() }} Aplikasi TickTrack. Hak Cipta Dilindungi.
                    </p>
                </div>
            </div>
        </div>
    </LayoutAuth>
</template>
