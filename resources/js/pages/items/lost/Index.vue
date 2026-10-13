<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OwnedReportsIndex from '@/components/items/OwnedReportsIndex.vue';
import {
    create as lostItemsCreate,
    destroy as lostItemsDestroy,
    edit as lostItemsEdit,
    index as lostItemsIndex,
    status as lostItemsStatus,
} from '@/routes/lost-items';
import type {
    FilterOption,
    OwnedItemFilters,
    OwnedItemReport,
    StatusOptions,
} from '@/types';

defineProps<{
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
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laporan Kehilangan',
                href: lostItemsIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Laporan Kehilangan" />

    <OwnedReportsIndex
        type="lost"
        :items="items"
        :filters="filters"
        :categories="categories"
        :locations="locations"
        :status-options="statusOptions"
        :index-url-for="(query) => lostItemsIndex.url({ query })"
        :create-url="lostItemsCreate.url()"
        :edit-url-for="(id) => lostItemsEdit.url(id)"
        :status-url-for="(id) => lostItemsStatus.url(id)"
        :destroy-url-for="(id) => lostItemsDestroy.url(id)"
    />
</template>
