<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    MapPin,
    PackageCheck,
    PackageSearch,
    SearchX,
    Tag,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ClaimForm from '@/components/items/ClaimForm.vue';
import DeleteReportDialog from '@/components/items/DeleteReportDialog.vue';
import FoundNotificationForm from '@/components/items/FoundNotificationForm.vue';
import OwnerPanel from '@/components/items/OwnerPanel.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import { index as itemsIndex } from '@/routes/items';
import type {
    ItemDetail,
    ItemDetailPermissions,
    OwnerClaim,
    OwnerFoundNotification,
    OwnerLinks,
    StatusOptions,
} from '@/types';

const props = defineProps<{
    item: ItemDetail;
    statusOptions: StatusOptions;
    permissions: ItemDetailPermissions;
    claims: OwnerClaim[] | null;
    foundNotifications: OwnerFoundNotification[] | null;
    ownerLinks: OwnerLinks | null;
}>();

const imageFailed = ref(false);
const deleteOpen = ref(false);

const imageUrl = computed(() =>
    props.item.image_path ? `/storage/${props.item.image_path}` : null,
);

const showImage = computed(() => imageUrl.value !== null && !imageFailed.value);

const isFound = computed(() => props.item.type === 'found');

const typeLabel = computed(() => (isFound.value ? 'Found' : 'Lost'));

const typeBadgeClasses = computed(() =>
    isFound.value
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30'
        : 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
);

const statusDotClasses: Record<string, string> = {
    active: 'bg-amber-500',
    claimed: 'bg-sky-500',
    resolved: 'bg-emerald-500',
};

const statusLabel = computed(
    () => props.statusOptions[props.item.status] ?? props.item.status,
);

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const formatDate = (value: string | null): string | null => {
    if (!value) {
        return null;
    }

    const date = new Date(`${value}T00:00:00`);

    return Number.isNaN(date.getTime()) ? null : dateFormatter.format(date);
};

const eventDate = computed(() => formatDate(props.item.event_date));
const reportedAt = computed(() => formatDate(props.item.created_at));

const eventDateLabel = computed(() =>
    isFound.value ? 'Tanggal penemuan' : 'Tanggal kehilangan',
);
</script>

<template>
    <Head :title="`${item.title} — NampakGak?`" />

    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b">
            <div
                class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between gap-4 px-4 sm:px-6"
            >
                <Link
                    :href="itemsIndex()"
                    class="inline-flex items-center gap-2 text-sm font-medium outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" />
                    Kembali ke daftar barang
                </Link>

                <Button v-if="$page.props.auth.user" variant="ghost" size="sm" as-child>
                    <Link :href="dashboard()">Dashboard</Link>
                </Button>
                <Button v-else variant="ghost" size="sm" as-child>
                    <Link :href="login()">Log in</Link>
                </Button>
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 sm:py-10">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                <div
                    class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-xl border bg-muted/60"
                >
                    <img
                        v-if="showImage"
                        :src="imageUrl ?? undefined"
                        :alt="item.title"
                        class="absolute inset-0 h-full w-full object-cover"
                        @error="imageFailed = true"
                    />
                    <template v-else>
                        <PackageSearch
                            class="size-10 text-muted-foreground/50"
                            aria-hidden="true"
                        />
                        <span class="sr-only">Tidak ada foto</span>
                    </template>
                </div>

                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                            :class="typeBadgeClasses"
                        >
                            <component
                                :is="isFound ? PackageCheck : SearchX"
                                class="size-3"
                                aria-hidden="true"
                            />
                            {{ typeLabel }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-medium"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="statusDotClasses[item.status] ?? 'bg-muted-foreground'"
                                aria-hidden="true"
                            />
                            {{ statusLabel }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        {{ item.title }}
                    </h1>

                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ item.description }}
                    </p>

                    <dl class="grid gap-2 text-sm">
                        <div class="flex items-center gap-2">
                            <dt class="flex items-center gap-1.5 text-muted-foreground">
                                <Tag class="size-4" aria-hidden="true" />
                                Kategori
                            </dt>
                            <dd class="font-medium">{{ item.category.name }}</dd>
                        </div>
                        <div class="flex items-center gap-2">
                            <dt class="flex items-center gap-1.5 text-muted-foreground">
                                <MapPin class="size-4" aria-hidden="true" />
                                Lokasi
                            </dt>
                            <dd class="font-medium">{{ item.location.name }}</dd>
                        </div>
                        <div v-if="eventDate" class="flex items-center gap-2">
                            <dt class="flex items-center gap-1.5 text-muted-foreground">
                                <CalendarDays class="size-4" aria-hidden="true" />
                                {{ eventDateLabel }}
                            </dt>
                            <dd class="font-medium">{{ eventDate }}</dd>
                        </div>
                    </dl>

                    <p v-if="reportedAt" class="text-xs text-muted-foreground">
                        Dilaporkan pada {{ reportedAt }}
                    </p>
                </div>
            </div>

            <div class="mt-8 space-y-6">
                <!-- Owner: management panel with claims or sightings. -->
                <OwnerPanel
                    v-if="permissions.isOwner"
                    :type="item.type"
                    :claims="claims"
                    :found-notifications="foundNotifications"
                    :links="ownerLinks"
                    @request-delete="deleteOpen = true"
                />

                <!-- Non-owner viewing a lost report: report that it was found. -->
                <FoundNotificationForm
                    v-if="permissions.canReportFound"
                    :action-url="`/items/${item.id}/found-notifications`"
                />

                <!-- Non-owner viewing a found report: claim ownership. -->
                <ClaimForm
                    v-if="permissions.canClaim"
                    :action-url="`/items/${item.id}/claims`"
                />

                <!-- Signed-out visitor prompt. -->
                <div
                    v-if="!permissions.isOwner && !permissions.canReportFound && !permissions.canClaim"
                    class="rounded-xl border border-dashed px-4 py-6 text-center"
                >
                    <p class="text-sm text-muted-foreground">
                        {{
                            $page.props.auth.user
                                ? 'Tidak ada tindakan yang tersedia untuk laporan ini saat ini.'
                                : 'Masuk terlebih dahulu untuk melaporkan penemuan atau mengklaim barang ini.'
                        }}
                    </p>
                    <Button v-if="!$page.props.auth.user" class="mt-3" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                </div>
            </div>
        </main>

        <DeleteReportDialog
            v-if="ownerLinks"
            v-model:open="deleteOpen"
            :report-id="item.id"
            :report-title="item.title"
            :destroy-url="ownerLinks.delete"
            :type="item.type"
        />
    </div>
</template>
