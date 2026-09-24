<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload } from '@lucide/vue';
import { ref } from 'vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    audience_gender: string[] | null;
    audience_age: string[] | null;
    audience_location: string[] | null;
    profile_link: string | null;
    profile_image: string | null;
    status: 'active' | 'inactive';
    notes: string | null;
}

const props = defineProps<{
    creator: Creator;
}>();

const form = useForm({
    name: props.creator.name,
    username: props.creator.username,
    platform: props.creator.platform,
    category: props.creator.category,
    followers: props.creator.followers,
    audience_gender: props.creator.audience_gender?.[0] ?? '',
    audience_age: props.creator.audience_age?.[0] ?? '',
    audience_location: props.creator.audience_location?.[0] ?? '',
    profile_link: props.creator.profile_link ?? '',
    profile_image: null as File | null,
    status: props.creator.status,
    notes: props.creator.notes ?? '',
});
const previewImage = ref<string | null>(
    props.creator.profile_image
        ? `/storage/${props.creator.profile_image}`
        : null,
);

const handleImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    form.profile_image = file;

    if (file) {
        previewImage.value = URL.createObjectURL(file);
    }
};
const submit = () => {
    form.transform((data) => ({
    ...data,
    _method: 'put',
})).post(`/creators/${props.creator.id}`, {
    forceFormData: true,
});
};

</script>

<template>
    <Head :title="`Edit - ${creator.name}`" />

    <div class="app-textured-bg flex h-full flex-1 flex-col gap-6 p-6">
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
                    Edit Creator
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Perbarui informasi Creator / KOL.
                </p>
            </div>
        </div>

        <!-- Form -->
        <form
            @submit.prevent="submit"
            class="rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Name -->
                <div>
                    <label
                        for="name"
                        class="text-sm font-medium text-foreground"
                    >
                        Creator Name
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Username -->
                <div>
                    <label
                        for="username"
                        class="text-sm font-medium text-foreground"
                    >
                        Username
                    </label>

                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />

                    <p
                        v-if="form.errors.username"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ form.errors.username }}
                    </p>
                </div>

                <!-- Platform -->
                <div>
                    <label
                        for="platform"
                        class="text-sm font-medium text-foreground"
                    >
                        Platform
                    </label>

                    <select
                        id="platform"
                        v-model="form.platform"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    >
                        <option value="TikTok">TikTok</option>
                        <option value="Instagram">Instagram</option>
                        <option value="YouTube">YouTube</option>
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label
                        for="category"
                        class="text-sm font-medium text-foreground"
                    >
                        Category
                    </label>

                    <input
                        id="category"
                        v-model="form.category"
                        type="text"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>

                <!-- Followers -->
                <div>
                    <label
                        for="followers"
                        class="text-sm font-medium text-foreground"
                    >
                        Followers
                    </label>

                    <input
                        id="followers"
                        v-model="form.followers"
                        type="number"
                        min="0"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>

                <!-- Status -->
                <div>
                    <label
                        for="status"
                        class="text-sm font-medium text-foreground"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Gender -->
                <div>
                    <label
                        for="audience_gender"
                        class="text-sm font-medium text-foreground"
                    >
                        Audience Gender
                    </label>

                    <input
                        id="audience_gender"
                        v-model="form.audience_gender"
                        type="text"
                        placeholder="Contoh: Female"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>

                <!-- Age -->
                <div>
                    <label
                        for="audience_age"
                        class="text-sm font-medium text-foreground"
                    >
                        Audience Age
                    </label>

                    <input
                        id="audience_age"
                        v-model="form.audience_age"
                        type="text"
                        placeholder="Contoh: 18-24"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>

                <!-- Location -->
                <div class="md:col-span-2">
                    <label
                        for="audience_location"
                        class="text-sm font-medium text-foreground"
                    >
                        Audience Location
                    </label>

                    <input
                        id="audience_location"
                        v-model="form.audience_location"
                        type="text"
                        placeholder="Contoh: Indonesia"
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>

                <!-- Profile Link -->
                <div class="md:col-span-2">
                    <label
                        for="profile_link"
                        class="text-sm font-medium text-foreground"
                    >
                        Profile Link
                    </label>

                    <input
                        id="profile_link"
                        v-model="form.profile_link"
                        type="url"
                        placeholder="https://..."
                        class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                    />
                </div>
                <!-- Profile Image -->
                 <div class="space-y-3">
                    <Label>Foto Profil</Label>

                    <div class="flex items-center gap-4">
                        <div
                            v-if="previewImage"
                            class="shrink-0"
                        >
                            <img
                                :src="previewImage"
                                alt="Preview foto profil"
                                class="h-20 w-20 rounded-full border border-border object-cover"
                            />
                        </div>

                        <div>
                            <input
                                id="profile_image"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleImageChange"
                            />

                            <label
                                for="profile_image"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-border bg-background px-4 py-2.5 text-sm font-medium text-foreground shadow-sm transition hover:bg-muted"
                            >
                                <Upload class="h-4 w-4" />
                                Ganti Foto
                            </label>

                            <p class="mt-2 text-sm text-muted-foreground">
                                JPG, PNG, atau WEBP. Maksimal 2 MB.
                            </p>
                        </div>
                    </div>

                    <p
                        v-if="form.errors.profile_image"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.profile_image }}
                    </p>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label
                        for="notes"
                        class="text-sm font-medium text-foreground"
                    >
                        Notes
                    </label>

                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="5"
                        class="mt-2 w-full resize-none rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none transition focus:border-primary"
                        placeholder="Catatan mengenai creator..."
                    ></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div
                class="mt-8 flex items-center justify-end gap-3 border-t border-border pt-6"
            >
                <Link
                    :href="`/creators/${creator.id}`"
                    class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Save class="size-4" />

                    {{
                        form.processing
                            ? 'Saving...'
                            : 'Save Changes'
                    }}
                </button>
            </div>
        </form>
    </div>
</template>