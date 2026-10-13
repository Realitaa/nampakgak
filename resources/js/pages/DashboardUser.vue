<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Plus, SearchX, Hand } from '@lucide/vue';
import { computed } from 'vue';
import DashboardClaims from '@/components/dashboard/DashboardClaims.vue';
import DashboardQuickActions from '@/components/dashboard/DashboardQuickActions.vue';
import DashboardRecentReports from '@/components/dashboard/DashboardRecentReports.vue';
import DashboardSummaryCards from '@/components/dashboard/DashboardSummaryCards.vue';
import type {
    DashboardClaim,
    DashboardOptions,
    DashboardReport,
    DashboardSummary,
} from '@/components/dashboard/types';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import { create as foundCreate } from '@/routes/found-items';
import { create as lostCreate } from '@/routes/lost-items';

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

defineProps<{
    summary: DashboardSummary;
    reports: DashboardReport[];
    claims: DashboardClaim[];
    statusOptions: DashboardOptions;
    claimStatusOptions: DashboardOptions;
}>();

const page = usePage();
const displayName = computed(() => page.props.auth.user.name);

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
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button>
                            <Plus aria-hidden="true" />
                            New report
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem as-child>
                            <Link
                                class="flex w-full cursor-pointer items-center gap-2"
                                :href="lostCreate()"
                            >
                                <SearchX class="size-4" aria-hidden="true" />
                                Lost item
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link
                                class="flex w-full cursor-pointer items-center gap-2"
                                :href="foundCreate()"
                            >
                                <Hand class="size-4" aria-hidden="true" />
                                Found item
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>

        <DashboardSummaryCards :summary="summary" />

        <DashboardQuickActions />

        <DashboardRecentReports
            :reports="reports"
            :status-options="statusOptions"
        />

        <DashboardClaims
            :claims="claims"
            :claim-status-options="claimStatusOptions"
        />
    </div>
</template>
