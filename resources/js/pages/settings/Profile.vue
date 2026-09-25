<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import {
    BadgeCheck,
    Building2,
    Camera,
    CheckCircle2,
    Mail,
    ShieldCheck,
    Store,
    UserRound,
} from '@lucide/vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user as {
    id?: number;
    name: string;
    email: string;
    business_name?: string | null;
    profile_photo?: string | null;
    role?: string | null;
    email_verified_at?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
});

const previewPhoto = ref<string | null>(null);

const handlePhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (!input.files?.length) {
        if (previewPhoto.value) {
            URL.revokeObjectURL(previewPhoto.value);
        }

        previewPhoto.value = null;
        return;
    }

    const file = input.files[0];

    if (previewPhoto.value) {
        URL.revokeObjectURL(previewPhoto.value);
    }

    previewPhoto.value = URL.createObjectURL(file);
};

onBeforeUnmount(() => {
    if (previewPhoto.value) {
        URL.revokeObjectURL(previewPhoto.value);
    }
});

const initials = computed(() => {
    return (
        user.value.name
            ?.trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((word) => word.charAt(0).toUpperCase())
            .join('') || 'U'
    );
});

const accountStatus = computed(() => {
    return user.value.email_verified_at
        ? 'Akun aktif'
        : 'Email belum diverifikasi';
});

const accountStatusClass = computed(() => {
    return user.value.email_verified_at
        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
        : 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
});

