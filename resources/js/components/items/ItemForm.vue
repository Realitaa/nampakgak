<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ImagePlus, Loader2, X } from '@lucide/vue';
import { computed, onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
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
import { index as lostItemsIndex, store, update } from '@/routes/lost-items';
import type { FilterOption, OwnedItemReport } from '@/types';

const props = defineProps<{
    mode: 'create' | 'edit';
    categories: FilterOption[];
    locations: FilterOption[];
    item?: OwnedItemReport;
}>();

const form = useForm<{
    title: string;
    description: string;
    category_id: string;
    location_id: string;
    event_date: string;
    image: File | null;
}>({
    title: props.item?.title ?? '',
    description: props.item?.description ?? '',
    category_id: props.item ? String(props.item.category_id) : '',
    location_id: props.item ? String(props.item.location_id) : '',
    event_date: props.item?.event_date ?? '',
    image: null,
});

const previewUrl = ref<string | null>(null);
const previewFailed = ref(false);

// The stored image shown in edit mode until a replacement is picked.
const existingImageUrl = computed(() =>
    props.item?.image_path ? `/storage/${props.item.image_path}` : null,
);

const showExistingImage = computed(
    () => existingImageUrl.value !== null && !previewFailed.value,
);

const heading = computed(() =>
    props.mode === 'create' ? 'Tambah Laporan Kehilangan' : 'Ubah Laporan Kehilangan',
);

const submitLabel = computed(() =>
    props.mode === 'create' ? 'Simpan Laporan' : 'Simpan Perubahan',
);

function releasePreview(): void {
    if (previewUrl.value !== null) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
}

function onImageChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    releasePreview();
    previewFailed.value = false;
    form.image = file;

    if (file !== null) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

function clearImage(event: Event): void {
    releasePreview();
    form.image = null;

    // Reset the file input so the same file can be chosen again.
    const input = (event.currentTarget as HTMLElement)
        .closest('form')
        ?.querySelector<HTMLInputElement>('input[type="file"]');

    if (input) {
        input.value = '';
    }
}

function submit(): void {
    if (form.processing) {
        return;
    }

    if (props.mode === 'create') {
        form.post(store.url(), { forceFormData: true });

        return;
    }

    // A multipart PUT is awkward to send from a browser, so the update is
    // posted as a form-data POST with a _method override.
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(update.url(props.item!.id), { forceFormData: true });
}

onUnmounted(releasePreview);
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="title">Judul Barang</Label>
            <Input
                id="title"
                v-model="form.title"
                name="title"
                placeholder="Contoh: Tas ransel hitam"
                :aria-invalid="Boolean(form.errors.title)"
            />
            <InputError :message="form.errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Deskripsi</Label>
            <textarea
                id="description"
                v-model="form.description"
                name="description"
                rows="4"
                placeholder="Jelaskan ciri-ciri barang, isi, dan detail lain yang membantu."
                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/30"
                :aria-invalid="Boolean(form.errors.description)"
            />
            <InputError :message="form.errors.description" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="category">Kategori</Label>
                <Select v-model="form.category_id">
                    <SelectTrigger id="category" class="w-full">
                        <SelectValue placeholder="Pilih kategori" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="category in categories"
                            :key="category.id"
                            :value="String(category.id)"
                        >
                            {{ category.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.category_id" />
            </div>

            <div class="grid gap-2">
                <Label for="location">Lokasi Terakhir Dilihat</Label>
                <Select v-model="form.location_id">
                    <SelectTrigger id="location" class="w-full">
                        <SelectValue placeholder="Pilih lokasi" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="location in locations"
                            :key="location.id"
                            :value="String(location.id)"
                        >
                            {{ location.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.location_id" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="event_date">
                Tanggal Kehilangan
                <span class="text-muted-foreground">(opsional)</span>
            </Label>
            <Input
                id="event_date"
                v-model="form.event_date"
                type="date"
                name="event_date"
                :max="new Date().toISOString().slice(0, 10)"
                :aria-invalid="Boolean(form.errors.event_date)"
            />
            <InputError :message="form.errors.event_date" />
        </div>

        <div class="grid gap-2">
            <Label for="image">
                Foto Barang
                <span v-if="mode === 'edit'" class="text-muted-foreground">
                    (biarkan kosong untuk mempertahankan foto saat ini)
                </span>
            </Label>

            <div v-if="previewUrl" class="relative w-full max-w-xs">
                <img
                    :src="previewUrl"
                    alt="Pratinjau foto barang"
                    class="aspect-[4/3] w-full rounded-lg border object-cover"
                />
                <Button
                    type="button"
                    variant="secondary"
                    size="icon-sm"
                    class="absolute top-2 right-2"
                    aria-label="Hapus foto yang dipilih"
                    @click="clearImage"
                >
                    <X aria-hidden="true" />
                </Button>
            </div>

            <div v-else-if="mode === 'edit' && showExistingImage" class="w-full max-w-xs">
                <img
                    :src="existingImageUrl ?? undefined"
                    alt="Foto barang saat ini"
                    class="aspect-[4/3] w-full rounded-lg border object-cover"
                    @error="previewFailed = true"
                />
            </div>

            <div
                class="flex items-center gap-3 rounded-lg border border-dashed px-3 py-2.5"
            >
                <ImagePlus
                    class="size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
                <input
                    id="image"
                    type="file"
                    name="image"
                    accept="image/png,image/jpeg"
                    class="w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground hover:file:bg-secondary/80"
                    :aria-invalid="Boolean(form.errors.image)"
                    @change="onImageChange"
                />
            </div>
            <p class="text-xs text-muted-foreground">
                Format JPG, JPEG, atau PNG. Ukuran maksimal 2 MB.
            </p>
            <InputError :message="form.errors.image" />
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <Button type="button" variant="outline" as-child>
                <Link :href="lostItemsIndex()">Batal</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                <Loader2
                    v-if="form.processing"
                    class="animate-spin"
                    aria-hidden="true"
                />
                {{ form.processing ? 'Menyimpan…' : submitLabel }}
            </Button>
        </div>
    </form>
</template>
