<script setup lang="ts">
import { CalendarDays, MapPin, PackageSearch, PackageCheck, SearchX } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
    formatReportDate,
    type LandingReport,
} from '@/components/landing/fixtures';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps<{
    report: LandingReport;
    typeOptions: Record<string, string>;
    statusOptions: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'open', report: LandingReport): void;
}>();

const imageFailed = ref(false);

const imageUrl = computed(() =>
    props.report.image_path ? `/storage/${props.report.image_path}` : null,
);

const showImage = computed(
    () => imageUrl.value !== null && !imageFailed.value,
);

watch(imageUrl, () => {
    imageFailed.value = false;
});

const typeBadgeClasses: Record<string, string> = {
    lost: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
    found: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const statusDotClasses: Record<string, string> = {
    active: 'bg-amber-500',
    claimed: 'bg-sky-500',
    resolved: 'bg-emerald-500',
};

const typeLabel = computed(
    () => props.typeOptions[props.report.type] ?? props.report.type,
);

const statusLabel = computed(
    () => props.statusOptions[props.report.status] ?? props.report.status,
);

const formattedDate = computed(() => formatReportDate(props.report.event_date));
</script>

<template>
    <Card
        class="group h-full cursor-pointer gap-0 overflow-hidden py-0 transition-colors hover:border-ring/60"
        @click="emit('open', report)"
    >
        <div
            class="relative flex h-36 items-center justify-center border-b bg-muted/60"
        >
            <img
                v-if="showImage"
                :src="imageUrl ?? undefined"
                :alt="report.title"
                loading="lazy"
                class="absolute inset-0 h-full w-full object-cover"
                @error="imageFailed = true"
            />
            <template v-else>
                <PackageSearch
                    class="size-10 text-muted-foreground/50"
                    aria-hidden="true"
                />
                <span class="sr-only">No photo available</span>
            </template>

            <span
                class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="typeBadgeClasses[report.type]"
            >
                <component
                    :is="report.type === 'lost' ? SearchX : PackageCheck"
                    class="size-3"
                    aria-hidden="true"
                />
                {{ typeLabel }}
            </span>
        </div>

        <CardContent class="flex flex-1 flex-col gap-3 p-4">
            <div class="space-y-1">
                <h3 class="line-clamp-2 leading-snug font-medium">
                    {{ report.title }}
                </h3>
                <p class="text-xs text-muted-foreground">
                    {{ report.category.name }}
                </p>
            </div>

            <div class="mt-auto space-y-1.5 text-xs text-muted-foreground">
                <p class="flex items-center gap-1.5">
                    <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ report.location.name }}</span>
                </p>
                <p v-if="formattedDate" class="flex items-center gap-1.5">
                    <CalendarDays
                        class="size-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ formattedDate }}</span>
                </p>
            </div>

            <div class="flex items-center justify-between gap-2 border-t pt-3">
                <Badge variant="secondary" class="gap-1.5">
                    <span
                        class="size-1.5 rounded-full"
                        :class="statusDotClasses[report.status]"
                        aria-hidden="true"
                    />
                    {{ statusLabel }}
                </Badge>
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
