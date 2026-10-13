<script setup lang="ts">
import {
    CalendarDays,
    MapPin,
    PackageSearch,
    SearchX,
    PackageCheck,
    Tag,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import type { ItemReport } from '@/types';

const props = defineProps<{
    report: ItemReport;
    /**
     * Destination for the shared report detail page. Rendered as a link so the
     * whole card navigates; omit it only for non-navigable previews.
     */
    href?: string;
}>();

const imageFailed = ref(false);

const imageUrl = computed(() =>
    props.report.image_path ? `/storage/${props.report.image_path}` : null,
);

const showImage = computed(() => imageUrl.value !== null && !imageFailed.value);

watch(imageUrl, () => {
    imageFailed.value = false;
});

const typeBadgeClasses: Record<ItemReport['type'], string> = {
    lost: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
    found: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

const typeLabels: Record<ItemReport['type'], string> = {
    lost: 'Lost',
    found: 'Found',
};

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

const reportedAt = computed(() => {
    if (!props.report.event_date) {
        return null;
    }

    const date = new Date(`${props.report.event_date}T00:00:00`);

    return Number.isNaN(date.getTime()) ? null : dateFormatter.format(date);
});
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href ?? undefined"
        class="group relative flex h-full flex-col overflow-hidden rounded-xl border bg-card shadow-xs transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
        :class="href ? 'cursor-pointer hover:border-ring/60' : 'text-left'"
    >
        <div
            class="relative flex aspect-[4/3] items-center justify-center border-b bg-muted/60"
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
                    class="size-8 text-muted-foreground/50"
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
                {{ typeLabels[report.type] }}
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-4">
            <h3 class="line-clamp-2 leading-snug font-medium">
                {{ report.title }}
            </h3>
            <div class="mt-auto space-y-1.5 text-xs text-muted-foreground">
                <p class="flex items-center gap-1.5">
                    <Tag class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">
                        {{ report.category.name }}
                    </span>
                </p>
                <p class="flex items-center gap-1.5">
                    <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">
                        {{ report.location.name }}
                    </span>
                </p>
                <p v-if="reportedAt" class="flex items-center gap-1.5">
                    <CalendarDays
                        class="size-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ reportedAt }}</span>
                </p>
            </div>
        </div>
    </component>
</template>
