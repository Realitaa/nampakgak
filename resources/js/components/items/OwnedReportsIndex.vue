<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Plus,
    RotateCcw,
    Search,
    SearchX,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import OwnedReportCard from '@/components/items/OwnedReportCard.vue';
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
import type {
    FilterOption,
    ItemType,
    OwnedItemFilters,
    OwnedItemReport,
    StatusOptions,
} from '@/types';

const props = defineProps<{
    /** Distinguishes the two report kinds for copy and empty states. */
    type: ItemType;
    items: {
        current_page: number;
        data: OwnedItemReport[];
        from: number | null;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
        to: number | null;
        total: number;
    };
    filters: OwnedItemFilters;
    categories: FilterOption[];
    locations: FilterOption[];
    statusOptions: StatusOptions;
    /** Route builders for the listing being rendered. */
    indexUrlFor: (query?: Record<string, string>) => string;
    createUrl: string;
    editUrlFor: (id: number) => string;
    statusUrlFor: (id: number) => string;
    destroyUrlFor: (id: number) => string;
    viewUrlFor: (id: number) => string;
}>();

// Copy that differs between the two report kinds.
const copy = computed(() =>
    props.type === 'found'
        ? {
              title: 'Laporan Penemuan',
              description:
                  'Kelola barang-barang yang kamu laporkan temukan, perbarui statusnya, dan pantau perkembangannya.',
              emptyTitle: 'Belum ada laporan penemuan',
              emptyBody:
                  'Mulai dengan menambahkan laporan pertama agar pemiliknya dapat menemukan barangnya kembali.',
              noMatchTitle: 'Tidak ada laporan penemuan yang cocok',
          }
        : {
              title: 'Laporan Kehilangan',
              description:
                  'Kelola barang-barang yang kamu laporkan hilang, perbarui statusnya, dan pantau perkembangannya.',
              emptyTitle: 'Belum ada laporan kehilangan',
              emptyBody:
                  'Mulai dengan menambahkan laporan pertama agar orang lain dapat membantumu menemukan barang tersebut.',
              noMatchTitle: 'Tidak ada laporan kehilangan yang cocok',
          },
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
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
        status.value = next.status ?? '';
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

    if (status.value) {
        query.status = status.value;
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
    router.get(props.indexUrlFor(query), {}, {
        preserveState: true,
        onFinish: () => {
            pending.value = false;
        },
    });
}

// Reka UI forbids an empty-string <SelectItem> value (it is reserved for
// clearing the selection), so "all" is used as the sentinel for "no filter".
const ALL = 'all';

// Any change to search or filters restarts from the first page.
const submitSearch = () => visit();
const setStatus = (value: string) => {
    status.value = value === ALL ? '' : value;
    visit();
};
const setCategory = (value: string) => {
    category.value = value === ALL ? '' : value;
    visit();
};
const setLocation = (value: string) => {
    location.value = value === ALL ? '' : value;
    visit();
};

const statusFilterOptions: { value: string; label: string }[] = [
    { value: ALL, label: 'Semua status' },
    ...Object.entries(props.statusOptions).map(([value, label]) => ({
        value,
        label: String(label),
    })),
];

const hasActiveFilters = computed(
    () =>
        search.value.trim() !== '' ||
        status.value !== '' ||
        category.value !== '' ||
        location.value !== '',
);

const clearFilters = () => {
    search.value = '';
    status.value = '';
    category.value = '';
    location.value = '';
    visit();
};

const pageLinks = computed(() => {
    const { current_page: current, last_page: last } = props.items;

    if (last <= 1) {
        return [] as number[];
    }

    const wanted = new Set<number>([1, last]);

    for (let page = current - 1; page <= current + 1; page += 1) {
        if (page >= 1 && page <= last) {
            wanted.add(page);
        }
    }

    const pages = Array.from(wanted).sort((a, b) => a - b);
    const links: number[] = [];

    pages.forEach((page, index) => {
        const previous = pages[index - 1];

        if (previous !== undefined && page - previous > 1) {
            links.push(-previous);
        }

        links.push(page);
    });

    return links;
});

function pageHref(page: number): string {
    const query = currentQuery();

    if (page > 1) {
        query.page = String(page);
    }

    return props.indexUrlFor(query);
}

const showingLabel = computed(() => {
    const { from, to, total } = props.items;

    if (from === null || to === null || total === 0) {
        return 'Belum ada laporan untuk ditampilkan';
    }

    return `Menampilkan ${from}–${to} dari ${total} laporan`;
});
</script>

<template>
    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 lg:p-6">
        <header
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="max-w-xl">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ copy.title }}
                </h1>
                <p class="mt-1.5 text-sm text-muted-foreground">
                    {{ copy.description }}
                </p>
            </div>

            <Button as-child>
                <Link :href="createUrl">
                    <Plus aria-hidden="true" />
                    Tambah Laporan
                </Link>
            </Button>
        </header>

        <section class="rounded-xl border bg-card p-4 shadow-xs">
            <form class="flex flex-col gap-3" @submit.prevent="submitSearch">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="grid flex-1 gap-1.5">
                        <Label :for="`${type}-search`" class="sr-only">
                            Cari laporan
                        </Label>
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <Input
                                :id="`${type}-search`"
                                v-model="search"
                                type="search"
                                name="search"
                                class="pl-9"
                                placeholder="Cari judul atau deskripsi barang"
                            />
                        </div>
                    </div>
                    <Button type="submit" class="shrink-0" :disabled="pending">
                        <Search aria-hidden="true" />
                        Cari
                    </Button>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="grid gap-1.5 sm:w-44">
                        <Label :for="`${type}-filter-status`" class="sr-only">
                            Status
                        </Label>
                        <Select
                            :model-value="status === '' ? ALL : status"
                            @update:model-value="setStatus(String($event))"
                        >
                            <SelectTrigger :id="`${type}-filter-status`" class="w-full">
                                <SelectValue placeholder="Semua status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in statusFilterOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5 sm:w-52">
                        <Label :for="`${type}-filter-category`" class="sr-only">
                            Kategori
                        </Label>
                        <Select
                            :model-value="category === '' ? ALL : category"
                            @update:model-value="setCategory(String($event))"
                        >
                            <SelectTrigger :id="`${type}-filter-category`" class="w-full">
                                <SelectValue placeholder="Semua kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="ALL">Semua kategori</SelectItem>
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

                    <div class="grid gap-1.5 sm:w-60">
                        <Label :for="`${type}-filter-location`" class="sr-only">
                            Lokasi
                        </Label>
                        <Select
                            :model-value="location === '' ? ALL : location"
                            @update:model-value="setLocation(String($event))"
                        >
                            <SelectTrigger :id="`${type}-filter-location`" class="w-full">
                                <SelectValue placeholder="Semua lokasi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="ALL">Semua lokasi</SelectItem>
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

                    <Button
                        v-if="hasActiveFilters"
                        type="button"
                        variant="outline"
                        class="sm:self-end"
                        @click="clearFilters"
                    >
                        <RotateCcw aria-hidden="true" />
                        Reset
                    </Button>
                </div>
            </form>
        </section>

        <p class="text-sm text-muted-foreground" aria-live="polite">
            {{ showingLabel }}
        </p>

        <div v-if="items.data.length">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <OwnedReportCard
                    v-for="report in items.data"
                    :key="report.id"
                    :report="report"
                    :status-options="statusOptions"
                    :edit-url="editUrlFor(report.id)"
                    :status-url="statusUrlFor(report.id)"
                    :destroy-url="destroyUrlFor(report.id)"
                    :view-url="viewUrlFor(report.id)"
                />
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-3 rounded-xl border border-dashed px-6 py-14 text-center"
        >
            <span class="flex size-12 items-center justify-center rounded-full bg-muted">
                <SearchX class="size-5 text-muted-foreground" aria-hidden="true" />
            </span>
            <div class="space-y-1">
                <h2 class="font-medium">
                    {{ hasActiveFilters ? copy.noMatchTitle : copy.emptyTitle }}
                </h2>
                <p class="mx-auto max-w-sm text-sm text-muted-foreground">
                    {{
                        hasActiveFilters
                            ? 'Coba kata kunci lain atau reset filter untuk melihat semua laporanmu.'
                            : copy.emptyBody
                    }}
                </p>
            </div>
            <Button v-if="hasActiveFilters" variant="outline" size="sm" @click="clearFilters">
                <RotateCcw aria-hidden="true" />
                Reset filter
            </Button>
            <Button v-else as-child>
                <Link :href="createUrl">
                    <Plus aria-hidden="true" />
                    Tambah Laporan
                </Link>
            </Button>
        </div>

        <nav
            v-if="items.last_page > 1"
            class="flex flex-col items-center justify-between gap-3 sm:flex-row"
            aria-label="Navigasi halaman"
        >
            <p class="text-xs text-muted-foreground">
                Halaman {{ items.current_page }} dari {{ items.last_page }}
            </p>

            <div class="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    as-child
                    :disabled="!items.prev_page_url"
                >
                    <Link v-if="items.prev_page_url" :href="items.prev_page_url" preserve-scroll>
                        <ArrowLeft aria-hidden="true" />
                        Sebelumnya
                    </Link>
                    <span v-else class="opacity-50">
                        <ArrowLeft aria-hidden="true" />
                        Sebelumnya
                    </span>
                </Button>

                <template v-for="(link, index) in pageLinks" :key="`${link}-${index}`">
                    <span
                        v-if="link < 0"
                        class="px-2 text-xs text-muted-foreground"
                        aria-hidden="true"
                    >
                        …
                    </span>
                    <Button
                        v-else
                        :variant="link === items.current_page ? 'default' : 'outline'"
                        size="icon-sm"
                        as-child
                    >
                        <Link :href="pageHref(link)" preserve-scroll>
                            {{ link }}
                        </Link>
                    </Button>
                </template>

                <Button
                    variant="outline"
                    size="sm"
                    as-child
                    :disabled="!items.next_page_url"
                >
                    <Link v-if="items.next_page_url" :href="items.next_page_url" preserve-scroll>
                        Berikutnya
                        <ArrowRight aria-hidden="true" />
                    </Link>
                    <span v-else class="opacity-50">
                        Berikutnya
                        <ArrowRight aria-hidden="true" />
                    </span>
                </Button>
            </div>
        </nav>
    </div>
</template>
