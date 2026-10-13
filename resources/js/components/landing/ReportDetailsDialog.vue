<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    HandCoins,
    MapPin,
    ShieldCheck,
    Tag,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
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
import { login, register } from '@/routes';
import { show } from '@/routes/items';
import type { User } from '@/types';

const props = defineProps<{
    report: LandingReport | null;
    user: User | null;
    typeOptions: Record<string, string>;
    statusOptions: Record<string, string>;
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

const imageFailed = ref(false);

const imageUrl = computed(() =>
    props.report?.image_path ? `/storage/${props.report.image_path}` : null,
);

const showImage = computed(
    () => imageUrl.value !== null && !imageFailed.value,
);

watch(imageUrl, () => {
    imageFailed.value = false;
});

const typeBadgeClasses = computed(() => {
    if (!props.report) {
        return '';
    }

    return props.report.type === 'lost'
        ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30'
        : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30';
});

const typeLabel = computed(() =>
    props.report
        ? (props.typeOptions[props.report.type] ?? props.report.type)
        : 'Report',
);

const statusLabel = computed(() =>
    props.report
        ? (props.statusOptions[props.report.status] ?? props.report.status)
        : '',
);

const formattedDate = computed(() =>
    formatReportDate(props.report?.event_date ?? null),
);

const detailHref = computed(() =>
    props.report ? show(props.report.id) : null,
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
                        {{ typeLabel }}
                    </span>
                    <Badge v-if="report" variant="secondary">
                        {{ statusLabel }}
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
                <div
                    v-if="showImage"
                    class="overflow-hidden rounded-lg border bg-muted/60"
                >
                    <img
                        :src="imageUrl ?? undefined"
                        :alt="report.title"
                        class="h-48 w-full object-cover"
                        @error="imageFailed = true"
                    />
                </div>

                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ report.description }}
                </p>

                <Separator />

                <dl
                    class="grid grid-cols-[auto_1fr] items-start gap-x-3 gap-y-2 text-sm"
                >
                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <Tag class="size-4" aria-hidden="true" />
                        <span class="sr-only">Category</span>
                    </dt>
                    <dd>{{ report.category.name }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <MapPin class="size-4" aria-hidden="true" />
                        <span class="sr-only">Location</span>
                    </dt>
                    <dd>{{ report.location.name }}</dd>

                    <template v-if="formattedDate">
                        <dt
                            class="flex items-center gap-1.5 text-muted-foreground"
                        >
                            <CalendarDays class="size-4" aria-hidden="true" />
                            <span class="sr-only">Date</span>
                        </dt>
                        <dd>{{ formattedDate }}</dd>
                    </template>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <ShieldCheck class="size-4" aria-hidden="true" />
                        <span class="sr-only">Status</span>
                    </dt>
                    <dd>{{ statusLabel }}</dd>
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

            <DialogFooter class="mt-2 flex-col gap-2 sm:flex-col">
                <template v-if="user && detailHref">
                    <Button as-child class="w-full">
                        <Link :href="detailHref">View full details</Link>
                    </Button>
                </template>
                <template v-else-if="!user">
                    <Button as-child class="w-full">
                        <Link :href="register()">Create an account</Link>
                    </Button>
                    <Button variant="outline" as-child class="w-full">
                        <Link :href="login()">Log in</Link>
                    </Button>
                </template>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
