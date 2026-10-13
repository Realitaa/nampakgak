<script setup lang="ts">
import type { Component } from 'vue';
import {
    CalendarDays,
    IdCard,
    KeyRound,
    Laptop,
    MapPin,
    Package,
    PackageCheck,
    SearchX,
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
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    report: LandingReport;
}>();

const emit = defineEmits<{
    (e: 'open', report: LandingReport): void;
}>();

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

const statusDotClasses: Record<string, string> = {
    searching: 'bg-amber-500',
    'awaiting-claim': 'bg-sky-500',
    returned: 'bg-emerald-500',
};
</script>

<template>
    <Card
        class="group h-full cursor-pointer gap-0 overflow-hidden py-0 transition-colors hover:border-ring/60"
        @click="emit('open', report)"
    >
        <div
            class="relative flex h-36 items-center justify-center border-b bg-muted/60"
        >
            <component
                :is="categoryIcons[report.category]"
                class="size-10 text-muted-foreground/50"
                aria-hidden="true"
            />
            <span
                class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="typeBadgeClasses[report.type]"
            >
                <component
                    :is="report.type === 'lost' ? SearchX : PackageCheck"
                    class="size-3"
                    aria-hidden="true"
                />
                {{ REPORT_TYPE_LABELS[report.type] }}
            </span>
        </div>

        <CardContent class="flex flex-1 flex-col gap-3 p-4">
            <div class="space-y-1">
                <h3 class="line-clamp-2 leading-snug font-medium">
                    {{ report.title }}
                </h3>
                <p class="text-xs text-muted-foreground">
                    {{ CATEGORY_LABELS[report.category] }}
                </p>
            </div>

            <div class="mt-auto space-y-1.5 text-xs text-muted-foreground">
                <p class="flex items-center gap-1.5">
                    <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ report.location }}</span>
                </p>
                <p class="flex items-center gap-1.5">
                    <CalendarDays
                        class="size-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ formatReportDate(report.occurredAt) }}</span>
                </p>
            </div>

            <div class="flex items-center justify-between gap-2 border-t pt-3">
                <span
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-foreground/80"
                >
                    <span
                        class="size-1.5 rounded-full"
                        :class="statusDotClasses[report.status]"
                        aria-hidden="true"
                    />
                    {{ REPORT_STATUS_LABELS[report.status] }}
                </span>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-7 px-2 text-xs"
                    @click.stop="emit('open', report)"
                >
                    View details
                    <span class="sr-only">— {{ report.title }}</span>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
