<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    platform: '',
    category: '',
    followers: 0,
    audience_gender: [] as string[],
    audience_age: [] as string[],
    audience_location: [] as string[],
    profile_link: '',
    status: 'active',
    notes: '',
});

const submit = () => {
    form.post('/creators');
};
</script>

<template>
    <Head title="Add Creator" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">

        <!-- Header -->
        <div>
            <Link
                href="/creators"
                class="text-sm text-muted-foreground hover:text-foreground"
            >
                ← Back to Creator
            </Link>

            <h1 class="mt-3 text-2xl font-semibold tracking-tight">
                Add Creator
            </h1>

            <p class="text-sm text-muted-foreground">
                Tambahkan data Creator/KOL untuk dianalisis.
            </p>
        </div>


        <!-- Form -->
        <form
            @submit.prevent="submit"
            class="max-w-4xl space-y-6"
        >

            <!-- Basic Information -->
            <div class="rounded-xl border border-border bg-card p-6">

                <div class="mb-6">
                    <h2 class="text-base font-semibold">
                        Basic Information
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Informasi utama mengenai Creator.
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Creator Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Sarah Putri"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />

                        <p
                            v-if="form.errors.name"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>


                    <!-- Username -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Username
                        </label>

                        <input
                            v-model="form.username"
                            type="text"
                            placeholder="@username"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />

                        <p
                            v-if="form.errors.username"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.username }}
                        </p>
                    </div>


                    <!-- Platform -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Platform
                        </label>

                        <select
                            v-model="form.platform"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        >
                            <option value="" disabled>
                                Select platform
                            </option>

                            <option value="TikTok">
                                TikTok
                            </option>

                            <option value="Instagram">
                                Instagram
                            </option>

                            <option value="YouTube">
                                YouTube
                            </option>
                        </select>

                        <p
                            v-if="form.errors.platform"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.platform }}
                        </p>
                    </div>


                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Category
                        </label>

                        <input
                            v-model="form.category"
                            type="text"
                            placeholder="Contoh: Beauty, Fashion, Gaming"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />

                        <p
                            v-if="form.errors.category"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.category }}
                        </p>
                    </div>


                    <!-- Followers -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Followers
                        </label>

                        <input
                            v-model.number="form.followers"
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />

                        <p
                            v-if="form.errors.followers"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.followers }}
                        </p>
                    </div>


                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Status
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        >
                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>
                        </select>
                    </div>

                </div>
            </div>


            <!-- Audience -->
            <div class="rounded-xl border border-border bg-card p-6">

                <div class="mb-6">
                    <h2 class="text-base font-semibold">
                        Audience
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Data audience dapat digunakan untuk analisis
                        kesesuaian Creator dengan campaign.
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Gender
                        </label>

                        <input
                            v-model="form.audience_gender[0]"
                            type="text"
                            placeholder="Contoh: Female 70%"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                    </div>


                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Age
                        </label>

                        <input
                            v-model="form.audience_age[0]"
                            type="text"
                            placeholder="Contoh: 18-24 60%"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                    </div>


                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium">
                            Location
                        </label>

                        <input
                            v-model="form.audience_location[0]"
                            type="text"
                            placeholder="Contoh: Indonesia 90%"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                    </div>

                </div>
            </div>


            <!-- Additional Information -->
            <div class="rounded-xl border border-border bg-card p-6">

                <div class="mb-6">
                    <h2 class="text-base font-semibold">
                        Additional Information
                    </h2>
                </div>

                <div class="space-y-5">

                    <!-- Profile Link -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Profile Link
                        </label>

                        <input
                            v-model="form.profile_link"
                            type="url"
                            placeholder="https://..."
                            class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                    </div>


                    <!-- Notes -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Notes
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Catatan tambahan mengenai Creator..."
                            class="w-full resize-none rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                    </div>

                </div>
            </div>


            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">

                <Link
                    href="/creators"
                    class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium transition hover:bg-muted"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Save Creator' }}
                </button>

            </div>

        </form>

    </div>
</template>