const formattedCreatedAt = computed(() => {
    if (!user.value.created_at) {
        return 'Informasi tidak tersedia';
    }

    const date = new Date(user.value.created_at);

    if (Number.isNaN(date.getTime())) {
        return 'Informasi tidak tersedia';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
});
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <!-- HEADER -->
        <Heading
            variant="small"
            title="Profile"
            description="Kelola identitas akun, informasi bisnis, dan keamanan profil kamu."
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <!-- PROFILE HERO -->
            <section
                class="relative overflow-hidden rounded-2xl border border-border bg-card/80 p-6 shadow-sm backdrop-blur-sm"
            >
                <!-- Glow -->
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-56 w-56 rounded-full bg-sky-500/10 blur-3xl"
                />

                <div
                    class="pointer-events-none absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-center gap-5">
                        <!-- Avatar -->
                        <div class="relative shrink-0">
                            <div
                                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-border bg-muted shadow-sm"
                            >
                                <img
                                    v-if="previewPhoto || user.profile_photo"
                                    :src="
                                        previewPhoto ||
                                        `/storage/${user.profile_photo}`
                                    "
                                    alt="Foto profil"
                                    class="h-full w-full object-cover"
                                />

                                <span
                                    v-else
                                    class="text-2xl font-bold text-muted-foreground"
                                >
                                    {{ initials }}
                                </span>
                            </div>

                            <label
                                for="profile_photo"
                                class="absolute -bottom-2 -right-2 flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-border bg-background text-muted-foreground shadow-md transition hover:bg-muted hover:text-foreground"
                                title="Ganti foto profil"
                            >
                                <Camera class="size-4" />
                            </label>
                        </div>

                        <!-- Identity -->
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="text-xl font-semibold tracking-tight text-foreground"
                                >
                                    {{ user.name }}
                                </h2>

                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="accountStatusClass"
                                >
                                    <CheckCircle2
                                        v-if="user.email_verified_at"
                                        class="size-3.5"
                                    />
                                    {{ accountStatus }}
                                </span>
                            </div>

                            <p
                                class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground"
                            >
                                <Store class="size-3.5" />

                                {{
                                    user.business_name ||
                                    'Nama bisnis belum diatur'
                                }}
                            </p>

                            <p
                                class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground"
                            >
                                <Mail class="size-3.5" />

                                {{ user.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Role -->
                    <div
                        class="flex items-center gap-3 rounded-xl border border-border bg-background/70 px-4 py-3"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400"
                        >
                            <BadgeCheck class="size-5" />
                        </div>

                        <div>
                            <p
                                class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground"
                            >
                                Account Role
                            </p>

                            <p class="mt-0.5 text-sm font-semibold">
                                {{
                                    user.role
                                        ? user.role.charAt(0).toUpperCase() +
                                          user.role.slice(1)
                                        : 'User'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Hidden-ish file input -->
                <div>
                    <input
                        id="profile_photo"
                        type="file"
                        name="profile_photo"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="handlePhotoChange"
                    />

                    <InputError
                        class="mt-1"
                        :message="errors.profile_photo"
                    />
                </div>
            </section>

            <!-- INFORMATION GRID -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- PERSONAL INFORMATION -->
                <section
                    class="rounded-2xl border border-border bg-card/80 p-6 shadow-sm backdrop-blur-sm"
                >
                    <div class="mb-6 flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400"
                        >
                            <UserRound class="size-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold text-foreground">
                                Informasi Personal
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Informasi dasar akun kamu.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <!-- Name -->
                        <div class="grid gap-2">
                            <Label for="name">Nama</Label>

                            <Input
                                id="name"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Nama lengkap"
                            />

                            <InputError
                                :message="errors.name"
                            />
                        </div>

                        <!-- Email -->
                        <div class="grid gap-2">
                            <Label for="email">
                                Alamat Email
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="Alamat email"
                            />

                            <InputError
                                :message="errors.email"
                            />
                        </div>
                    </div>
                </section>

                <!-- BUSINESS INFORMATION -->
                <section
                    class="rounded-2xl border border-border bg-card/80 p-6 shadow-sm backdrop-blur-sm"
                >
                    <div class="mb-6 flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400"
                        >
                            <Building2 class="size-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold text-foreground">
                                Informasi Bisnis
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Identitas bisnis atau store yang digunakan.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="grid gap-2">
                            <Label for="business_name">
                                Nama Bisnis / Store
                            </Label>

                            <Input
                                id="business_name"
                                name="business_name"
                                :default-value="
                                    user.business_name ?? ''
                                "
                                autocomplete="organization"
                                placeholder="Contoh: Rama Store"
                            />

                            <InputError
                                :message="errors.business_name"
                            />
                        </div>

                        <div
                            class="rounded-xl border border-border bg-muted/30 p-4"
                        >
                            <div class="flex items-center gap-3">
                                <Store
                                    class="size-4 text-muted-foreground"
                                />

                                <div>
                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        Store / Business
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium"
                                    >
                                        {{
                                            user.business_name ||
                                            'Belum diatur'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ACCOUNT OVERVIEW -->
            <section
                class="rounded-2xl border border-border bg-card/80 p-6 shadow-sm backdrop-blur-sm"
            >
                <div class="mb-6 flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                    >
                        <ShieldCheck class="size-5" />
                    </div>

                    <div>
                        <h2 class="font-semibold text-foreground">
                            Account Overview
                        </h2>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Ringkasan status akun kamu.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-xl border border-border bg-background/60 p-4"
                    >
                        <p
                            class="text-xs font-medium uppercase tracking-wider text-muted-foreground"
                        >
                            Status
                        </p>

                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full"
                                :class="
                                    user.email_verified_at
                                        ? 'bg-emerald-500'
                                        : 'bg-amber-500'
                                "
                            />

                            <span
                                class="text-sm font-semibold"
                            >
                                {{ accountStatus }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-background/60 p-4"
                    >
                        <p
                            class="text-xs font-medium uppercase tracking-wider text-muted-foreground"
                        >
                            Email
                        </p>

                        <p
                            class="mt-2 truncate text-sm font-semibold"
                        >
                            {{ user.email }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-border bg-background/60 p-4"
                    >
                        <p
                            class="text-xs font-medium uppercase tracking-wider text-muted-foreground"
                        >
                            Bergabung
                        </p>

                        <p class="mt-2 text-sm font-semibold">
                            {{ formattedCreatedAt }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- SAVE BAR -->
            <div
                class="flex flex-col gap-3 rounded-2xl border border-sky-500/20 bg-sky-500/5 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-medium">
                        Ada perubahan pada profil?
                    </p>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Simpan perubahan untuk memperbarui informasi akun.
                    </p>
                </div>

                <Button
                    :disabled="processing"
                    data-test="update-profile-button"
                    class="shrink-0"
                >
                    {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </Button>
            </div>
        </Form>

        <!-- DELETE ACCOUNT -->
        <DeleteUser />
    </div>
</template>