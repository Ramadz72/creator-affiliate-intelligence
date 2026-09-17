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
    content_date: '',
    content_url: '',
    content_type: '',
    views: 0,
    likes: 0,
    comments: 0,
    shares: 0,
});

const submit = () => {
    form.post(`/creators/${props.creator.id}/contents`);
};
</script>

<template>
    <Head :title="`Tambah Konten - ${creator.name}`" />

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
                    Tambah Konten
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Tambahkan data performa konten untuk {{ creator.name }}
                    (@{{ creator.username }})
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
                        Informasi Konten
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Data dasar dari konten yang ingin dianalisis.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <!-- Content Date -->
                    <div class="space-y-2">
                        <Label for="content_date">
                            Tanggal Konten
                        </Label>

                        <Input
                            id="content_date"
                            type="date"
                            v-model="form.content_date"
                        />

                        <p
                            v-if="form.errors.content_date"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.content_date }}
                        </p>
                    </div>

                    <!-- Content Type -->
                    <div class="space-y-2">
                        <Label for="content_type">
                            Jenis Konten
                        </Label>

                        <Input
                            id="content_type"
                            v-model="form.content_type"
                            placeholder="Contoh: Video"
                        />

                        <p
                            v-if="form.errors.content_type"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.content_type }}
                        </p>
                    </div>

                    <!-- Content URL -->
                    <div class="space-y-2 sm:col-span-2">
                        <Label for="content_url">
                            URL Konten
                        </Label>

                        <Input
                            id="content_url"
                            type="url"
                            v-model="form.content_url"
                            placeholder="https://www.tiktok.com/..."
                        />

                        <p class="text-xs text-muted-foreground">
                            Opsional. Bisa diisi link TikTok, Instagram, atau
                            platform lainnya.
                        </p>

                        <p
                            v-if="form.errors.content_url"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.content_url }}
                        </p>
                    </div>
                </div>

                <!-- Performance -->
                <div class="border-t border-border pt-6">
                    <h2 class="text-lg font-semibold text-foreground">
                        Performa Konten
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Masukkan angka performa dari konten tersebut.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <!-- Views -->
                    <div class="space-y-2">
                        <Label for="views">
                            Views
                        </Label>

                        <Input
                            id="views"
                            type="number"
                            min="0"
                            v-model="form.views"
                        />

                        <p
                            v-if="form.errors.views"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.views }}
                        </p>
                    </div>

                    <!-- Likes -->
                    <div class="space-y-2">
                        <Label for="likes">
                            Likes
                        </Label>

                        <Input
                            id="likes"
                            type="number"
                            min="0"
                            v-model="form.likes"
                        />

                        <p
                            v-if="form.errors.likes"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.likes }}
                        </p>
                    </div>

                    <!-- Comments -->
                    <div class="space-y-2">
                        <Label for="comments">
                            Comments
                        </Label>

                        <Input
                            id="comments"
                            type="number"
                            min="0"
                            v-model="form.comments"
                        />

                        <p
                            v-if="form.errors.comments"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.comments }}
                        </p>
                    </div>

                    <!-- Shares -->
                    <div class="space-y-2">
                        <Label for="shares">
                            Shares
                        </Label>

                        <Input
                            id="shares"
                            type="number"
                            min="0"
                            v-model="form.shares"
                        />

                        <p
                            v-if="form.errors.shares"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.shares }}
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
                                : 'Simpan Konten'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>