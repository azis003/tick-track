<script setup>
// import icons dari lucide-vue-next
import { 
    FileText, 
    LoaderCircle, 
    Clock, 
    CheckCircle, 
    FolderClosed,
    TrendingUp,
    TrendingDown,
    Minus
} from 'lucide-vue-next'

// props
defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

// Fungsi untuk mendapatkan icon berdasarkan status
const getIcon = (type) => {
    const icons = {
        new: FileText,
        inProgress: LoaderCircle,
        pending: Clock,
        resolved: CheckCircle,
        closed: FolderClosed,
    };
    return icons[type] || FileText;
};

// Fungsi untuk mendapatkan warna berdasarkan status
const getColorClasses = (type) => {
    const colors = {
        new: {
            bg: 'from-indigo-500 to-indigo-600',
            text: 'text-indigo-600',
            badge: 'bg-indigo-100',
            hover: 'bg-indigo-100',
        },
        inProgress: {
            bg: 'from-blue-500 to-blue-600',
            text: 'text-blue-600',
            badge: 'bg-blue-100',
            hover: 'bg-blue-100',
        },
        pending: {
            bg: 'from-orange-500 to-orange-600',
            text: 'text-orange-600',
            badge: 'bg-orange-100',
            hover: 'bg-orange-100',
        },
        resolved: {
            bg: 'from-green-500 to-green-600',
            text: 'text-green-600',
            badge: 'bg-green-100',
            hover: 'bg-green-100',
        },
        closed: {
            bg: 'from-gray-500 to-gray-600',
            text: 'text-gray-600',
            badge: 'bg-gray-100',
            hover: 'bg-gray-100',
        },
    };
    return colors[type] || colors.new;
};

// Fungsi untuk mendapatkan label status
const getStatusLabel = (type) => {
    const labels = {
        new: 'New',
        inProgress: 'Proses',
        pending: 'Pending',
        resolved: 'Solved',
        closed: 'Closed',
    };
    return labels[type] || type;
};

// Fungsi untuk mendapatkan emoji trending
const getTrendingIcon = (direction) => {
    if (direction === 'up') return TrendingUp;
    if (direction === 'down') return TrendingDown;
    return Minus;
};

// Fungsi untuk mendapatkan warna trending
const getTrendingColor = (direction) => {
    if (direction === 'up') return 'text-green-600';
    if (direction === 'down') return 'text-red-600';
    return 'text-gray-500';
};
</script>

<template>
    <div class="grid grid-cols-1 gap-5 mb-8 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Looping Through Stats -->
        <div
            v-for="(stat, key) in stats"
            :key="key"
            class="relative overflow-hidden transition-all duration-300 bg-white shadow-sm group rounded-2xl hover:shadow-md"
        >
            <!-- Animated Background Circle -->
            <div
                :class="`absolute -right-6 -top-6 w-24 h-24 ${getColorClasses(key).hover} rounded-full opacity-0 group-hover:opacity-30 transition-opacity duration-500`"
            ></div>

            <div class="relative p-6">
                <!-- Icon & Count -->
                <div class="flex items-center mb-4">
                    <div
                        :class="`flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br ${getColorClasses(key).bg} flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300`"
                    >
                        <component
                            :is="getIcon(key)"
                            :class="`w-6 h-6 text-white ${key === 'inProgress' ? 'animate-spin' : 'group-hover:rotate-12'} transition-transform duration-300`"
                        />
                    </div>
                    <div class="ml-4">
                        <p class="text-3xl font-bold text-gray-900">
                            {{ stat.count }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ getStatusLabel(key) }}
                        </p>
                    </div>
                </div>

                <!-- Trending Indicator -->
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                    <div
                        v-if="stat.trend.direction !== 'neutral'"
                        :class="`flex items-center text-sm ${getTrendingColor(stat.trend.direction)}`"
                    >
                        <component
                            :is="getTrendingIcon(stat.trend.direction)"
                            class="w-4 h-4 mr-1"
                        />
                        <span class="font-medium">
                            {{ stat.trend.value }}%
                        </span>
                    </div>
                    <div v-else class="flex items-center text-sm text-gray-400">
                        <Minus class="w-4 h-4 mr-1" />
                        <span>Stabil</span>
                    </div>

                    <span class="text-xs text-gray-500">
                        vs minggu lalu
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
