<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Loader2, ShieldCheck, X } from '@lucide/vue';
import { onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    actionUrl: string;
}>();

const form = useForm<{
    proof_description: string;
    proof_image: File | null;
}>({
    proof_description: '',
    proof_image: null,
});

const previewUrl = ref<string | null>(null);

function releasePreview(): void {
    if (previewUrl.value !== null) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
}

function onImageChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    releasePreview();
    form.proof_image = file;

    if (file !== null) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

function clearImage(): void {
    releasePreview();
    form.proof_image = null;
}

function submit(): void {
    if (form.processing) {
        return;
    }

    form.post(props.actionUrl, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            releasePreview();
        },
    });
}

onUnmounted(releasePreview);
</script>

<template>
    <form
        class="rounded-xl border bg-emerald-50/40 p-4 shadow-xs sm:p-6 dark:bg-emerald-400/5"
        @submit.prevent="submit"
    >
        <div class="space-y-1">
            <h2 class="font-medium">Klaim Barang Ini</h2>
            <p class="text-sm text-muted-foreground">
                Berikan bukti kepemilikan. Detail ini hanya dilihat oleh orang
                yang melaporkan penemuan barang.
            </p>
        </div>

        <div class="mt-4 grid gap-2">
            <Label for="proof-description">Bukti Kepemilikan</Label>
            <textarea
                id="proof-description"
                v-model="form.proof_description"
                name="proof_description"
                rows="4"
                placeholder="Sebutkan ciri khas barang yang hanya diketahui pemiliknya."
                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/30"
                :aria-invalid="Boolean(form.errors.proof_description)"
            />
            <InputError :message="form.errors.proof_description" />
        </div>

        <div class="mt-4 grid gap-2">
            <Label for="proof-image">
                Foto Bukti
                <span class="text-muted-foreground">(opsional)</span>
            </Label>

            <div v-if="previewUrl" class="relative w-full max-w-xs">
                <img
                    :src="previewUrl"
                    alt="Pratinjau bukti"
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

            <div class="flex items-center gap-3 rounded-lg border border-dashed px-3 py-2.5">
                <ImagePlus class="size-4 shrink-0 text-muted-foreground" aria-hidden="true" />
                <input
                    id="proof-image"
                    type="file"
                    name="proof_image"
                    accept="image/png,image/jpeg"
                    class="w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground hover:file:bg-secondary/80"
                    :aria-invalid="Boolean(form.errors.proof_image)"
                    @change="onImageChange"
                />
            </div>
            <p class="text-xs text-muted-foreground">
                Format JPG, JPEG, atau PNG. Ukuran maksimal 2 MB.
            </p>
            <InputError :message="form.errors.proof_image" />
        </div>

        <div class="mt-4 flex items-center justify-between gap-3">
            <p class="flex items-start gap-1.5 text-xs text-muted-foreground">
                <ShieldCheck class="mt-0.5 size-3.5 shrink-0" aria-hidden="true" />
                Kontakmu hanya dibagikan ke pelapor penemuan.
            </p>
            <Button type="submit" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? 'Mengirim…' : 'Kirim Klaim' }}
            </Button>
        </div>
    </form>
</template>
