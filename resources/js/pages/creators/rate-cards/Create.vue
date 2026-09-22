<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Creator {
    id: number;
    name: string;
    username: string;
}

const props = defineProps<{
    creator: Creator;
}>();

const form = useForm({
    platform: '',
    deliverable: '',
    price: 0,
    duration: '',
    revision: 0,
    usage_rights: '',
    valid_until: '',
    notes: '',
});

const submit = () => {
    form.post(`/creators/${props.creator.id}/rate-cards`);
};
</script>

<template>
    <Head :title="`Tambah Rate Card - ${creator.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link
                :href="`/creators/${creator.id}`"
                class="flex size-10 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground"
            >
                <ArrowLeft class="size-5" />
            </Link>

            <div>
                <h1 class="text-2xl font-semibold text-foreground">
                    Tambah Rate Card
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Tambahkan informasi harga kerja sama untuk
                    {{ creator.name }} (@{{ creator.username }})
                </p>
            </div>
        </div>

        <!-- Form -->
        <div
            class="w-full rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <form
                @submit.prevent="submit"
                class="space-y-6"
            >
                <!-- Basic Information -->
                <div>
                    <h2 class="text-lg font-semibold text-foreground">
                        Informasi Rate Card
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Tentukan platform dan jenis deliverable yang ditawarkan
                        oleh creator.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <!-- Platform -->
                    <div class="space-y-2">
                        <Label for="platform">
                            Platform
                        </Label>

                        <Input
                            id="platform"
                            v-model="form.platform"
                            placeholder="Contoh: TikTok"
                        />

                        <p
                            v-if="form.errors.platform"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.platform }}
                        </p>
                    </div>

                    <!-- Deliverable -->
                    <div class="space-y-2">
                        <Label for="deliverable">
                            Deliverable
                        </Label>

                        <Input
                            id="deliverable"
                            v-model="form.deliverable"
                            placeholder="Contoh: Video TikTok"
                        />

                        <p
                            v-if="form.errors.deliverable"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.deliverable }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="space-y-2">
                        <Label for="price">
                            Harga
                        </Label>

                        <Input
                            id="price"
                            type="number"
                            min="0"
                            step="0.01"
                            v-model="form.price"
                            placeholder="Contoh: 1500000"
                        />

                        <p class="text-xs text-muted-foreground">
                            Masukkan harga dalam Rupiah tanpa tanda titik atau
                            simbol Rp.
                        </p>

                        <p
                            v-if="form.errors.price"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.price }}
                        </p>
                    </div>

                    <!-- Duration -->
                    <div class="space-y-2">
                        <Label for="duration">
                            Durasi
                        </Label>

                        <Input
                            id="duration"
                            v-model="form.duration"
                            placeholder="Contoh: 30-60 detik"
                        />

                        <p class="text-xs text-muted-foreground">
                            Opsional. Isi durasi konten jika berlaku.
                        </p>

                        <p
                            v-if="form.errors.duration"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.duration }}
                        </p>
                    </div>

                    <!-- Revision -->
                    <div class="space-y-2">
                        <Label for="revision">
                            Jumlah Revisi
                        </Label>

                        <Input
                            id="revision"
                            type="number"
                            min="0"
                            v-model="form.revision"
                            placeholder="Contoh: 2"
                        />

                        <p class="text-xs text-muted-foreground">
                            Jumlah revisi yang termasuk dalam harga.
                        </p>

                        <p
                            v-if="form.errors.revision"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.revision }}
                        </p>
                    </div>

                    <!-- Valid Until -->
                    <div class="space-y-2">
                        <Label for="valid_until">
                            Berlaku Sampai
                        </Label>

                        <Input
                            id="valid_until"
                            type="date"
                            v-model="form.valid_until"
                        />

                        <p class="text-xs text-muted-foreground">
                            Opsional. Kosongkan jika rate card tidak memiliki
                            tanggal kedaluwarsa.
                        </p>

                        <p
                            v-if="form.errors.valid_until"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.valid_until }}
                        </p>
                    </div>

                    <!-- Usage Rights -->
                    <div class="space-y-2 sm:col-span-2">
                        <Label for="usage_rights">
                            Usage Rights
                        </Label>

                        <Input
                            id="usage_rights"
                            v-model="form.usage_rights"
                            placeholder="Contoh: Organic only / Ads 30 hari"
                        />

                        <p class="text-xs text-muted-foreground">
                            Opsional. Jelaskan hak penggunaan konten oleh
                            brand.
                        </p>

                        <p
                            v-if="form.errors.usage_rights"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.usage_rights }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2 sm:col-span-2">
                        <Label for="notes">
                            Catatan
                        </Label>

                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Contoh: Harga dapat berubah berdasarkan campaign dan kebutuhan brand."
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground shadow-sm outline-none transition placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        ></textarea>

                        <p class="text-xs text-muted-foreground">
                            Opsional. Tambahkan informasi tambahan mengenai
                            rate card.
                        </p>

                        <p
                            v-if="form.errors.notes"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex justify-end gap-3 border-t border-border pt-6"
                >
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                    >
                        Batal
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save class="size-4" />

                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Rate Card'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>