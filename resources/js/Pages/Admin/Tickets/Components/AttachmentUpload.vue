<script setup>
import { ref } from 'vue'
import { Upload, X, File, Image, FileText } from 'lucide-vue-next'

/**
 * AttachmentUpload Component
 * Drag & drop file upload dengan preview
 */
const props = defineProps({
    maxFiles: {
        type: Number,
        default: 5
    },
    maxSize: {
        type: Number,
        default: 5 // MB
    },
    accept: {
        type: String,
        default: '.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx'
    }
})

const emit = defineEmits(['update:files'])

const files = ref([])
const isDragging = ref(false)
const error = ref('')

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (type) => {
    if (type.startsWith('image/')) return Image
    if (type.includes('pdf')) return FileText
    return File
}

const validateFile = (file) => {
    // Check size
    if (file.size > props.maxSize * 1024 * 1024) {
        return `File "${file.name}" melebihi ukuran maksimal ${props.maxSize}MB`
    }

    // Check extension
    const ext = '.' + file.name.split('.').pop().toLowerCase()
    const allowedExts = props.accept.split(',').map(e => e.trim().toLowerCase())
    if (!allowedExts.includes(ext)) {
        return `Format file "${file.name}" tidak didukung`
    }

    return null
}

const addFiles = (newFiles) => {
    error.value = ''

    // Check max files
    if (files.value.length + newFiles.length > props.maxFiles) {
        error.value = `Maksimal ${props.maxFiles} file`
        return
    }

    for (const file of newFiles) {
        const validationError = validateFile(file)
        if (validationError) {
            error.value = validationError
            return
        }

        // Create preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader()
            reader.onload = (e) => {
                file.preview = e.target.result
            }
            reader.readAsDataURL(file)
        }

        files.value.push(file)
    }

    emit('update:files', files.value)
}

const removeFile = (index) => {
    files.value.splice(index, 1)
    emit('update:files', files.value)
}

const handleDrop = (e) => {
    isDragging.value = false
    const droppedFiles = Array.from(e.dataTransfer.files)
    addFiles(droppedFiles)
}

const handleFileInput = (e) => {
    const selectedFiles = Array.from(e.target.files)
    addFiles(selectedFiles)
    e.target.value = '' // Reset input
}
</script>

<template>
    <div class="space-y-3">
        <!-- Drop zone -->
        <div
            class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
            :class="[
                isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <Upload class="mx-auto h-10 w-10 text-gray-400 mb-3" />
            <p class="text-sm text-gray-600 mb-2">
                Drag & drop file di sini atau
                <label class="text-blue-600 hover:text-blue-700 cursor-pointer font-medium">
                    pilih file
                    <input
                        type="file"
                        :accept="accept"
                        multiple
                        class="hidden"
                        @change="handleFileInput"
                    />
                </label>
            </p>
            <p class="text-xs text-gray-500">
                Maks {{ maxFiles }} file, {{ maxSize }}MB per file
            </p>
        </div>

        <!-- Error message -->
        <p v-if="error" class="text-sm text-red-600">
            {{ error }}
        </p>

        <!-- File list -->
        <div v-if="files.length > 0" class="space-y-2">
            <div
                v-for="(file, index) in files"
                :key="index"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Preview or icon -->
                    <div class="flex-shrink-0">
                        <img
                            v-if="file.preview"
                            :src="file.preview"
                            class="h-10 w-10 object-cover rounded"
                        />
                        <component
                            v-else
                            :is="getFileIcon(file.type)"
                            class="h-10 w-10 text-gray-400"
                        />
                    </div>

                    <!-- File info -->
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatSize(file.size) }}
                        </p>
                    </div>
                </div>

                <!-- Remove button -->
                <button
                    type="button"
                    class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                    @click="removeFile(index)"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</template>
