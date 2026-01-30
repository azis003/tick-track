<script setup>
// import inertia router
import { router } from '@inertiajs/vue3'

// import icons
import { Trash2 } from 'lucide-vue-next'

// import Sweet Alert
import Swal from 'sweetalert2'

// props
const props = defineProps({
    URL: {
        type: String,
        required: true,
    },
    id: {
        type: [Number, String],
        required: true,
    },
});

// method destroy
const destroy = () => {
    // show sweet alert
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: "Data yang telah dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            // delete
            router.delete(`${props.URL}/${props.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // show success alert
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Data berhasil dihapus.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    })
                },
            })
        }
    })
};
</script>

<template>
    <button
        @click="destroy"
        class="inline-flex items-center p-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-lg transition-all duration-200 shadow-sm"
        title="Hapus"
    >
        <Trash2 class="w-4 h-4" />
    </button>
</template>
