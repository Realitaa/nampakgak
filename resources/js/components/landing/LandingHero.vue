<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Compass, Hand, Search, SearchX } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard, register } from '@/routes';
import { index as itemsIndex } from '@/routes/items';
import type { User } from '@/types';

const props = defineProps<{
    user: User | null;
}>();

const emit = defineEmits<{
    (e: 'search', query: string): void;
}>();

const query = ref('');

const reportHref = props.user ? dashboard() : register();
const accountNote =
    'Reporting needs a free account — you will be asked to sign in or register.';

const suggestions = ['ID card', 'wallet', 'keys', 'earbuds'];

function submit(): void {
    emit('search', query.value);
}

function searchFor(term: string): void {
    query.value = term;
    emit('search', term);
}
</script>

<template>
    <section
        class="mx-auto w-full max-w-6xl px-4 pt-16 pb-14 sm:px-6 sm:pt-24 sm:pb-20"
    >
        <div class="mx-auto max-w-2xl text-center">
            <p
                class="text-xs font-semibold tracking-[0.2em] text-primary uppercase"
            >
                Lost something on campus?
            </p>
            <h1
                class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl"
            >
                Something missing? Let's find it.
            </h1>
            <p
                class="mx-auto mt-4 max-w-xl text-base text-pretty text-muted-foreground sm:text-lg"
            >
                Lost your belongings or found something on campus? NampakGak?
                helps connect lost items with the people looking for them.
            </p>
        </div>

        <form
            class="mx-auto mt-8 flex w-full max-w-2xl flex-col gap-2 rounded-xl border bg-card p-2 shadow-xs sm:flex-row sm:items-center"
            role="search"
            @submit.prevent="submit"
        >
            <div class="grid flex-1 gap-1.5">
                <Label for="landing-search" class="sr-only">
                    Search for a lost or found item
                </Label>
                <Input
                    id="landing-search"
                    v-model="query"
                    type="search"
                    placeholder="Search for a wallet, ID card, keys..."
                    class="h-11 border-0 bg-transparent text-base shadow-none focus-visible:border-transparent focus-visible:ring-0 md:text-sm dark:bg-transparent"
                    autocomplete="off"
                />
            </div>
            <Button type="submit" size="lg" class="h-11 shrink-0">
                <Search aria-hidden="true" />
                Search
            </Button>
        </form>

        <div
            class="mx-auto mt-3 flex w-full max-w-2xl flex-wrap items-center justify-center gap-2"
        >
            <span class="text-xs text-muted-foreground">Try:</span>
            <Button
                v-for="term in suggestions"
                :key="term"
                variant="outline"
                size="sm"
                type="button"
                class="h-7 rounded-md px-2.5 text-xs font-normal text-muted-foreground"
                @click="searchFor(term)"
            >
                {{ term }}
            </Button>
        </div>

        <div
            class="mx-auto mt-6 flex w-full max-w-2xl flex-col items-center justify-center gap-3 sm:flex-row"
        >
            <Button as-child class="w-full sm:w-auto">
                <Link :href="itemsIndex()">
                    <Compass aria-hidden="true" />
                    Discover all reports
                </Link>
            </Button>
            <Button variant="outline" as-child class="w-full sm:w-auto">
                <Link :href="reportHref">
                    <SearchX aria-hidden="true" />
                    Report a Lost Item
                </Link>
            </Button>
            <Button variant="secondary" as-child class="w-full sm:w-auto">
                <Link :href="reportHref">
                    <Hand aria-hidden="true" />
                    Report a Found Item
                </Link>
            </Button>
        </div>

        <p v-if="!user" class="mt-3 text-center text-xs text-muted-foreground">
            {{ accountNote }}
        </p>
    </section>
</template>
