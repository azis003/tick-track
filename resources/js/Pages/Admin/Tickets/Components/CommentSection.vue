<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { 
    MessageSquare, 
    Send, 
    Edit2, 
    Trash2, 
    Paperclip,
    Lock,
    X,
    User
} from 'lucide-vue-next'

/**
 * CommentSection Component
 * Section untuk menampilkan dan menambah komentar pada tiket
 */
const props = defineProps({
    ticket: {
        type: Object,
        required: true
    },
    comments: {
        type: Array,
        default: () => []
    },
    readOnly: {
        type: Boolean,
        default: false
    }
})

const page = usePage()
const auth = page.props.auth

// Form state
const commentForm = useForm({
    content: '',
    is_internal: false,
    attachments: []
})

const editingCommentId = ref(null)
const editForm = useForm({
    content: ''
})

// Methods
const submitComment = () => {
    commentForm.post(`/admin/tickets/${props.ticket.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset()
        }
    })
}

const startEdit = (comment) => {
    editingCommentId.value = comment.id
    editForm.content = comment.content
}

const cancelEdit = () => {
    editingCommentId.value = null
    editForm.reset()
}

const submitEdit = (commentId) => {
    editForm.put(`/admin/comments/${commentId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCommentId.value = null
            editForm.reset()
        }
    })
}

