<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ItemForm from '@/components/items/ItemForm.vue';
import { index as lostItemsIndex, update as lostItemsUpdate } from '@/routes/lost-items';
import type { FilterOption, OwnedItemReport } from '@/types';

const props = defineProps<{
    item: OwnedItemReport;
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
                title: 'Ubah Laporan',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head title="Ubah Laporan Kehilangan" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="max-w-xl">
            <h1 class="text-2xl font-semibold tracking-tight">
                Ubah Laporan Kehilangan
            </h1>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Perbarui detail laporan agar informasi yang ditampilkan tetap
                akurat.
            </p>
        </div>

        <div class="rounded-xl border bg-card p-4 shadow-xs sm:p-6">
            <ItemForm
                mode="edit"
                type="lost"
                :item="props.item"
                :categories="categories"
                :locations="locations"
                :index-url="lostItemsIndex.url()"
                store-url=""
                :update-url="lostItemsUpdate.url(props.item.id)"
            />
        </div>
    </div>
</template>
