<script setup>
// app name
const appName = import.meta.env.VITE_APP_NAME

// import Head & usePage dari Inertia
import { Head, usePage } from "@inertiajs/vue3"

// import LayoutAdmin
import LayoutAdmin from "@/Layouts/LayoutAdmin.vue";

// import component PageHeader
import PageHeader from "@/Shared/PageHeader.vue";

// import Dashboard Components
import StatisticsGrid from "./Components/StatisticsGrid.vue";
import TriageQueue from "./Components/TriageQueue.vue";
import ActivityFeed from "./Components/ActivityFeed.vue";
import ChartsSection from "./Components/ChartsSection.vue";

// Mendapatkan data dari props
const {
    stats,
    triageQueue,
    recentActivities,
    chartData
} = usePage().props;
</script>

<template>
    <Head :title="`Dashboard Helpdesk - ${appName}`" />

    <LayoutAdmin>
        <!-- Header -->
        <div class="mb-8">
            <PageHeader
                :showButton="false"
                title="Dashboard Helpdesk"
                description="Selamat datang di TickTrack. Berikut adalah ringkasan aktivitas dan statistik tiket helpdesk."
            />
        </div>

        <!-- Statistics Grid (5 Cards with Trending) -->
        <StatisticsGrid :stats="stats" />

        <!-- Middle Section: Triage Queue + Activity Feed -->
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
            <!-- Triage Queue -->
            <TriageQueue :triageQueue="triageQueue" />

            <!-- Activity Feed -->
            <ActivityFeed :recentActivities="recentActivities" />
        </div>

        <!-- Charts Section (Donut + Line Chart) -->
        <ChartsSection :chartData="chartData" />
    </LayoutAdmin>
</template>
