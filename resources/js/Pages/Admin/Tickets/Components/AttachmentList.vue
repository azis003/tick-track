<script setup>
import { File, Image, FileText, Download } from 'lucide-vue-next'

/**
 * AttachmentList Component
 * Menampilkan daftar file attachment dari tiket
 */
defineProps({
    attachments: {
        type: Array,
        required: true,
        default: () => []
    }
})

const formatSize = (bytes) => {
    if (!bytes) return '-'
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (type) => {
    if (type && type.startsWith('image/')) return Image
    if (type && type.includes('pdf')) return FileText
    return File
}

const isImage = (type) => {
    return type && type.startsWith('image/')
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <div class="space-y-3">
        <!-- Empty state -->
        <div v-if="!attachments || attachments.length === 0" class="text-center py-6 text-gray-500">
            <File class="mx-auto h-8 w-8 text-gray-400 mb-2" />
            <p class="text-sm">Tidak ada lampiran</p>
        </div>

        <!-- Attachment list -->
        <div v-else class="grid gap-3 sm:grid-cols-2">
            <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
                <!-- Preview or icon -->
                <div class="flex-shrink-0">
                    <img
                        v-if="isImage(attachment.file_type)"
                        :src="`/storage/${attachment.file_path}`"
                        :alt="attachment.file_name"
                        class="h-12 w-12 object-cover rounded"
                    />
                    <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                        <component
                            :is="getFileIcon(attachment.file_type)"
                            class="h-6 w-6 text-gray-500"
                        />
                    </div>
                </div>

                <!-- File info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ attachment.file_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatSize(attachment.file_size) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Oleh {{ attachment.user?.name || 'Unknown' }} • {{ formatDate(attachment.created_at) }}
                    </p>
                </div>

                <!-- Download button -->
                <a
                    :href="`/attachments/${attachment.id}/download`"
                    class="flex-shrink-0 p-2 text-gray-400 hover:text-blue-600 transition-colors"
                    title="Download"
                >
                    <Download class="h-5 w-5" />
                </a>
            </div>
        </div>
    </div>
</template>
