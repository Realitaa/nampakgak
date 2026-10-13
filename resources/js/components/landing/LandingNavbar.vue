<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, Plus, ScanSearch } from '@lucide/vue';
import { computed, ref } from 'vue';
import ThemeToggle from '@/components/landing/ThemeToggle.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { dashboard, login, register } from '@/routes';
import type { User } from '@/types';

const props = defineProps<{
    user: User | null;
}>();

const emit = defineEmits<{
    (e: 'jump', target: 'recent-reports' | 'how-it-works'): void;
}>();

const menuOpen = ref(false);

// Report forms are not implemented yet, so report actions point at the
// closest existing flow: signing up (or in) to use the app.
const reportHref = computed(() => (props.user ? dashboard() : register()));
const reportNote =
    'Reporting needs an account — this opens the sign-up page for now.';

function closeAndJump(target: 'recent-reports' | 'how-it-works'): void {
    menuOpen.value = false;
    emit('jump', target);
}
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div
            class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-4 sm:px-6"
        >
            <Link
                href="/"
                class="flex items-center gap-2 rounded-md outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
            >
                <span
                    class="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <ScanSearch class="size-4.5" aria-hidden="true" />
                </span>
                <span class="text-sm font-semibold tracking-tight">
                    NampakGak?
                </span>
            </Link>

            <nav
                class="hidden items-center gap-1 md:flex"
                aria-label="Main navigation"
            >
                <Button
                    variant="ghost"
                    size="sm"
                    type="button"
                    @click="emit('jump', 'recent-reports')"
                >
                    Browse reports
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    type="button"
                    @click="emit('jump', 'how-it-works')"
                >
                    How it works
                </Button>
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="reportHref" :title="reportNote">
                        Report a lost item
                    </Link>
                </Button>
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="reportHref" :title="reportNote">
                        Report a found item
                    </Link>
                </Button>
            </nav>

            <div class="flex items-center gap-1.5">
                <ThemeToggle />

                <template v-if="user">
                    <Button size="sm" as-child class="hidden md:inline-flex">
                        <Link :href="dashboard()">Dashboard</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button
                        variant="ghost"
                        size="sm"
                        as-child
                        class="hidden md:inline-flex"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button size="sm" as-child class="hidden md:inline-flex">
                        <Link :href="register()">Register</Link>
                    </Button>
                </template>

                <Sheet v-model:open="menuOpen">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="md:hidden"
                        aria-label="Open menu"
                        @click="menuOpen = true"
                    >
                        <Menu class="size-5" aria-hidden="true" />
                    </Button>

                    <SheetContent side="right" class="w-72 gap-0">
                        <SheetHeader class="text-left">
                            <SheetTitle class="flex items-center gap-2">
                                <span
                                    class="flex size-7 items-center justify-center rounded-md bg-primary text-primary-foreground"
                                >
                                    <ScanSearch
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </span>
                                NampakGak?
                            </SheetTitle>
                            <SheetDescription class="sr-only">
                                Site navigation
                            </SheetDescription>
                        </SheetHeader>

                        <nav
                            class="flex flex-col gap-1 px-4 py-2"
                            aria-label="Mobile navigation"
                        >
                            <Button
                                variant="ghost"
                                class="justify-start px-2 py-2"
                                @click="closeAndJump('recent-reports')"
                            >
                                Browse reports
                            </Button>
                            <Button
                                variant="ghost"
                                class="justify-start px-2 py-2"
                                @click="closeAndJump('how-it-works')"
                            >
                                How it works
                            </Button>
                            <Button
                                variant="ghost"
                                class="justify-start gap-1.5 px-2 py-2"
                                as-child
                            >
                                <Link
                                    :href="reportHref"
                                    @click="menuOpen = false"
                                >
                                    <Plus
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    Report a lost item
                                </Link>
                            </Button>
                            <Button
                                variant="ghost"
                                class="justify-start gap-1.5 px-2 py-2"
                                as-child
                            >
                                <Link
                                    :href="reportHref"
                                    @click="menuOpen = false"
                                >
                                    <Plus
                                        class="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    Report a found item
                                </Link>
                            </Button>
                        </nav>

                        <div class="mt-auto flex flex-col gap-2 border-t p-4">
                            <template v-if="user">
                                <Button as-child>
                                    <Link
                                        :href="dashboard()"
                                        @click="menuOpen = false"
                                    >
                                        Dashboard
                                    </Link>
                                </Button>
                            </template>
                            <template v-else>
                                <Button as-child>
                                    <Link
                                        :href="register()"
                                        @click="menuOpen = false"
                                    >
                                        Register
                                    </Link>
                                </Button>
                                <Button variant="outline" as-child>
                                    <Link
                                        :href="login()"
                                        @click="menuOpen = false"
                                    >
                                        Log in
                                    </Link>
                                </Button>
                            </template>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
