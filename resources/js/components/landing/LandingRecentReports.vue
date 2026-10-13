<script setup lang="ts">
import { ArrowRight, SearchX } from '@lucide/vue';
import type {
    LandingReport,
    ReportTypeFilter,
} from '@/components/landing/fixtures';
import LandingReportCard from '@/components/landing/LandingReportCard.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    reports: LandingReport[];
    totalCount: number;
    activeType: ReportTypeFilter;
    hasFilters: boolean;
    typeOptions: Record<string, string>;
    statusOptions: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'set-type', type: ReportTypeFilter): void;
    (e: 'show-all'): void;
    (e: 'open', report: LandingReport): void;
}>();

const typeFilters: { value: ReportTypeFilter; label: string }[] = [
    { value: 'all', label: 'All reports' },
    { value: 'lost', label: 'Lost items' },
    { value: 'found', label: 'Found items' },
];
</script>

<template>
    <section
        id="recent-reports"
        class="mx-auto w-full max-w-6xl scroll-mt-24 px-4 pb-14 sm:px-6 sm:pb-20"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="max-w-xl">
                <h2 class="text-2xl font-semibold tracking-tight">
                    Recently Reported Items
                </h2>
                <p class="mt-1.5 text-sm text-muted-foreground">
                    Fresh lost and found reports from around campus. Check back
                    often — something spotted today may be yours.
                </p>
            </div>

            <div
                class="inline-flex w-fit gap-1 rounded-lg bg-muted p-1"
                role="group"
                aria-label="Filter reports by type"
            >
                <button
                    v-for="filter in typeFilters"
                    :key="filter.value"
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    :class="
                        activeType === filter.value
                            ? 'bg-background font-medium text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    :aria-pressed="activeType === filter.value"
                    @click="emit('set-type', filter.value)"
                >
                    {{ filter.label }}
                </button>
            </div>
        </div>

        <div
            v-if="reports.length"
            class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <LandingReportCard
                v-for="report in reports"
                :key="report.id"
                :report="report"
                :type-options="typeOptions"
                :status-options="statusOptions"
                @open="emit('open', $event)"
            />
        </div>

        <div
            v-else
            class="mt-6 flex flex-col items-center gap-3 rounded-xl border border-dashed px-6 py-12 text-center"
        >
            <span
                class="flex size-12 items-center justify-center rounded-full bg-muted"
            >
                <SearchX
                    class="size-5 text-muted-foreground"
                    aria-hidden="true"
                />
            </span>
            <div class="space-y-1">
                <h3 class="font-medium">No matching reports</h3>
                <p class="mx-auto max-w-sm text-sm text-muted-foreground">
                    Try a different keyword or clear the filters to browse every
                    report.
                </p>
            </div>
            <Button
                v-if="hasFilters"
                variant="outline"
                size="sm"
                @click="emit('show-all')"
            >
                Clear filters
            </Button>
        </div>

        <div
            class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row"
        >
            <p class="text-xs text-muted-foreground">
                Showing {{ reports.length }} of {{ totalCount }} reports
            </p>
            <Button
                v-if="hasFilters"
                variant="ghost"
                size="sm"
                @click="emit('show-all')"
            >
                Show all reports
                <ArrowRight aria-hidden="true" />
            </Button>
        </div>
    </section>
</template>
