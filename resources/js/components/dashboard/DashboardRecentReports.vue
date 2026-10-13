<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';
import {
    ArrowRight,
    CalendarDays,
    FolderOpen,
    IdCard,
    KeyRound,
    Laptop,
    MapPin,
    Package,
    Shirt,
    Wallet,
} from '@lucide/vue';
import {
    CATEGORY_LABELS,
    REPORT_STATUS_LABELS,
    REPORT_TYPE_LABELS,
    formatReportDate,
    type LandingReport,
} from '@/components/landing/fixtures';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { home } from '@/routes';
import { computed, ref } from 'vue';

defineProps<{
    reports: LandingReport[];
}>();

const selectedReport = ref<LandingReport | null>(null);

const dialogOpen = computed({
    get: () => selectedReport.value !== null,
    set: (value: boolean) => {
        if (!value) {
            selectedReport.value = null;
        }
    },
});

const categoryIcons: Record<string, Component> = {
    documents: IdCard,
    electronics: Laptop,
    'wallets-bags': Wallet,
    keys: KeyRound,
    clothing: Shirt,
    other: Package,
};

const typeBadgeClasses: Record<string, string> = {
    lost: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
    found: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const statusClasses: Record<string, string> = {
    searching:
        'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
    'awaiting-claim':
        'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
    returned:
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const statusDotClasses: Record<string, string> = {
    searching: 'bg-amber-500',
    'awaiting-claim': 'bg-sky-500',
    returned: 'bg-emerald-500',
};
</script>

<template>
    <Card class="gap-4 py-5">
        <CardHeader>
            <CardTitle>My Recent Reports</CardTitle>
            <CardDescription>
                The latest items you've reported lost or found.
            </CardDescription>
            <CardAction>
                <Button
                    variant="ghost"
                    size="sm"
                    as-child
                    title="Full report listing arrives with the reports module"
                >
                    <Link :href="home()">
                        View all reports
                        <ArrowRight aria-hidden="true" />
                    </Link>
                </Button>
            </CardAction>
        </CardHeader>

        <CardContent>
            <div v-if="reports.length" class="divide-y">
                <article
                    v-for="report in reports"
                    :key="report.id"
                    class="flex flex-col gap-3 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:gap-4"
                >
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                        >
                            <component
                                :is="categoryIcons[report.category]"
                                class="size-4"
                                aria-hidden="true"
                            />
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-sm font-medium">
                                    {{ report.title }}
                                </h3>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                    :class="typeBadgeClasses[report.type]"
                                >
                                    {{ REPORT_TYPE_LABELS[report.type] }}
                                </span>
                            </div>
                            <p
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-muted-foreground"
                            >
                                <span class="truncate">
                                    {{ CATEGORY_LABELS[report.category] }}
                                </span>
                                <span class="flex min-w-0 items-center gap-1">
                                    <MapPin
                                        class="size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    <span class="truncate">
                                        {{ report.location }}
                                    </span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <CalendarDays
                                        class="size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    {{ formatReportDate(report.occurredAt) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 sm:justify-end"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                            :class="statusClasses[report.status]"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="statusDotClasses[report.status]"
                                aria-hidden="true"
                            />
                            {{ REPORT_STATUS_LABELS[report.status] }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-7 px-2 text-xs"
                            @click="selectedReport = report"
                        >
                            View details
                            <span class="sr-only">— {{ report.title }}</span>
                        </Button>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="flex flex-col items-center gap-3 rounded-lg border border-dashed px-6 py-10 text-center"
            >
                <span
                    class="flex size-11 items-center justify-center rounded-full bg-muted"
                >
                    <FolderOpen
                        class="size-5 text-muted-foreground"
                        aria-hidden="true"
                    />
                </span>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium">No reports yet</h3>
                    <p class="text-sm text-muted-foreground">
                        Lost something on campus, or found an item that needs
                        its owner? Your reports will appear here.
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>

    <Dialog v-model:open="dialogOpen">
        <DialogContent class="max-w-md gap-0">
            <DialogHeader v-if="selectedReport">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="typeBadgeClasses[selectedReport.type]"
                    >
                        {{ REPORT_TYPE_LABELS[selectedReport.type] }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="statusClasses[selectedReport.status]"
                    >
                        <span
                            class="size-1.5 rounded-full"
                            :class="statusDotClasses[selectedReport.status]"
                            aria-hidden="true"
                        />
                        {{ REPORT_STATUS_LABELS[selectedReport.status] }}
                    </span>
                </div>
                <DialogTitle class="text-left text-lg leading-snug">
                    {{ selectedReport.title }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    Report details for {{ selectedReport.title }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedReport" class="space-y-4">
                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ selectedReport.description }}
                </p>

                <Separator />

                <dl
                    class="grid grid-cols-[auto_1fr] items-start gap-x-3 gap-y-2 text-sm"
                >
                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <MapPin class="size-4" aria-hidden="true" />
                        <span class="sr-only">Location</span>
                    </dt>
                    <dd>{{ selectedReport.location }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <CalendarDays class="size-4" aria-hidden="true" />
                        <span class="sr-only">Date</span>
                    </dt>
                    <dd>
                        {{ formatReportDate(selectedReport.occurredAt) }}
                    </dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <Package class="size-4" aria-hidden="true" />
                        <span class="sr-only">Category</span>
                    </dt>
                    <dd>{{ CATEGORY_LABELS[selectedReport.category] }}</dd>
                </dl>
            </div>
        </DialogContent>
    </Dialog>
</template>
