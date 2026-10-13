<script setup lang="ts">
import { CalendarDays, HandCoins, MapPin, ShieldCheck } from '@lucide/vue';
import { computed } from 'vue';
import {
    CATEGORY_LABELS,
    REPORT_STATUS_LABELS,
    REPORT_TYPE_LABELS,
    formatReportDate,
    type LandingReport,
} from '@/components/landing/fixtures';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import type { User } from '@/types';
import { dashboard, register } from '@/routes';

const props = defineProps<{
    report: LandingReport | null;
    user: User | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const open = computed({
    get: () => props.report !== null,
    set: (value: boolean) => {
        if (!value) {
            emit('close');
        }
    },
});

const typeBadgeClasses = computed(() => {
    if (!props.report) {
        return '';
    }

    return props.report.type === 'lost'
        ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30'
        : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30';
});

const accountHref = computed(() => (props.user ? dashboard() : register()));
const accountActionLabel = computed(() =>
    props.user ? 'Go to your dashboard' : 'Create an account to get started',
);

const privacyNote = computed(() => {
    if (!props.report) {
        return '';
    }

    return props.report.type === 'found'
        ? 'Contact details stay private. Claims are verified before anything is handed over.'
        : 'The reporter will confirm key details with you before arranging a return.';
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-w-md gap-0">
            <DialogHeader>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="typeBadgeClasses"
                    >
                        {{
                            report ? REPORT_TYPE_LABELS[report.type] : 'Report'
                        }}
                    </span>
                    <Badge v-if="report" variant="secondary">
                        {{ REPORT_STATUS_LABELS[report.status] }}
                    </Badge>
                </div>
                <DialogTitle class="text-left text-lg leading-snug">
                    {{ report?.title }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    Report details for {{ report?.title }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="report" class="space-y-4">
                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ report.description }}
                </p>

                <Separator />

                <dl
                    class="grid grid-cols-[auto_1fr] items-start gap-x-3 gap-y-2 text-sm"
                >
                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <MapPin class="size-4" aria-hidden="true" />
                        <span class="sr-only">Location</span>
                    </dt>
                    <dd>{{ report.location }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <CalendarDays class="size-4" aria-hidden="true" />
                        <span class="sr-only">Date</span>
                    </dt>
                    <dd>{{ formatReportDate(report.occurredAt) }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <ShieldCheck class="size-4" aria-hidden="true" />
                        <span class="sr-only">Category</span>
                    </dt>
                    <dd>{{ CATEGORY_LABELS[report.category] }}</dd>
                </dl>

                <div
                    class="flex items-start gap-2.5 rounded-lg bg-muted/60 p-3 text-xs leading-relaxed text-muted-foreground"
                >
                    <HandCoins
                        class="mt-0.5 size-4 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ privacyNote }}</span>
                </div>
            </div>

            <DialogFooter class="mt-2">
                <Button as-child class="w-full">
                    <a :href="accountHref.url">
                        {{ accountActionLabel }}
                    </a>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
