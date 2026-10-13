<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ScanSearch } from '@lucide/vue';
import { dashboard, login, register } from '@/routes';
import type { User } from '@/types';

defineProps<{
    user: User | null;
}>();

const emit = defineEmits<{
    (e: 'jump', target: 'recent-reports' | 'how-it-works'): void;
}>();

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t bg-muted/40">
        <div
            class="mx-auto grid w-full max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-[1.2fr_1fr_1fr_1fr]"
        >
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                    >
                        <ScanSearch class="size-4.5" aria-hidden="true" />
                    </span>
                    <span class="text-sm font-semibold tracking-tight">
                        NampakGak?
                    </span>
                </div>
                <p
                    class="max-w-xs text-sm leading-relaxed text-muted-foreground"
                >
                    A campus lost and found board for the UNIMED community.
                    Report what you've lost, share what you've found, and help
                    items get back to their owners.
                </p>
            </div>

            <nav aria-label="Explore">
                <h3 class="text-sm font-medium">Explore</h3>
                <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                    <li>
                        <button
                            type="button"
                            class="transition-colors hover:text-foreground"
                            @click="emit('jump', 'recent-reports')"
                        >
                            Browse reports
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="transition-colors hover:text-foreground"
                            @click="emit('jump', 'how-it-works')"
                        >
                            How it works
                        </button>
                    </li>
                </ul>
            </nav>

            <nav aria-label="Report">
                <h3 class="text-sm font-medium">Report an item</h3>
                <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                    <li>
                        <Link
                            :href="user ? dashboard() : register()"
                            class="transition-colors hover:text-foreground"
                        >
                            Report a lost item
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="user ? dashboard() : register()"
                            class="transition-colors hover:text-foreground"
                        >
                            Report a found item
                        </Link>
                    </li>
                </ul>
            </nav>

            <nav aria-label="Account">
                <h3 class="text-sm font-medium">Account</h3>
                <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                    <template v-if="user">
                        <li>
                            <Link
                                :href="dashboard()"
                                class="transition-colors hover:text-foreground"
                            >
                                Dashboard
                            </Link>
                        </li>
                    </template>
                    <template v-else>
                        <li>
                            <Link
                                :href="login()"
                                class="transition-colors hover:text-foreground"
                            >
                                Log in
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="register()"
                                class="transition-colors hover:text-foreground"
                            >
                                Register
                            </Link>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>

        <div class="border-t">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-muted-foreground sm:flex-row sm:px-6"
            >
                <p>&copy; {{ year }} NampakGak? All rights reserved.</p>
                <p>Campus lost &amp; found for the UNIMED community.</p>
            </div>
        </div>
    </footer>
</template>
