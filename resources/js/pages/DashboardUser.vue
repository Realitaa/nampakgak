<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import DashboardClaims from '@/components/dashboard/DashboardClaims.vue';
import DashboardQuickActions from '@/components/dashboard/DashboardQuickActions.vue';
import DashboardRecentReports from '@/components/dashboard/DashboardRecentReports.vue';
import DashboardSummaryCards from '@/components/dashboard/DashboardSummaryCards.vue';
import ReportComingSoonDialog from '@/components/dashboard/ReportComingSoonDialog.vue';
import {
    DASHBOARD_CLAIMS,
    DASHBOARD_REPORTS,
    MOCK_USER,
    buildDashboardSummary,
} from '@/components/dashboard/fixtures';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Mock identity until the profile/report modules are wired up — swap this
// for the authenticated user's name when real data is available.
const displayName = MOCK_USER.name;

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 17) {
        return 'Good afternoon';
    }

    return 'Good evening';
});

const summary = buildDashboardSummary();
const reportDialogOpen = ref(false);
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="mx-auto flex h-full w-full max-w-6xl flex-1 flex-col gap-6 p-4 lg:p-6"
    >
        <header
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ greeting }}, {{ displayName }}!
                </h1>
                <p class="mt-1.5 max-w-xl text-sm text-muted-foreground">
                    Keep track of the items you've lost, the belongings you've
                    found, and any claims waiting for your attention.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Button @click="reportDialogOpen = true">
                    <Plus aria-hidden="true" />
                    New report
                </Button>
            </div>
        </header>

        <DashboardSummaryCards :summary="summary" />

        <DashboardQuickActions />

        <DashboardRecentReports :reports="DASHBOARD_REPORTS" />

        <DashboardClaims :claims="DASHBOARD_CLAIMS" />
    </div>

    <ReportComingSoonDialog v-model:open="reportDialogOpen" />
</template>
