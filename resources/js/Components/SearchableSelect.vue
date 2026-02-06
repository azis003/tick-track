<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Search, ChevronDown, Check, X, User } from 'lucide-vue-next'

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Pilih data...'
    },
    label: {
        type: String,
        default: 'name'
    },
    value: {
        type: String,
        default: 'id'
    },
    error: String,
    disabled: Boolean
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')
const containerRef = ref(null)

// Map options to consistent format
const formattedOptions = computed(() => {
    return props.options.map(opt => ({
        id: opt[props.value],
        label: opt[props.label],
        sublabel: opt.email || '', // Khusus untuk user
        original: opt
    }))
})

// Filter options based on search
const filteredOptions = computed(() => {
    if (!searchQuery.value) return formattedOptions.value
    
    const query = searchQuery.value.toLowerCase()
    return formattedOptions.value.filter(opt => 
        opt.label.toLowerCase().includes(query) || 
        opt.sublabel.toLowerCase().includes(query)
    )
})

// Selected option label
const selectedLabel = computed(() => {
    const selected = formattedOptions.value.find(opt => opt.id === props.modelValue)
    return selected ? selected.label : ''
})

const selectOption = (option) => {
    emit('update:modelValue', option.id)
    isOpen.value = false
    searchQuery.value = ''
}

const clearSelection = (e) => {
    e.stopPropagation()
    emit('update:modelValue', '')
}

const toggle = () => {
    if (props.disabled) return
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        searchQuery.value = ''
    }
}

// Close on click outside
const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside)
})

</script>

<template>
    <div ref="containerRef" class="relative">
        <!-- Trigger -->
        <div
            @click="toggle"
            class="relative w-full cursor-pointer bg-white border rounded-lg pl-4 pr-10 py-2.5 text-left transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="[
                error ? 'border-red-500' : 'border-gray-300',
                disabled ? 'bg-gray-50 cursor-not-allowed opacity-75' : 'hover:border-gray-400'
            ]"
        >
            <span v-if="selectedLabel" class="block truncate text-sm text-gray-900 font-medium">
                {{ selectedLabel }}
            </span>
            <span v-else class="block truncate text-sm text-gray-400">
                {{ placeholder }}
            </span>
            
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1">
                <X 
                    v-if="modelValue && !disabled" 
                    @click="clearSelection"
                    class="h-4 w-4 text-gray-400 hover:text-gray-600 transition-colors" 
                />
                <ChevronDown class="h-5 w-5 text-gray-400" :class="{ 'rotate-180': isOpen }" />
            </span>
        </div>

        <!-- Dropdown Panel -->
        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-full bg-white shadow-xl max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden flex flex-col border border-gray-100"
            >
                <!-- Search Box inside dropdown -->
                <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                    <div class="relative">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            placeholder="Cari..."
                            @click.stop
                        />
                    </div>
                </div>

                <!-- Options List -->
                <ul class="overflow-auto max-h-56 py-1 scrollbar-thin scrollbar-thumb-gray-200">
                    <template v-if="filteredOptions.length > 0">
                        <li
                            v-for="option in filteredOptions"
                            :key="option.id"
                            @click="selectOption(option)"
                            class="relative cursor-pointer select-none py-2.5 pl-3 pr-9 hover:bg-blue-50 transition-colors group"
                            :class="{ 'bg-blue-50/50': modelValue === option.id }"
                        >
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-blue-100">
                                        <User class="h-4 w-4 text-gray-500 group-hover:text-blue-600" />
                                    </div>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="block truncate text-sm font-semibold" :class="modelValue === option.id ? 'text-blue-700' : 'text-gray-900'">
                                        {{ option.label }}
                                    </span>
                                    <span class="block truncate text-xs text-gray-500">
                                        {{ option.sublabel }}
                                    </span>
                                </div>
                            </div>

                            <span
                                v-if="modelValue === option.id"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600"
                            >
                                <Check class="h-5 w-5" />
                            </span>
                        </li>
                    </template>
                    <li v-else class="py-10 text-center text-gray-500 px-4">
                        <p class="text-sm">Data tidak ditemukan</p>
                    </li>
                </ul>
            </div>
        </transition>
    </div>
</template>
