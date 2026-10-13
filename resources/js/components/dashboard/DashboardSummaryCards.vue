<script setup lang="ts">
import type { Component } from 'vue';
import { BadgeCheck, Clock, Hand, SearchX } from '@lucide/vue';
import type { DashboardSummary } from '@/components/dashboard/fixtures';

defineProps<{
    summary: DashboardSummary;
}>();

const cards: {
    key: keyof DashboardSummary;
    label: string;
    icon: Component;
}[] = [
    { key: 'activeLost', label: 'Lost Items', icon: SearchX },
    { key: 'activeFound', label: 'Found Items', icon: Hand },
    { key: 'pendingClaims', label: 'Pending Claims', icon: Clock },
    { key: 'resolved', label: 'Resolved Items', icon: BadgeCheck },
];
</script>

<template>
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div
            v-for="card in cards"
            :key="card.key"
            class="rounded-xl border bg-card p-5 shadow-xs"
        >
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm text-muted-foreground">
                    {{ card.label }}
                </span>
                <span
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                >
                    <component
                        :is="card.icon"
                        class="size-4"
                        aria-hidden="true"
                    />
                </span>
            </div>
            <p class="mt-2 text-2xl font-semibold tracking-tight">
                {{ summary[card.key] }}
            </p>
        </div>
    </div>
</template>
