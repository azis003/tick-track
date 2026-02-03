<script setup>
// Import icons dari lucide-vue-next
import { PieChart, TrendingUp } from "lucide-vue-next";

// Import Chart.js components
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

// Import Vue Chart.js components
import { Doughnut, Line } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Props
const props = defineProps({
    chartData: {
        type: Object,
        required: true,
    },
});

// Data untuk donut chart - Distribusi Kategori
const donutChartData = {
    labels: props.chartData?.categoryDistribution?.labels || ['Tidak ada data'],
    datasets: [
        {
            data: props.chartData?.categoryDistribution?.data || [1],
            backgroundColor: props.chartData?.categoryDistribution?.colors || ['rgba(107, 114, 128, 0.8)'],
            borderColor: props.chartData?.categoryDistribution?.colors?.map(color => color.replace('0.8', '1')) || ['rgb(107, 114, 128)'],
            borderWidth: 2,
            hoverOffset: 15,
        },
    ],
};

const donutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right',
            labels: {
                font: {
                    size: 12,
                    family: "'Inter', sans-serif"
                },
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle',
            }
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            boxPadding: 6,
            callbacks: {
                label: function (context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                    return `${label}: ${value} tiket (${percentage}%)`;
                }
            }
        }
    },
};

// Data untuk line chart - Trend 7 Hari
const lineChartData = {
    labels: props.chartData?.dailyTrend?.days || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    datasets: [
        {
            label: 'New',
            data: props.chartData?.dailyTrend?.new || [0, 0, 0, 0, 0, 0, 0],
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(99, 102, 241)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        },
        {
            label: 'Resolved',
            data: props.chartData?.dailyTrend?.resolved || [0, 0, 0, 0, 0, 0, 0],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(34, 197, 94)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        }
    ],
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: {
                    size: 13,
                    family: "'Inter', sans-serif",
                    weight: '500',
                },
                padding: 20,
                usePointStyle: true,
            }
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            boxPadding: 6,
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function (context) {
                    return `${context.dataset.label}: ${context.raw} tiket`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(243, 244, 246, 1)',
            },
            ticks: {
                font: {
                    size: 11,
                },
                color: '#6b7280',
                callback: function (value) {
                    return Number.isInteger(value) ? value : '';
                }
            }
        },
        x: {
            grid: {
                color: 'rgba(243, 244, 246, 1)',
            },
            ticks: {
                font: {
                    size: 12,
                },
                color: '#6b7280',
            }
        }
    },
    interaction: {
        intersect: false,
        mode: 'index',
    },
};

// Hitung total tiket untuk donut chart
const totalTickets = donutChartData.datasets[0].data.reduce((a, b) => a + b, 0);

// Hitung kategori dominan
const maxIndex = donutChartData.datasets[0].data.indexOf(Math.max(...donutChartData.datasets[0].data));
const dominantCategory = donutChartData.labels[maxIndex] || 'N/A';
</script>

<template>
    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
        <!-- Chart 1: Donut Chart - Distribusi Kategori -->
        <div class="p-6 bg-white shadow-sm rounded-xl">
            <div class="flex items-center justify-between pb-3 mb-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Distribusi Kategori
                    </h3>
                    <p class="text-sm text-gray-600">
                        Persentase tiket per kategori
                    </p>
                </div>
                <PieChart class="w-6 h-6 text-purple-600" />
            </div>

            <div class="h-64">
                <Doughnut :data="donutChartData" :options="donutChartOptions" />
            </div>

            <div class="pt-4 mt-6 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Total <span class="font-semibold text-gray-900">{{ totalTickets }} tiket</span>
                    </div>
                    <div v-if="totalTickets > 0" class="text-sm font-medium text-purple-600">
                        {{ dominantCategory }} dominan
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Line Chart - Trend 7 Hari -->
        <div class="p-6 bg-white shadow-sm rounded-xl">
            <div class="flex items-center justify-between pb-3 mb-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Trend Tiket (7 Hari)
                    </h3>
                    <p class="text-sm text-gray-600">
                        New vs Resolved
                    </p>
                </div>
                <TrendingUp class="w-6 h-6 text-blue-600" />
            </div>

            <div class="h-64">
                <Line :data="lineChartData" :options="lineChartOptions" />
            </div>

            <div class="pt-4 mt-6 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-indigo-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">New</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 mr-2 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Resolved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
