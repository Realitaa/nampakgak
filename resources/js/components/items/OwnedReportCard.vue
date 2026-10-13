<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    MapPin,
    MoreHorizontal,
    PackageSearch,
    Pencil,
    Tag,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { ItemStatus, ItemType, OwnedItemReport, StatusOptions } from '@/types';
import DeleteReportDialog from './DeleteReportDialog.vue';

const props = defineProps<{
    report: OwnedItemReport;
    statusOptions: StatusOptions;
    /**
     * Route builders for the report kind being listed. Passed in so one card
     * serves both the lost and found listings without importing either route
     * set, which also keeps the type/route pairing unambiguous.
     */
    editUrl: string;
    statusUrl: string;
    destroyUrl: string;
    /** Shared detail page for this report. */
    viewUrl: string;
}>();

const imageFailed = ref(false);
const deleteOpen = ref(false);

const imageUrl = computed(() =>
    props.report.image_path ? `/storage/${props.report.image_path}` : null,
);

const showImage = computed(() => imageUrl.value !== null && !imageFailed.value);

const statusBadgeClasses: Record<ItemStatus, string> = {
    active: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
    claimed: 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
    resolved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
};

// Mirrors ItemStatus::allowedTransitions() on the server, which remains the
// authority; the menu only hides transitions the server would reject.
const allowedTransitions: Record<ItemStatus, ItemStatus[]> = {
    active: ['claimed', 'resolved'],
    claimed: ['active', 'resolved'],
    resolved: ['active'],
};

const nextStatuses = computed(() =>
    allowedTransitions[props.report.status].map((value) => ({
        value,
        label: props.statusOptions[value] ?? value,
    })),
);

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const formattedDate = computed(() => {
    if (!props.report.event_date) {
        return null;
    }

    const date = new Date(`${props.report.event_date}T00:00:00`);

    return Number.isNaN(date.getTime()) ? null : dateFormatter.format(date);
});

function changeStatus(value: ItemStatus): void {
    router.patch(props.statusUrl, { status: value }, { preserveScroll: true });
}
</script>

<template>
    <div
        class="group relative flex h-full flex-col overflow-hidden rounded-xl border bg-card shadow-xs transition-colors"
    >
        <div class="relative flex aspect-[4/3] items-center justify-center border-b bg-muted/60">
            <img
                v-if="showImage"
                :src="imageUrl ?? undefined"
                :alt="report.title"
                loading="lazy"
                class="absolute inset-0 h-full w-full object-cover"
                @error="imageFailed = true"
            />
            <template v-else>
                <PackageSearch class="size-8 text-muted-foreground/50" aria-hidden="true" />
                <span class="sr-only">Tidak ada foto</span>
            </template>

            <span
                class="absolute top-3 left-3 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="statusBadgeClasses[report.status]"
            >
                {{ statusOptions[report.status] ?? report.status }}
            </span>

            <DropdownMenu>
                <DropdownMenuTrigger
                    class="absolute top-2 right-2 inline-flex size-8 items-center justify-center rounded-md bg-background/80 text-foreground shadow-xs backdrop-blur transition-colors hover:bg-background focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    :aria-label="`Aksi untuk ${report.title}`"
                >
                    <MoreHorizontal class="size-4" aria-hidden="true" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-48">
                    <DropdownMenuLabel>Status laporan</DropdownMenuLabel>
                    <DropdownMenuItem
                        v-for="next in nextStatuses"
                        :key="next.value"
                        @select="changeStatus(next.value)"
                    >
                        Tandai {{ next.label }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child>
                        <Link :href="editUrl">Ubah laporan</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem variant="destructive" @select="deleteOpen = true">
                        Hapus laporan
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-4">
            <h3 class="line-clamp-2 leading-snug font-medium">
                <Link :href="viewUrl" class="outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    {{ report.title }}
                </Link>
            </h3>

            <div class="mt-auto space-y-1.5 text-xs text-muted-foreground">
                <p class="flex items-center gap-1.5">
                    <Tag class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ report.category.name }}</span>
                </p>
                <p class="flex items-center gap-1.5">
                    <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ report.location.name }}</span>
                </p>
                <p v-if="formattedDate" class="flex items-center gap-1.5">
                    <CalendarDays class="size-3.5 shrink-0" aria-hidden="true" />
                    <span>{{ formattedDate }}</span>
                </p>
            </div>

            <div class="mt-2 flex items-center gap-2 border-t pt-3">
                <Link
                    :href="editUrl"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-md border bg-background px-3 py-1.5 text-xs font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    <Pencil class="size-3.5" aria-hidden="true" />
                    Ubah
                </Link>
                <button
                    type="button"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-md border border-destructive/30 bg-background px-3 py-1.5 text-xs font-medium text-destructive shadow-xs transition-colors hover:bg-destructive/10 focus-visible:ring-[3px] focus-visible:ring-destructive/20 focus-visible:outline-none"
                    @click="deleteOpen = true"
                >
                    <Trash2 class="size-3.5" aria-hidden="true" />
                    Hapus
                </button>
            </div>
        </div>

        <DeleteReportDialog
            v-model:open="deleteOpen"
            :report-id="report.id"
            :report-title="report.title"
            :destroy-url="destroyUrl"
            :type="report.type as ItemType"
        />
    </div>
</template>
