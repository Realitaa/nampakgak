<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    PackageSearch,
    RotateCcw,
    ScanSearch,
    Search,
    SearchX,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ItemCard from '@/components/items/ItemCard.vue';
import ThemeToggle from '@/components/landing/ThemeToggle.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard, login, register } from '@/routes';
// Future item-detail route: pass each card's href once `items.show` exists.
// Cards stay link-ready until then (see ItemCard).
import { index as itemsIndex } from '@/routes/items';
import type {
    FilterOption,
    ItemFilters,
    ItemReport,
    TypeOptions,
} from '@/types';

const props = defineProps<{
    items: {
        current_page: number;
        data: ItemReport[];
        from: number | null;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
        to: number | null;
        total: number;
    };
    filters: ItemFilters;
    categories: FilterOption[];
    locations: FilterOption[];
    typeOptions: TypeOptions;
}>();

// Local mirror of the active filters, hydrated from the server so URLs stay
// the single source of truth (back/forward included).
const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');
const category = ref(
    props.filters.category === null ? '' : String(props.filters.category),
);
const location = ref(
    props.filters.location === null ? '' : String(props.filters.location),
);

watch(
    () => props.filters,
    (next) => {
        search.value = next.search ?? '';
        type.value = next.type ?? '';
        category.value = next.category === null ? '' : String(next.category);
        location.value = next.location === null ? '' : String(next.location);
    },
);

const pending = ref(false);

type Query = Record<string, string>;

function currentQuery(): Query {
    const query: Query = {};

    if (search.value.trim()) {
        query.search = search.value.trim();
    }

    if (type.value) {
        query.type = type.value;
    }

    if (category.value) {
        query.category = category.value;
    }

    if (location.value) {
        query.location = location.value;
    }

    return query;
}

function visit(page?: number): void {
    const query = currentQuery();

    if (page !== undefined && page > 1) {
        query.page = String(page);
    }

    pending.value = true;
    router.get(
        itemsIndex.url({ query }),
        {},
        {
            preserveState: true,
            onFinish: () => {
                pending.value = false;
            },
        },
    );
}

// Searching or changing a filter always restarts from the first page.
const submitSearch = () => visit();
const setType = (value: string) => {
    type.value = value;
    visit();
};
const setCategory = (value: string) => {
    category.value = value;
    visit();
};
const setLocation = (value: string) => {
    location.value = value;
    visit();
};
const clearFilters = () => {
    search.value = '';
    type.value = '';
    category.value = '';
    location.value = '';
    visit();
};

const categoryLabel = (id: string): string =>
    props.categories.find((option) => String(option.id) === id)?.name ?? id;

const locationLabel = (id: string): string =>
    props.locations.find((option) => String(option.id) === id)?.name ?? id;

const activeChips = computed(() => {
    const chips: { key: string; label: string; clear: () => void }[] = [];

    if (search.value.trim()) {
        chips.push({
            key: 'search',
            label: `"${search.value.trim()}"`,
            clear: () => {
                search.value = '';
                visit();
            },
        });
    }

    if (type.value) {
        chips.push({
            key: 'type',
            label: props.typeOptions[type.value] ?? type.value,
            clear: () => {
                type.value = '';
                visit();
            },
        });
    }

    if (category.value) {
        chips.push({
            key: 'category',
            label: categoryLabel(category.value),
            clear: () => {
                category.value = '';
                visit();
            },
        });
    }

    if (location.value) {
        chips.push({
            key: 'location',
            label: locationLabel(location.value),
            clear: () => {
                location.value = '';
                visit();
            },
        });
    }

    return chips;
});

const hasActiveFilters = computed(() => activeChips.value.length > 0);

// Compact page window: 1 … c-1 c c+1 … last (only when needed).
const pageLinks = computed<(number | 'gap')[]>(() => {
    const { current_page: current, last_page: last } = props.items;

    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    const wanted = new Set(
        [1, last, current - 1, current, current + 1].filter(
            (page) => page >= 1 && page <= last,
        ),
    );

    const pages = [...wanted].sort((a, b) => a - b);
    const links: (number | 'gap')[] = [];

    pages.forEach((page, index) => {
        if (index > 0 && page - (pages[index - 1] as number) > 1) {
            links.push('gap');
        }
        links.push(page);
    });

    return links;
});