const deleteComment = (commentId) => {
    if (confirm('Hapus komentar ini?')) {
        useForm({}).delete(`/admin/comments/${commentId}`, {
            preserveScroll: true
        })
    }
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const isOwner = (comment) => {
    return comment.user_id == auth?.user?.id
}

const canViewInternal = () => {
    const permissions = auth?.permissions || {}
    return permissions['tickets.view-all'] || permissions['tickets.work']
}

// Check if user can add comment based on ticket status and role
// IMPORTANT: Staff (Helpdesk/Teknisi) cannot add comments directly.
// They can only add notes through workflow actions (e.g., "Return to User" action).
// Only Creator (who created the ticket) can comment, and only when ticket is returned to them (pending_user).
const canComment = computed(() => {
    // If read-only mode, no commenting allowed
    if (props.readOnly) {
        return false
    }
    
    // No one can comment if closed or resolved
    if (props.ticket.status === 'closed' || props.ticket.status === 'resolved') {
        return false
    }
    
    // Staff (technician/helpdesk) CANNOT comment directly
    // They use workflow action panels with notes fields instead
    const isStaff = canViewInternal()
    if (isStaff) return false

    // Creator can only comment when ticket is returned to them (pending_user)
    const isCreator = props.ticket.created_by_id == auth?.user?.id
    if (isCreator && props.ticket.status === 'pending_user') {
        return true
    }

    return false
})

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files)
    commentForm.attachments = files
}
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-slate-50">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-gray-100 rounded-lg">
                    <MessageSquare class="w-4 h-4 text-gray-600" />
                </div>
                <h3 class="font-medium text-gray-900">Komentar</h3>
                <span class="text-sm text-gray-500">({{ comments.length }})</span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Add Comment Form -->
            <form v-if="canComment" @submit.prevent="submitComment" class="space-y-4">
                <!-- Title -->
                <h4 class="text-base font-semibold text-blue-700">Balas</h4>

                <!-- Textarea -->
                <div>
                    <textarea
                        v-model="commentForm.content"
                        rows="5"
                        placeholder="Tulis balasan Anda di sini..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-y"
                    ></textarea>
                    <p v-if="commentForm.errors.content" class="mt-1 text-sm text-red-600">
                        {{ commentForm.errors.content }}
                    </p>
                </div>

                <!-- File Pendukung -->
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">File Pendukung:</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:border-blue-400 hover:text-blue-600 cursor-pointer transition-colors bg-white">
                        <Paperclip class="w-4 h-4" />
                        <span>Pilih File</span>
                        <input
                            type="file"
                            multiple
                            @change="handleFileUpload"
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.zip"
                        />
                    </label>
                    <p class="mt-2 text-xs text-blue-500">
                        Tipe file yang dapat diupload: png, jpg, jpeg, pdf, docx, zip — maksimal 8MB
                    </p>

                    <!-- Attached files preview -->
                    <div v-if="commentForm.attachments.length > 0" class="mt-3 flex flex-wrap gap-2">
                        <div 
                            v-for="(file, index) in commentForm.attachments" 
                            :key="index" 
                            class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700"
                        >
                            <Paperclip class="w-3 h-3" />
                            <span>{{ file.name }}</span>
                            <button 
                                type="button"
                                @click="commentForm.attachments.splice(index, 1)"
                                class="p-0.5 hover:text-red-500 transition-colors"
                            >
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kirim Button -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="commentForm.processing || !commentForm.content.trim()"
                        class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                    >
                        <Send class="w-4 h-4" />
                        <span>{{ commentForm.processing ? 'Mengirim...' : 'Kirim' }}</span>
                    </button>
                </div>
            </form>

            <!-- Comment Disabled Message -->
            <div v-else class="p-4 bg-gray-50 border border-gray-100 rounded-lg flex items-center gap-3">
                <div class="p-2 bg-gray-200 rounded-lg">
                    <Lock class="w-4 h-4 text-gray-500" />
                </div>
                <div>
                    <p v-if="ticket.status === 'closed'" class="text-sm font-medium text-gray-600">
                        Tiket telah ditutup. Komentar tidak lagi diperbolehkan.
                    </p>
                    <p v-else-if="ticket.status === 'resolved'" class="text-sm font-medium text-gray-600">
                        Tiket sudah selesai. Selesaikan konfirmasi atau buka kembali jika masalah masih ada.
                    </p>
                    <p v-else-if="canViewInternal()" class="text-sm font-medium text-gray-600">
                        Gunakan tombol aksi (seperti "Kembalikan ke User") untuk meminta informasi tambahan.
                    </p>
                    <p v-else class="text-sm font-medium text-gray-600">
                        Komentar hanya tersedia saat tiket dikembalikan kepada Anda untuk informasi tambahan.
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ (ticket.status === 'closed' || ticket.status === 'resolved') ? 'History percakapan tetap tersimpan untuk audit.' : 'Riwayat percakapan tetap dapat dilihat di bawah.' }}
                    </p>
                </div>
            </div>

            <!-- Comments List -->
            <div v-if="comments.length > 0" class="space-y-4 pt-4 border-t">
                <div 
                    v-for="comment in comments" 
                    :key="comment.id"
                    :class="[
                        'p-4 rounded-lg',
                        comment.is_internal ? 'bg-orange-50 border border-orange-100' : 'bg-gray-50'
                    ]"
                >
                    <!-- Skip internal comments for users without permission -->
                    <template v-if="!comment.is_internal || canViewInternal()">
                        <!-- Comment Header -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                    <User class="w-4 h-4 text-white" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm text-gray-900">{{ comment.user?.name || 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</p>
                                </div>
                                <span v-if="comment.is_internal" 
                                      class="flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-xs">
                                    <Lock class="w-3 h-3" />
                                    Internal
                                </span>
                            </div>

                            <!-- Actions (owner only & ticket not closed/resolved) -->
                            <div v-if="isOwner(comment) && editingCommentId !== comment.id && canComment" class="flex items-center gap-1">
                                <button 
                                    @click="startEdit(comment)"
                                    class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                    title="Edit"
                                >
                                    <Edit2 class="w-4 h-4" />
                                </button>
                                <button 
                                    @click="deleteComment(comment.id)"
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                    title="Hapus"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Comment Content -->
                        <div v-if="editingCommentId === comment.id" class="mt-2">
                            <textarea
                                v-model="editForm.content"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            ></textarea>
                            <div class="flex justify-end gap-2 mt-2">
                                <button 
                                    @click="cancelEdit"
                                    class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800"
                                >
                                    Batal
                                </button>
                                <button 
                                    @click="submitEdit(comment.id)"
                                    :disabled="editForm.processing"
                                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Simpan
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-700 whitespace-pre-wrap">
                            {{ comment.content }}
                        </div>

                        <!-- Comment Attachments -->
                        <div v-if="comment.attachments && comment.attachments.length > 0" class="mt-3 flex flex-wrap gap-2">
                            <a 
                                v-for="attachment in comment.attachments" 
                                :key="attachment.id"
                                :href="`/admin/attachments/${attachment.id}/download`"
                                class="flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 rounded text-xs text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-colors"
                            >
                                <Paperclip class="w-3 h-3" />
                                <span>{{ attachment.file_name }}</span>
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-8">
                <MessageSquare class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500">Belum ada komentar</p>
            </div>
        </div>
    </div>
</template>
