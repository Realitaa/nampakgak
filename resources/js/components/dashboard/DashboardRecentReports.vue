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
import type {
    DashboardOptions,
    DashboardReport,
} from '@/components/dashboard/types';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { index as foundIndex } from '@/routes/found-items';
import { index as lostIndex } from '@/routes/lost-items';
import { computed, ref } from 'vue';

defineProps<{
    reports: DashboardReport[];
    statusOptions: DashboardOptions;
}>();

const selectedReport = ref<DashboardReport | null>(null);

const dialogOpen = computed({
    get: () => selectedReport.value !== null,
    set: (value: boolean) => {
        if (!value) {
            selectedReport.value = null;
        }
    },
});

// Category names come from the database, so match on lowercased keywords
// rather than a fixed slug list; anything unrecognised falls back to Package.
const categoryIcons: { keywords: string[]; icon: Component }[] = [
    { keywords: ['document', 'card', 'id'], icon: IdCard },
    { keywords: ['electronic', 'phone', 'laptop', 'gadget'], icon: Laptop },
    { keywords: ['wallet', 'bag', 'purse'], icon: Wallet },
    { keywords: ['key'], icon: KeyRound },
    { keywords: ['cloth', 'jacket', 'shirt', 'hoodie'], icon: Shirt },
];

function categoryIcon(name: string): Component {
    const normalized = name.toLowerCase();

    return (
        categoryIcons.find(({ keywords }) =>
            keywords.some((keyword) => normalized.includes(keyword)),
        )?.icon ?? Package
    );
}

const typeBadgeClasses: Record<string, string> = {
    lost: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
    found: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const statusClasses: Record<string, string> = {
    active: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
    claimed:
        'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
    resolved:
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const statusDotClasses: Record<string, string> = {
    active: 'bg-amber-500',
    claimed: 'bg-sky-500',
    resolved: 'bg-emerald-500',
};

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

function formatDate(value: string | null): string {
    if (!value) {
        return 'Date not specified';
    }

    return dateFormatter.format(new Date(`${value}T00:00:00`));
}
</script>

<template>
    <Card class="gap-4 py-5">
        <CardHeader>
            <CardTitle>My Recent Reports</CardTitle>
            <CardDescription>
                The latest items you've reported lost or found.
            </CardDescription>
            <CardAction>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm">
                            View all reports
                            <ArrowRight aria-hidden="true" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-44">
                        <DropdownMenuItem as-child>
                            <Link class="block w-full cursor-pointer" :href="lostIndex()">
                                Lost items
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link class="block w-full cursor-pointer" :href="foundIndex()">
                                Found items
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
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
                                :is="categoryIcon(report.category.name)"
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
                                    {{ report.type === 'lost' ? 'Lost' : 'Found' }}
                                </span>
                            </div>
                            <p
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-muted-foreground"
                            >
                                <span class="truncate">
                                    {{ report.category.name }}
                                </span>
                                <span class="flex min-w-0 items-center gap-1">
                                    <MapPin
                                        class="size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    <span class="truncate">
                                        {{ report.location.name }}
                                    </span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <CalendarDays
                                        class="size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    {{ formatDate(report.event_date) }}
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
                            {{ statusOptions[report.status] ?? report.status }}
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
                        {{ selectedReport.type === 'lost' ? 'Lost' : 'Found' }}
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
                        {{
                            statusOptions[selectedReport.status] ??
                            selectedReport.status
                        }}
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
                <Separator />

                <dl
                    class="grid grid-cols-[auto_1fr] items-start gap-x-3 gap-y-2 text-sm"
                >
                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <MapPin class="size-4" aria-hidden="true" />
                        <span class="sr-only">Location</span>
                    </dt>
                    <dd>{{ selectedReport.location.name }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <CalendarDays class="size-4" aria-hidden="true" />
                        <span class="sr-only">Date</span>
                    </dt>
                    <dd>{{ formatDate(selectedReport.event_date) }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <Package class="size-4" aria-hidden="true" />
                        <span class="sr-only">Category</span>
                    </dt>
                    <dd>{{ selectedReport.category.name }}</dd>
                </dl>
            </div>
        </DialogContent>
    </Dialog>
</template>