const pageHref = (page: number): string => {
    const query = currentQuery();
    query.page = String(page);
    return itemsIndex.url({ query });
};

const showingLabel = computed(() => {
    const { from, to, total } = props.items;

    if (from === null || to === null) {
        return 'No reports to show';
    }

    return `Showing ${from}–${to} of ${total} reports`;
});

const summaryLabel = computed(() =>
    props.items.total === 1 ? '1 report' : `${props.items.total} reports`,
);

const typeFilterOptions = [
    { value: '', label: 'All' },
    ...Object.entries(props.typeOptions).map(([value, label]) => ({
        value,
        label,
    })),
];
</script>

<template>
    <Head title="Discover Items" />

    <div class="min-h-screen bg-background text-foreground">
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

                <div class="flex items-center gap-1.5">
                    <ThemeToggle />
                    <template v-if="$page.props.auth.user">
                        <Button size="sm" as-child>
                            <Link :href="dashboard()">Dashboard</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="login()">Log in</Link>
                        </Button>
                        <Button size="sm" as-child>
                            <Link :href="register()">Register</Link>
                        </Button>
                    </template>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-semibold tracking-tight">
                    Discover Items
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Browse every lost and found report from campus. Search by
                    keyword, or narrow things down by type, category, and
                    location.
                </p>
            </div>

            <section
                class="mt-8 rounded-xl border bg-card p-4 shadow-xs sm:p-5"
                aria-label="Search and filters"
            >
                <form
                    class="flex flex-col gap-2 sm:flex-row sm:items-center"
                    role="search"
                    @submit.prevent="submitSearch"
                >
                    <div class="grid flex-1 gap-1.5">
                        <Label for="discover-search" class="sr-only">
                            Search reports
                        </Label>
                        <Input
                            id="discover-search"
                            v-model="search"
                            type="search"
                            placeholder="Search by title or description..."
                            autocomplete="off"
                        />
                    </div>
                    <Button type="submit" class="shrink-0" :disabled="pending">
                        <Search aria-hidden="true" />
                        Search
                    </Button>
                </form>

                <div
                    class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <div
                            class="inline-flex w-fit gap-1 rounded-lg bg-muted p-1"
                            role="group"
                            aria-label="Filter by report type"
                        >
                            <button
                                v-for="option in typeFilterOptions"
                                :key="option.value || 'all'"
                                type="button"
                                class="rounded-md px-3 py-1.5 text-sm transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                                :class="
                                    type === option.value
                                        ? 'bg-background font-medium text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                :aria-pressed="type === option.value"
                                @click="setType(option.value)"
                            >
                                {{ option.label }}
                            </button>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="grid gap-1.5">
                                <Label for="filter-category" class="sr-only">
                                    Filter by category
                                </Label>
                                <Select
                                    :model-value="category"
                                    @update:model-value="
                                        setCategory(String($event))
                                    "
                                >
                                    <SelectTrigger
                                        id="filter-category"
                                        class="w-full sm:w-44"
                                    >
                                        <SelectValue
                                            placeholder="All categories"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in categories"
                                            :key="option.id"
                                            :value="String(option.id)"
                                        >
                                            {{ option.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="filter-location" class="sr-only">
                                    Filter by location
                                </Label>
                                <Select
                                    :model-value="location"
                                    @update:model-value="
                                        setLocation(String($event))
                                    "
                                >
                                    <SelectTrigger
                                        id="filter-location"
                                        class="w-full sm:w-44"
                                    >
                                        <SelectValue
                                            placeholder="All locations"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in locations"
                                            :key="option.id"
                                            :value="String(option.id)"
                                        >
                                            {{ option.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        class="w-fit"
                        :disabled="pending"
                        @click="clearFilters"
                    >
                        <RotateCcw aria-hidden="true" />
                        Clear filters
                    </Button>
                </div>

                <div
                    v-if="hasActiveFilters"
                    class="mt-3 flex flex-wrap items-center gap-2 border-t pt-3"
                >
                    <span class="text-xs text-muted-foreground">
                        Active filters:
                    </span>
                    <button
                        v-for="chip in activeChips"
                        :key="chip.key"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-md border bg-muted/60 px-2 py-0.5 text-xs font-medium text-foreground/80 transition-colors hover:bg-muted focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                        :title="`Remove filter: ${chip.label}`"
                        @click="chip.clear()"
                    >
                        {{ chip.label }}
                        <X class="size-3" aria-hidden="true" />
                    </button>
                </div>
            </section>

            <p class="mt-6 text-sm text-muted-foreground" aria-live="polite">
                {{ summaryLabel }}
            </p>

            <div
                class="mt-3 transition-opacity"
                :class="pending ? 'pointer-events-none opacity-60' : ''"
                :aria-busy="pending"
            >
                <div
                    v-if="items.data.length"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <ItemCard
                        v-for="report in items.data"
                        :key="report.id"
                        :report="report"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center gap-3 rounded-xl border border-dashed px-6 py-16 text-center"
                >
                    <span
                        class="flex size-12 items-center justify-center rounded-full bg-muted"
                    >
                        <SearchX
                            class="size-5 text-muted-foreground"
                            aria-hidden="true"
                        />
                    </span>
                    <div class="space-y-1">
                        <h2 class="font-medium">No reports match</h2>
                        <p
                            class="mx-auto max-w-sm text-sm text-muted-foreground"
                        >
                            Nothing was reported with these filters yet. Try a
                            different keyword, or clear the filters to browse
                            everything.
                        </p>
                    </div>
                    <Button
                        v-if="hasActiveFilters"
                        variant="outline"
                        size="sm"
                        @click="clearFilters"
                    >
                        <RotateCcw aria-hidden="true" />
                        Clear filters
                    </Button>
                </div>
            </div>

            <nav
                v-if="items.last_page > 1"
                class="mt-8 flex flex-col items-center justify-between gap-4 sm:flex-row"
                aria-label="Pagination"
            >
                <p class="text-xs text-muted-foreground">
                    {{ showingLabel }}
                </p>

                <div class="flex items-center gap-1">
                    <Button
                        v-if="items.prev_page_url"
                        variant="outline"
                        size="sm"
                        class="gap-1"
                        as-child
                    >
                        <Link
                            :href="pageHref(items.current_page - 1)"
                            preserve-scroll
                        >
                            <ArrowLeft aria-hidden="true" />
                            <span class="sr-only sm:not-sr-only">Previous</span>
                        </Link>
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        class="gap-1"
                        disabled
                    >
                        <ArrowLeft aria-hidden="true" />
                        <span class="sr-only sm:not-sr-only">Previous</span>
                    </Button>

                    <template
                        v-for="(page, index) in pageLinks"
                        :key="`${page}-${index}`"
                    >
                        <span
                            v-if="page === 'gap'"
                            class="px-1 text-xs text-muted-foreground"
                            aria-hidden="true"
                        >
                            …
                        </span>
                        <Button
                            v-else-if="page === items.current_page"
                            variant="default"
                            size="sm"
                            class="min-w-8 px-2"
                            :aria-current="'page'"
                            disabled
                        >
                            {{ page }}
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="min-w-8 px-2"
                            as-child
                        >
                            <Link :href="pageHref(page)" preserve-scroll>
                                {{ page }}
                            </Link>
                        </Button>
                    </template>

                    <Button
                        v-if="items.next_page_url"
                        variant="outline"
                        size="sm"
                        class="gap-1"
                        as-child
                    >
                        <Link
                            :href="pageHref(items.current_page + 1)"
                            preserve-scroll
                        >
                            <span class="sr-only sm:not-sr-only">Next</span>
                            <ArrowRight aria-hidden="true" />
                        </Link>
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        class="gap-1"
                        disabled
                    >
                        <span class="sr-only sm:not-sr-only">Next</span>
                        <ArrowRight aria-hidden="true" />
                    </Button>
                </div>
            </nav>

            <p
                v-if="!items.data.length"
                class="mt-6 flex items-center justify-center gap-2 text-xs text-muted-foreground"
            >
                <PackageSearch class="size-3.5" aria-hidden="true" />
                New reports appear here the moment they are published.
            </p>
        </main>
    </div>
</template>
