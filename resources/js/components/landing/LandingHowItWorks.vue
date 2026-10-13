<script setup lang="ts">
import { PackageSearch, ScanSearch, ShieldCheck } from '@lucide/vue';

const steps = [
    {
        number: '1',
        icon: ScanSearch,
        title: 'Report',
        description: 'Share details about an item you lost or found on campus.',
    },
    {
        number: '2',
        icon: PackageSearch,
        title: 'Discover & claim',
        description:
            'Browse reports and submit evidence if you believe a found item belongs to you.',
    },
    {
        number: '3',
        icon: ShieldCheck,
        title: 'Verify & return',
        description:
            'The person who found the item reviews the claim and coordinates its return when ownership is verified.',
    },
];
</script>

<template>
    <section
        id="how-it-works"
        class="mx-auto w-full max-w-6xl scroll-mt-24 px-4 pb-14 sm:px-6 sm:pb-20"
    >
        <div class="max-w-xl">
            <h2 class="text-2xl font-semibold tracking-tight">How it works</h2>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Three steps between lost and found.
            </p>
        </div>

        <ol class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li
                v-for="step in steps"
                :key="step.number"
                class="rounded-xl border bg-card p-5 shadow-xs"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground"
                    >
                        {{ step.number }}
                    </span>
                    <component
                        :is="step.icon"
                        class="size-4.5 text-muted-foreground"
                        aria-hidden="true"
                    />
                </div>
                <h3 class="mt-4 font-medium">{{ step.title }}</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                    {{ step.description }}
                </p>
            </li>
        </ol>
    </section>
</template>
