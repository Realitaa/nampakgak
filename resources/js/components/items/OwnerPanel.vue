<script setup lang="ts">
import { PackageSearch, Pencil, Tag, Trash2 } from '@lucide/vue';
import type { OwnerClaim, OwnerFoundNotification, OwnerLinks } from '@/types';

type Props = {
    /** The report kind, which decides which owner panel is rendered. */
    type: 'lost' | 'found';
    claims?: OwnerClaim[] | null;
    foundNotifications?: OwnerFoundNotification[] | null;
    links: OwnerLinks | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'request-delete'): void;
}>();

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
});

const format = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    const date = new Date(`${value}T00:00:00`);

    return Number.isNaN(date.getTime()) ? '—' : dateFormatter.format(date);
};

const claimStatusLabels: Record<string, string> = {
    pending: 'Menunggu tinjauan',
    approved: 'Disetujui',
    rejected: 'Ditolak',
};

const claimStatusClasses: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
};
</script>

<template>
    <section class="rounded-xl border bg-card p-4 shadow-xs sm:p-6">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="font-medium">Kelola Laporan Ini</h2>
                <p class="text-sm text-muted-foreground">
                    Tindakan yang hanya tersedia untukmu sebagai pelapor.
                </p>
            </div>
            <div v-if="links" class="flex items-center gap-2">
                <a
                    :href="links.edit"
                    class="inline-flex items-center gap-1.5 rounded-md border bg-background px-3 py-1.5 text-xs font-medium shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                >
                    <Pencil class="size-3.5" aria-hidden="true" />
                    Ubah laporan
                </a>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md border border-destructive/30 bg-background px-3 py-1.5 text-xs font-medium text-destructive shadow-xs transition-colors hover:bg-destructive/10 focus-visible:ring-[3px] focus-visible:ring-destructive/20 focus-visible:outline-none"
                    @click="emit('request-delete')"
                >
                    <Trash2 class="size-3.5" aria-hidden="true" />
                    Hapus laporan
                </button>
            </div>
        </div>

        <!-- Found report: ownership claims awaiting the reporter's review. -->
        <div v-if="type === 'found'" class="mt-5 space-y-3">
            <h3 class="text-sm font-medium">
                Klaim Kepemilikan
                <span v-if="props.claims?.length" class="text-muted-foreground">
                    ({{ props.claims.length }})
                </span>
            </h3>

            <div
                v-if="props.claims?.length"
                class="space-y-3"
            >
                <article
                    v-for="claim in props.claims"
                    :key="claim.id"
                    class="rounded-lg border p-3"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium">
                                {{ claim.claimant.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Diajukan {{ format(claim.created_at) }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                            :class="
                                claimStatusClasses[claim.status] ??
                                'bg-muted text-muted-foreground ring-border'
                            "
                        >
                            {{ claimStatusLabels[claim.status] ?? claim.status }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-relaxed">
                        {{ claim.proof_description }}
                    </p>

                    <p
                        v-if="claim.rejection_reason"
                        class="mt-2 rounded-md bg-muted/60 p-2 text-xs text-muted-foreground"
                    >
                        Alasan penolakan: {{ claim.rejection_reason }}
                    </p>

                    <img
                        v-if="claim.proof_image"
                        :src="`/storage/${claim.proof_image}`"
                        :alt="`Bukti dari ${claim.claimant.name}`"
                        class="mt-3 aspect-[4/3] w-full max-w-xs rounded-lg border object-cover"
                        loading="lazy"
                    />

                    <!-- Contact details are only ever sent to the report owner. -->
                    <dl class="mt-3 grid gap-1 text-xs text-muted-foreground">
                        <div class="flex gap-1.5">
                            <dt>Email:</dt>
                            <dd class="text-foreground">{{ claim.claimant.email }}</dd>
                        </div>
                        <div class="flex gap-1.5">
                            <dt>Telepon:</dt>
                            <dd class="text-foreground">
                                {{ claim.claimant.phone_number ?? 'Tidak tersedia' }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <p
                v-else
                class="flex items-center gap-2 rounded-lg border border-dashed px-3 py-4 text-sm text-muted-foreground"
            >
                <PackageSearch class="size-4 shrink-0" aria-hidden="true" />
                Belum ada klaim untuk barang ini.
            </p>
        </div>

        <!-- Lost report: sightings other users reported. -->
        <div v-else class="mt-5 space-y-3">
            <h3 class="text-sm font-medium">
                Laporan Penemuan
                <span v-if="props.foundNotifications?.length" class="text-muted-foreground">
                    ({{ props.foundNotifications.length }})
                </span>
            </h3>

            <div v-if="props.foundNotifications?.length" class="space-y-3">
                <article
                    v-for="notification in props.foundNotifications"
                    :key="notification.id"
                    class="rounded-lg border p-3"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium">
                            {{ notification.reporter.name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ format(notification.created_at) }}
                        </p>
                    </div>

                    <p class="mt-3 text-sm leading-relaxed">
                        {{ notification.note }}
                    </p>

                    <img
                        v-if="notification.image_path"
                        :src="`/storage/${notification.image_path}`"
                        :alt="`Foto dari ${notification.reporter.name}`"
                        class="mt-3 aspect-[4/3] w-full max-w-xs rounded-lg border object-cover"
                        loading="lazy"
                    />

                    <dl class="mt-3 grid gap-1 text-xs text-muted-foreground">
                        <div class="flex gap-1.5">
                            <dt>Email:</dt>
                            <dd class="text-foreground">{{ notification.reporter.email }}</dd>
                        </div>
                        <div class="flex gap-1.5">
                            <dt>Telepon:</dt>
                            <dd class="text-foreground">
                                {{ notification.reporter.phone_number ?? 'Tidak tersedia' }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <p
                v-else
                class="flex items-center gap-2 rounded-lg border border-dashed px-3 py-4 text-sm text-muted-foreground"
            >
                <Tag class="size-4 shrink-0" aria-hidden="true" />
                Belum ada yang melaporkan menemukan barang ini.
            </p>
        </div>
    </section>
</template>
