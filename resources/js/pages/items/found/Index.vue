<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import OwnedReportsIndex from '@/components/items/OwnedReportsIndex.vue';
import {
    create as foundItemsCreate,
    destroy as foundItemsDestroy,
    edit as foundItemsEdit,
    index as foundItemsIndex,
    status as foundItemsStatus,
} from '@/routes/found-items';
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
                title: 'Laporan Penemuan',
                href: foundItemsIndex(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Laporan Penemuan" />

    <OwnedReportsIndex
        type="found"
        :items="items"
        :filters="filters"
        :categories="categories"
        :locations="locations"
        :status-options="statusOptions"
        :index-url-for="(query) => foundItemsIndex.url({ query })"
        :create-url="foundItemsCreate.url()"
        :edit-url-for="(id) => foundItemsEdit.url(id)"
        :status-url-for="(id) => foundItemsStatus.url(id)"
        :destroy-url-for="(id) => foundItemsDestroy.url(id)"
    />
</template>
