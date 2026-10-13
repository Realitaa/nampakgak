<script setup lang="ts">
import { computed, ref } from 'vue';
import { CalendarDays, EyeOff, Hand, Inbox } from '@lucide/vue';
import { formatReportDate } from '@/components/landing/fixtures';
import type { DashboardClaim } from '@/components/dashboard/fixtures';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

const props = defineProps<{
    claims: DashboardClaim[];
}>();

const selectedClaim = ref<DashboardClaim | null>(null);

const reviewDialogOpen = computed({
    get: () => selectedClaim.value !== null,
    set: (value: boolean) => {
        if (!value) {
            selectedClaim.value = null;
        }
    },
});

const incomingClaims = computed(() =>
    props.claims.filter(
        (claim) => claim.direction === 'incoming' && claim.status === 'pending',
    ),
);

const submittedClaims = computed(() =>
    props.claims.filter((claim) => claim.direction === 'submitted'),
);

const claimStatusLabels: Record<DashboardClaim['status'], string> = {
    pending: 'Awaiting review',
    approved: 'Approved',
    declined: 'Declined',
};

const claimStatusClasses: Record<DashboardClaim['status'], string> = {
    pending:
        'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
    approved:
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
    declined:
        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/30',
};
</script>

<template>
    <Card class="gap-4 py-5">
        <CardHeader>
            <CardTitle>Claims Requiring Attention</CardTitle>
            <CardDescription>
                Claims on items you've found, and updates on claims you've
                submitted.
            </CardDescription>
            <CardAction v-if="incomingClaims.length">
                <Badge variant="outline" class="text-muted-foreground">
                    {{ incomingClaims.length }} awaiting review
                </Badge>
            </CardAction>
        </CardHeader>

        <CardContent class="space-y-5">
            <div
                v-if="incomingClaims.length"
                class="space-y-1"
                aria-label="Claims awaiting your review"
            >
                <h3
                    class="px-0.5 pb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Awaiting your review
                </h3>
                <article
                    v-for="claim in incomingClaims"
                    :key="claim.id"
                    class="flex flex-col gap-3 rounded-lg border bg-muted/30 p-3 sm:flex-row sm:items-center sm:gap-4"
                >
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-background text-muted-foreground ring-1 ring-border"
                        >
                            <Hand class="size-4" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium">
                                {{ claim.reportTitle }}
                            </h4>
                            <p
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-muted-foreground"
                            >
                                <span>Claimed by {{ claim.claimant }}</span>
                                <span class="flex items-center gap-1">
                                    <CalendarDays
                                        class="size-3 shrink-0"
                                        aria-hidden="true"
                                    />
                                    Submitted
                                    {{ formatReportDate(claim.submittedAt) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        class="shrink-0"
                        @click="selectedClaim = claim"
                    >
                        Review claim
                        <span class="sr-only"> — {{ claim.reportTitle }} </span>
                    </Button>
                </article>
            </div>

            <div
                v-else
                class="flex flex-col items-center gap-3 rounded-lg border border-dashed px-6 py-10 text-center"
            >
                <span
                    class="flex size-11 items-center justify-center rounded-full bg-muted"
                >
                    <Inbox
                        class="size-5 text-muted-foreground"
                        aria-hidden="true"
                    />
                </span>
                <div class="space-y-1">
                    <h3 class="text-sm font-medium">
                        No claims need your attention
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        When someone claims an item you've found, it will show
                        up here for you to review.
                    </p>
                </div>
            </div>

            <div v-if="submittedClaims.length">
                <Separator />
                <h3
                    class="px-0.5 pt-4 pb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    Submitted by you
                </h3>
                <article
                    v-for="claim in submittedClaims"
                    :key="claim.id"
                    class="flex flex-col gap-2 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
                >
                    <div class="min-w-0">
                        <h4 class="truncate text-sm font-medium">
                            {{ claim.reportTitle }}
                        </h4>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Submitted
                            {{ formatReportDate(claim.submittedAt) }}
                        </p>
                    </div>
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="claimStatusClasses[claim.status]"
                    >
                        {{ claimStatusLabels[claim.status] }}
                    </span>
                </article>
            </div>
        </CardContent>
    </Card>

    <Dialog v-model:open="reviewDialogOpen">
        <DialogContent class="max-w-md gap-0">
            <DialogHeader v-if="selectedClaim">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge variant="secondary">Claim review</Badge>
                </div>
                <DialogTitle class="text-left text-lg leading-snug">
                    {{ selectedClaim.reportTitle }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    Claim submitted by {{ selectedClaim.claimant }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedClaim" class="space-y-4">
                <dl
                    class="grid grid-cols-[auto_1fr] items-start gap-x-3 gap-y-2 text-sm"
                >
                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <Hand class="size-4" aria-hidden="true" />
                        <span class="sr-only">Claimant</span>
                    </dt>
                    <dd>{{ selectedClaim.claimant }}</dd>

                    <dt class="flex items-center gap-1.5 text-muted-foreground">
                        <CalendarDays class="size-4" aria-hidden="true" />
                        <span class="sr-only">Submitted</span>
                    </dt>
                    <dd>{{ formatReportDate(selectedClaim.submittedAt) }}</dd>
                </dl>

                <div
                    class="flex items-start gap-2.5 rounded-lg bg-muted/60 p-3 text-xs leading-relaxed text-muted-foreground"
                >
                    <EyeOff class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <span>
                        Ownership evidence stays private — it is only shared
                        when the full claim review happens.
                    </span>
                </div>

                <div
                    class="flex items-start gap-2.5 rounded-lg border border-dashed p-3 text-xs leading-relaxed text-muted-foreground"
                >
                    <span>
                        Claim review isn't available yet. Approve and decline
                        actions will appear here once the claims module ships.
                    </span>
                </div>
            </div>

            <DialogFooter class="mt-2">
                <DialogClose as-child>
                    <Button variant="outline">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
