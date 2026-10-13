<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Hand, SearchX } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard, register } from '@/routes';
import type { User } from '@/types';

const props = defineProps<{
    user: User | null;
}>();

const reportHref = props.user ? dashboard() : register();
</script>

<template>
    <section class="mx-auto w-full max-w-6xl px-4 pb-16 sm:px-6 sm:pb-24">
        <div
            class="rounded-2xl border bg-card px-6 py-12 text-center shadow-xs sm:px-12 sm:py-16"
        >
            <h2
                class="mx-auto max-w-lg text-3xl font-semibold tracking-tight text-balance sm:text-4xl"
            >
                Help a lost item find its way home.
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-pretty text-muted-foreground">
                Every report can make a difference. Share what you've lost or
                found and help the campus community reconnect with its
                belongings.
            </p>
            <div
                class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
            >
                <Button variant="outline" size="lg" as-child>
                    <Link :href="reportHref">
                        <SearchX aria-hidden="true" />
                        Report a Lost Item
                    </Link>
                </Button>
                <Button size="lg" as-child>
                    <Link :href="reportHref">
                        <Hand aria-hidden="true" />
                        Report a Found Item
                    </Link>
                </Button>
            </div>
        </div>
    </section>
</template>
