<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Check, Monitor, Moon, Palette, Sun } from '@lucide/vue';

import { useAppearance } from '@/composables/useAppearance';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});

const themes = [
    {
        value: 'light',
        label: 'Light',
        description: 'Gunakan tampilan terang.',
        Icon: Sun,
    },
    {
        value: 'dark',
        label: 'Dark',
        description: 'Gunakan tampilan gelap.',
        Icon: Moon,
    },
    {
        value: 'system',
        label: 'System',
        description: 'Ikuti pengaturan perangkat.',
        Icon: Monitor,
    },
] as const;

const currentThemeLabel = computed(() => {
    if (appearance.value === 'system') {
        return `System · ${resolvedAppearance.value === 'dark' ? 'Dark' : 'Light'}`;
    }

    return resolvedAppearance.value === 'dark' ? 'Dark mode' : 'Light mode';
});

const currentThemeDescription = computed(() => {
    if (appearance.value === 'system') {
        return 'Tampilan mengikuti preferensi tema perangkat Anda.';
    }

    return appearance.value === 'dark'
        ? 'Tampilan gelap sedang digunakan.'
        : 'Tampilan terang sedang digunakan.';
});
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-8">
        <!-- Header -->
        <Heading
            variant="small"
            title="Appearance settings"
            description="Sesuaikan tampilan aplikasi agar nyaman digunakan sesuai preferensi Anda."
        />

        <!-- Current Appearance -->
        <div
            class="border-border bg-card relative overflow-hidden rounded-2xl border"
        >
            <div
                class="bg-primary/10 absolute -top-20 -right-20 h-48 w-48 rounded-full blur-3xl"
            />

            <div
                class="relative flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="bg-primary/10 text-primary flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                    >
                        <Palette class="h-5 w-5" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-foreground font-semibold">
                                Current appearance
                            </h2>

                            <span
                                class="bg-primary/10 text-primary inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium"
                            >
                                Active
                            </span>
                        </div>

                        <p class="text-foreground mt-1 text-sm font-medium">
                            {{ currentThemeLabel }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-xs">
                            {{ currentThemeDescription }}
                        </p>
                    </div>
                </div>

                <div
                    class="border-border bg-muted/30 rounded-xl border px-4 py-3"
                >
                    <p
                        class="text-muted-foreground text-[11px] tracking-wider uppercase"
                    >
                        Preference
                    </p>

                    <p class="text-foreground mt-1 text-sm font-medium">
                        {{
                            appearance === 'system'
                                ? 'System controlled'
                                : 'Manually selected'
                        }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Theme Selection -->
        <section class="space-y-4">
            <div>
                <h2 class="text-foreground text-base font-semibold">Theme</h2>

                <p class="text-muted-foreground mt-1 text-sm">
                    Pilih bagaimana aplikasi menampilkan warna dan kontras.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <button
                    v-for="theme in themes"
                    :key="theme.value"
                    type="button"
                    @click="updateAppearance(theme.value)"
                    class="group relative overflow-hidden rounded-2xl border text-left transition-all duration-200"
                    :class="
                        appearance === theme.value
                            ? 'border-primary ring-primary/20 ring-2'
                            : 'border-border hover:border-primary/40 hover:shadow-sm'
                    "
                >
                    <!-- Theme Preview -->
                    <div
                        class="border-border h-32 overflow-hidden border-b p-3"
                        :class="
                            theme.value === 'dark'
                                ? 'bg-slate-950'
                                : theme.value === 'light'
                                  ? 'bg-slate-100'
                                  : 'bg-gradient-to-br from-slate-100 to-slate-950'
                        "
                    >
                        <!-- Light -->
                        <div
                            v-if="theme.value === 'light'"
                            class="h-full rounded-lg border border-slate-200 bg-white p-3 shadow-sm"
                        >
                            <div class="flex gap-2">
                                <div class="h-2 w-12 rounded bg-slate-200" />
                                <div class="h-2 w-7 rounded bg-blue-500" />
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="h-10 rounded bg-slate-100" />
                                <div class="h-10 rounded bg-slate-100" />
                                <div class="h-10 rounded bg-slate-100" />
                            </div>
                        </div>

                        <!-- Dark -->
                        <div
                            v-else-if="theme.value === 'dark'"
                            class="h-full rounded-lg border border-slate-800 bg-slate-900 p-3 shadow-sm"
                        >
                            <div class="flex gap-2">
                                <div class="h-2 w-12 rounded bg-slate-700" />
                                <div class="h-2 w-7 rounded bg-blue-500" />
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="h-10 rounded bg-slate-800" />
                                <div class="h-10 rounded bg-slate-800" />
                                <div class="h-10 rounded bg-slate-800" />
                            </div>
                        </div>

                        <!-- System -->
                        <div
                            v-else
                            class="flex h-full overflow-hidden rounded-lg border border-slate-300 shadow-sm"
                        >
                            <div class="w-1/2 bg-white p-3">
                                <div class="h-2 w-10 rounded bg-slate-200" />

                                <div class="mt-4 space-y-2">
                                    <div class="h-7 rounded bg-slate-100" />
                                    <div class="h-7 rounded bg-slate-100" />
                                </div>
                            </div>

                            <div class="w-1/2 bg-slate-900 p-3">
                                <div class="h-2 w-10 rounded bg-slate-700" />

                                <div class="mt-4 space-y-2">
                                    <div class="h-7 rounded bg-slate-800" />
                                    <div class="h-7 rounded bg-slate-800" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Theme Info -->
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="bg-muted flex h-8 w-8 items-center justify-center rounded-lg"
                                >
                                    <component
                                        :is="theme.Icon"
                                        class="text-foreground h-4 w-4"
                                    />
                                </div>

                                <div>
                                    <p class="text-sm font-semibold">
                                        {{ theme.label }}
                                    </p>

                                    <p class="text-muted-foreground text-xs">
                                        {{ theme.description }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="appearance === theme.value"
                                class="bg-primary text-primary-foreground flex h-6 w-6 items-center justify-center rounded-full"
                            >
                                <Check class="h-3.5 w-3.5" />
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </section>

        <!-- Live Interface Preview -->
        <section class="space-y-4">
            <div>
                <h2 class="text-foreground text-base font-semibold">
                    Interface preview
                </h2>

                <p class="text-muted-foreground mt-1 text-sm">
                    Preview sederhana tampilan dashboard berdasarkan tema yang
                    Anda pilih.
                </p>
            </div>

            <div
                class="border-border overflow-hidden rounded-2xl border shadow-sm"
                :class="
                    resolvedAppearance === 'dark'
                        ? 'bg-slate-950'
                        : 'bg-slate-50'
                "
            >
                <!-- Preview Header -->
                <div
                    class="flex items-center justify-between border-b p-4"
                    :class="
                        resolvedAppearance === 'dark'
                            ? 'border-slate-800'
                            : 'border-slate-200'
                    "
                >
                    <div class="flex items-center gap-3">
                        <div class="bg-primary h-8 w-8 rounded-lg" />

                        <div>
                            <div
                                class="h-2.5 w-28 rounded"
                                :class="
                                    resolvedAppearance === 'dark'
                                        ? 'bg-slate-700'
                                        : 'bg-slate-300'
                                "
                            />

                            <div
                                class="mt-1.5 h-2 w-16 rounded"
                                :class="
                                    resolvedAppearance === 'dark'
                                        ? 'bg-slate-800'
                                        : 'bg-slate-200'
                                "
                            />
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <div
                            class="h-7 w-7 rounded-full"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-slate-800'
                                    : 'border border-slate-200 bg-white'
                            "
                        />

                        <div
                            class="h-7 w-16 rounded-lg"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-blue-600'
                                    : 'bg-blue-500'
                            "
                        />
                    </div>
                </div>

                <!-- Preview Content -->
                <div class="grid gap-3 p-4 sm:grid-cols-3">
                    <div
                        v-for="item in 3"
                        :key="item"
                        class="rounded-xl border p-4"
                        :class="
                            resolvedAppearance === 'dark'
                                ? 'border-slate-800 bg-slate-900'
                                : 'border-slate-200 bg-white'
                        "
                    >
                        <div
                            class="h-2.5 w-16 rounded"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-slate-700'
                                    : 'bg-slate-200'
                            "
                        />

                        <div
                            class="mt-4 h-6 w-24 rounded"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-slate-600'
                                    : 'bg-slate-300'
                            "
                        />

                        <div
                            class="mt-3 h-2 w-full rounded"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-slate-800'
                                    : 'bg-slate-100'
                            "
                        />

                        <div
                            class="mt-2 h-2 w-3/4 rounded"
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'bg-slate-800'
                                    : 'bg-slate-100'
                            "
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Info -->
        <div class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-5">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400"
                >
                    <Monitor class="h-4 w-4" />
                </div>

                <div>
                    <h3 class="text-foreground text-sm font-semibold">
                        Tentang System theme
                    </h3>

                    <p class="text-muted-foreground mt-1 text-xs leading-5">
                        Jika memilih System, aplikasi akan mengikuti preferensi
                        tema perangkat Anda. Perubahan tema dari sistem operasi
                        juga akan diterapkan secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
