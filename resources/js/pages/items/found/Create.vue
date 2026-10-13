<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ItemForm from '@/components/items/ItemForm.vue';
import {
    create as foundItemsCreate,
    index as foundItemsIndex,
    store as foundItemsStore,
} from '@/routes/found-items';
import type { FilterOption } from '@/types';

defineProps<{
    categories: FilterOption[];
    locations: FilterOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laporan Penemuan',
                href: foundItemsIndex(),
            },
            {
                title: 'Tambah Laporan',
                href: foundItemsCreate(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Tambah Laporan Penemuan" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="max-w-xl">
            <h1 class="text-2xl font-semibold tracking-tight">
                Tambah Laporan Penemuan
            </h1>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Catat detail barang yang kamu temukan agar pemiliknya dapat
                mengenali dan mengambilnya kembali.
            </p>
        </div>

        <div class="rounded-xl border bg-card p-4 shadow-xs sm:p-6">
            <ItemForm
                mode="create"
                type="found"
                :categories="categories"
                :locations="locations"
                :index-url="foundItemsIndex.url()"
                :store-url="foundItemsStore.url()"
                update-url=""
            />
        </div>
    </div>
</template>
