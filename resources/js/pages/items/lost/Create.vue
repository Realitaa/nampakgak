<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ItemForm from '@/components/items/ItemForm.vue';
import { create as lostItemsCreate, index as lostItemsIndex, store as lostItemsStore } from '@/routes/lost-items';
import type { FilterOption } from '@/types';

defineProps<{
    categories: FilterOption[];
    locations: FilterOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laporan Kehilangan',
                href: lostItemsIndex(),
            },
            {
                title: 'Tambah Laporan',
                href: lostItemsCreate(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Tambah Laporan Kehilangan" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="max-w-xl">
            <h1 class="text-2xl font-semibold tracking-tight">
                Tambah Laporan Kehilangan
            </h1>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Catat detail barang yang hilang agar mudah ditemukan kembali oleh
                orang lain di kampus.
            </p>
        </div>

        <div class="rounded-xl border bg-card p-4 shadow-xs sm:p-6">
            <ItemForm
                mode="create"
                type="lost"
                :categories="categories"
                :locations="locations"
                :index-url="lostItemsIndex.url()"
                :store-url="lostItemsStore.url()"
                update-url=""
            />
        </div>
    </div>
</template>
