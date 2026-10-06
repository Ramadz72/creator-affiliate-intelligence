<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowLeft,
    ArrowRight,
    Check,
    ChevronLeft,
    ExternalLink,
    ImagePlus,
    Camera,
    Link as LinkIcon,
    Save,
    User,
    Users,
    X,
    Play,
} from '@lucide/vue'

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
    profile_image: null as File | null,
    status: 'active',
    notes: '',
})

const currentStep = ref(1)
const previewImage = ref<string | null>(null)

const steps = [
    {
        number: 1,
        title: 'Profil',
        description: 'Informasi utama',
    },
    {
        number: 2,
        title: 'Audience',
        description: 'Karakteristik audience',
    },
    {
        number: 3,
        title: 'Detail',
        description: 'Informasi tambahan',
    },
    {
        number: 4,
        title: 'Review',
        description: 'Periksa data',
    },
]

const platforms = [
    {
        value: 'TikTok',
        label: 'TikTok',
        icon: Users,
    },
    {
        value: 'Instagram',
        label: 'Instagram',
        icon: Camera,
    },
    {
        value: 'YouTube',
        label: 'YouTube',
        icon: Play,
    },
]

const profileInitial = computed(() => {
    if (!form.name.trim()) {
        return '?'
    }

    return form.name.trim().charAt(0).toUpperCase()
})

const formattedFollowers = computed(() => {
    return new Intl.NumberFormat('id-ID').format(
        Number(form.followers || 0),
    )
})

const shortFollowers = computed(() => {
    const value = Number(form.followers || 0)

    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(
            value % 1_000_000 === 0 ? 0 : 1,
        )}M`
    }

    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(
            value % 1_000 === 0 ? 0 : 1,
        )}K`
    }

    return value.toLocaleString('id-ID')
})

const completion = computed(() => {
    const fields = [
        form.name,
        form.username,
        form.platform,
        form.category,
        form.followers > 0,
        form.audience_gender[0],
        form.audience_age[0],
        form.audience_location[0],
        form.profile_link,
        form.profile_image,
        form.notes,
    ]

    const completed = fields.filter((field) => {
        if (typeof field === 'boolean') {
            return field
        }

        return Boolean(field)
    }).length

    return Math.round((completed / fields.length) * 100)
})

const usernameDisplay = computed(() => {
    if (!form.username) {
        return '@username'
    }

    return form.username.startsWith('@')
        ? form.username
        : `@${form.username}`
})

const handleUsernameInput = () => {
    form.username = form.username.replace(/\s/g, '')
}

const handleFollowersInput = () => {
    const value = Number(form.followers)

    if (!Number.isFinite(value) || value < 0) {
        form.followers = 0
    }
}

const handleImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0] ?? null

    form.profile_image = file

    if (previewImage.value) {
        URL.revokeObjectURL(previewImage.value)
    }

    if (file) {
        previewImage.value = URL.createObjectURL(file)
    } else {
        previewImage.value = null
    }
}

const removeImage = () => {
    form.profile_image = null

    if (previewImage.value) {
        URL.revokeObjectURL(previewImage.value)
    }

    previewImage.value = null

    const input = document.getElementById(
        'profile_image',
    ) as HTMLInputElement | null

    if (input) {
        input.value = ''
    }
}

const setAudience = (
    field: 'audience_gender' | 'audience_age' | 'audience_location',
    value: string,
) => {
    form[field][0] = value
}

const canContinueFromProfile = computed(() => {
    return Boolean(
        form.name.trim() &&
            form.username.trim() &&
            form.platform &&
            form.category.trim(),
    )
})

const nextStep = () => {
    if (currentStep.value === 1 && !canContinueFromProfile.value) {
        return
    }

    if (currentStep.value < 4) {
        currentStep.value++
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        })
    }
}

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        })
    }
}

const goToStep = (step: number) => {
    if (step < currentStep.value) {
        currentStep.value = step
    }
}

const submit = () => {
    form.post('/creators', {
        forceFormData: true,
    })
}

const fieldClass = (
    field: keyof typeof form.errors,
) => {
    return form.errors[field]
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/10'
        : 'border-border focus:border-primary focus:ring-primary/10'
}
</script>

<template>
    <Head title="Tambah Creator" />

    <div
        class="app-textured-bg min-h-full p-4 text-foreground md:p-6"
    >
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-start gap-4">
                <Link
                    href="/creators"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight"
                    >
                        Tambah Creator
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Tambahkan creator atau KOL untuk kebutuhan
                        analisis kolaborasi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stepper -->
        <div
            class="mb-6 rounded-xl border border-border bg-card px-5 py-4 shadow-sm"
        >
            <div class="flex items-center">
                <template
                    v-for="(step, index) in steps"
                    :key="step.number"
                >
                    <button
                        type="button"
                        class="group flex min-w-0 items-center gap-3 text-left"
                        :class="{
                            'cursor-pointer': step.number <= currentStep,
                            'cursor-default':
                                step.number > currentStep,
                        }"
                        @click="goToStep(step.number)"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border text-sm font-semibold transition"
                            :class="
                                step.number < currentStep
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : step.number === currentStep
                                      ? 'border-primary bg-primary/10 text-primary'
                                      : 'border-border bg-muted text-muted-foreground'
                            "
                        >
                            <Check
                                v-if="step.number < currentStep"
                                class="h-4 w-4"
                            />

                            <span v-else>
                                {{ step.number }}
                            </span>
                        </div>

                        <div class="hidden min-w-0 sm:block">
                            <p
                                class="truncate text-sm font-medium"
                                :class="
                                    step.number === currentStep
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ step.title }}
                            </p>

                            <p class="truncate text-xs text-muted-foreground">
                                {{ step.description }}
                            </p>
                        </div>
                    </button>

                    <div
                        v-if="index < steps.length - 1"
                        class="mx-3 h-px flex-1 bg-border sm:mx-5"
                        :class="{
                            'bg-primary/40':
                                step.number < currentStep,
                        }"
                    />
                </template>
            </div>
        </div>

        <!-- Main -->
        <div
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
        >
            <!-- Form -->
            <form
                @submit.prevent="submit"
                class="min-w-0"
            >
                <!-- STEP 1 -->
                <div
                    v-if="currentStep === 1"
                    class="space-y-5"
                >
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
                    >
                        <div class="mb-6">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-primary"
                            >
                                Step 01
                            </p>

                            <h2 class="mt-1 text-lg font-semibold">
                                Profil Creator
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Informasi utama mengenai creator atau
                                KOL.
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Creator Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Sarah Putri"
                                    :class="fieldClass('name')"
                                    class="h-11 w-full rounded-lg border bg-background px-4 text-sm outline-none transition focus:ring-2"
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
                                <label
                                    class="text-sm font-medium"
                                >
                                    Username
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground"
                                    >
                                        @
                                    </span>

                                    <input
                                        v-model="form.username"
                                        type="text"
                                        placeholder="username"
                                        :class="fieldClass('username')"
                                        class="h-11 w-full rounded-lg border bg-background pl-8 pr-4 text-sm outline-none transition focus:ring-2"
                                        @input="handleUsernameInput"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.username"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.username }}
                                </p>
                            </div>

                            <!-- Platform -->
                            <div class="space-y-2 md:col-span-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Platform
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="grid gap-3 sm:grid-cols-3">
                                    <button
                                        v-for="platform in platforms"
                                        :key="platform.value"
                                        type="button"
                                        class="flex h-12 items-center gap-3 rounded-lg border px-4 text-sm font-medium transition"
                                        :class="
                                            form.platform ===
                                            platform.value
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'border-border bg-background hover:bg-muted'
                                        "
                                        @click="
                                            form.platform =
                                                platform.value
                                        "
                                    >
                                        <component
                                            :is="platform.icon"
                                            class="h-4 w-4"
                                        />

                                        {{ platform.label }}

                                        <Check
                                            v-if="
                                                form.platform ===
                                                platform.value
                                            "
                                            class="ml-auto h-4 w-4"
                                        />
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.platform"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.platform }}
                                </p>
                            </div>

                            <!-- Category -->
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Category
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="form.category"
                                    type="text"
                                    placeholder="Beauty, Fashion, Gaming..."
                                    :class="fieldClass('category')"
                                    class="h-11 w-full rounded-lg border bg-background px-4 text-sm outline-none transition focus:ring-2"
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
                                <label
                                    class="text-sm font-medium"
                                >
                                    Followers
                                </label>

                                <input
                                    v-model.number="form.followers"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="fieldClass('followers')"
                                    class="h-11 w-full rounded-lg border bg-background px-4 text-sm outline-none transition focus:ring-2"
                                    @input="handleFollowersInput"
                                />

                                <div
                                    class="flex items-center justify-between"
                                >
                                    <p
                                        v-if="form.errors.followers"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.followers }}
                                    </p>

                                    <p
                                        v-else
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            form.followers
                                                ? `${formattedFollowers} followers`
                                                : 'Masukkan jumlah followers'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2 md:col-span-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Status
                                </label>

                                <div
                                    class="flex items-center justify-between rounded-lg border border-border bg-background px-4 py-3"
                                >
                                    <div>
                                        <p class="text-sm font-medium">
                                            Creator Active
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            Creator dapat digunakan
                                            untuk kebutuhan analisis.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="relative h-6 w-11 rounded-full transition"
                                        :class="
                                            form.status === 'active'
                                                ? 'bg-primary'
                                                : 'bg-muted'
                                        "
                                        @click="
                                            form.status =
                                                form.status ===
                                                'active'
                                                    ? 'inactive'
                                                    : 'active'
                                        "
                                    >
                                        <span
                                            class="absolute top-1 h-4 w-4 rounded-full bg-white shadow-sm transition"
                                            :class="
                                                form.status === 'active'
                                                    ? 'left-6'
                                                    : 'left-1'
                                            "
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div
                    v-else-if="currentStep === 2"
                    class="space-y-5"
                >
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
                    >
                        <div class="mb-6">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-primary"
                            >
                                Step 02
                            </p>

                            <h2 class="mt-1 text-lg font-semibold">
                                Audience
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Informasi audience membantu menilai
                                kesesuaian creator dengan campaign.
                            </p>
                        </div>

                        <div class="space-y-5">
                            <!-- Gender -->
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <label
                                        class="text-sm font-medium"
                                    >
                                        Gender
                                    </label>

                                    <span
                                        class="text-xs text-muted-foreground"
                                    >
                                        Optional
                                    </span>
                                </div>

                                <input
                                    :value="
                                        form.audience_gender[0] ?? ''
                                    "
                                    type="text"
                                    placeholder="Contoh: Female 70%"
                                    class="h-11 w-full rounded-lg border border-border bg-background px-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    @input="
                                        setAudience(
                                            'audience_gender',
                                            (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        )
                                    "
                                />

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Contoh: Female 70%, Male 30%
                                </p>
                            </div>

                            <!-- Age -->
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Age
                                </label>

                                <input
                                    :value="
                                        form.audience_age[0] ?? ''
                                    "
                                    type="text"
                                    placeholder="Contoh: 18-24 60%"
                                    class="h-11 w-full rounded-lg border border-border bg-background px-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    @input="
                                        setAudience(
                                            'audience_age',
                                            (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        )
                                    "
                                />

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Contoh: 18-24 60%, 25-34 30%
                                </p>
                            </div>

                            <!-- Location -->
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Location
                                </label>

                                <input
                                    :value="
                                        form.audience_location[0] ?? ''
                                    "
                                    type="text"
                                    placeholder="Contoh: Indonesia 90%"
                                    class="h-11 w-full rounded-lg border border-border bg-background px-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    @input="
                                        setAudience(
                                            'audience_location',
                                            (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        )
                                    "
                                />

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Contoh: Indonesia 90%, Malaysia 10%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div
                    v-else-if="currentStep === 3"
                    class="space-y-5"
                >
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
                    >
                        <div class="mb-6">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-primary"
                            >
                                Step 03
                            </p>

                            <h2 class="mt-1 text-lg font-semibold">
                                Detail Creator
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Lengkapi informasi tambahan agar
                                profile creator lebih siap dianalisis.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <!-- Profile Link -->
                            <div class="space-y-2">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Profile Link
                                </label>

                                <div class="relative">
                                    <LinkIcon
                                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                    />

                                    <input
                                        v-model="form.profile_link"
                                        type="url"
                                        placeholder="https://..."
                                        class="h-11 w-full rounded-lg border border-border bg-background pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                </div>

                                <div
                                    v-if="form.profile_link"
                                    class="flex items-center gap-2 text-xs text-muted-foreground"
                                >
                                    <Check
                                        class="h-3.5 w-3.5 text-emerald-500"
                                    />

                                    Link profile siap digunakan
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <div class="space-y-3">
                                <label
                                    class="text-sm font-medium"
                                >
                                    Foto Profil
                                </label>

                                <div
                                    class="flex flex-col gap-4 rounded-xl border border-dashed border-border bg-muted/20 p-4 sm:flex-row sm:items-center"
                                >
                                    <div
                                        class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted"
                                    >
                                        <img
                                            v-if="previewImage"
                                            :src="previewImage"
                                            alt="Preview foto profile"
                                            class="h-full w-full object-cover"
                                        />

                                        <User
                                            v-else
                                            class="h-8 w-8 text-muted-foreground"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <input
                                                id="profile_image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="hidden"
                                                @change="
                                                    handleImageChange
                                                "
                                            />

                                            <label
                                                for="profile_image"
                                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-border bg-background px-3.5 py-2 text-sm font-medium transition hover:bg-muted"
                                            >
                                                <ImagePlus
                                                    class="h-4 w-4"
                                                />

                                                {{
                                                    previewImage
                                                        ? 'Ganti Foto'
                                                        : 'Pilih Foto'
                                                }}
                                            </label>

                                            <button
                                                v-if="previewImage"
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-500 transition hover:bg-red-500/10"
                                                @click="removeImage"
                                            >
                                                <X class="h-4 w-4" />
                                                Hapus
                                            </button>
                                        </div>

                                        <p
                                            class="mt-2 text-xs text-muted-foreground"
                                        >
                                            JPG, PNG, atau WEBP · Maksimal
                                            2 MB
                                        </p>
                                    </div>
                                </div>

                                <p
                                    v-if="form.errors.profile_image"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.profile_image }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <label
                                        class="text-sm font-medium"
                                    >
                                        Notes
                                    </label>

                                    <span
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ form.notes.length }} karakter
                                    </span>
                                </div>

                                <textarea
                                    v-model="form.notes"
                                    rows="5"
                                    placeholder="Catatan tambahan mengenai creator..."
                                    class="w-full resize-none rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div
                    v-else
                    class="space-y-5"
                >
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
                    >
                        <div class="mb-6">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-primary"
                            >
                                Step 04
                            </p>

                            <h2 class="mt-1 text-lg font-semibold">
                                Review Creator
                            </h2>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Pastikan informasi creator sudah sesuai
                                sebelum disimpan.
                            </p>
                        </div>

                        <!-- Review Profile -->
                        <div
                            class="rounded-xl border border-border bg-muted/20 p-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted"
                                >
                                    <img
                                        v-if="previewImage"
                                        :src="previewImage"
                                        alt="Preview"
                                        class="h-full w-full object-cover"
                                    />

                                    <span
                                        v-else
                                        class="text-lg font-semibold text-muted-foreground"
                                    >
                                        {{ profileInitial }}
                                    </span>
                                </div>

                                <div class="min-w-0">
                                    <h3
                                        class="truncate font-semibold"
                                    >
                                        {{ form.name || 'Nama Creator' }}
                                    </h3>

                                    <p
                                        class="mt-0.5 text-sm text-muted-foreground"
                                    >
                                        {{ usernameDisplay }}
                                    </p>
                                </div>

                                <div class="ml-auto">
                                    <span
                                        class="rounded-md px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            form.status === 'active'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        {{
                                            form.status === 'active'
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Review Sections -->
                        <div class="mt-5 divide-y divide-border">
                            <div
                                class="flex items-start justify-between gap-4 py-4"
                            >
                                <div>
                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        Platform
                                    </p>

                                    <p class="mt-1 text-sm font-medium">
                                        {{ form.platform || '-' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        Followers
                                    </p>

                                    <p class="mt-1 text-sm font-medium">
                                        {{ formattedFollowers }}
                                    </p>
                                </div>
                            </div>

                            <div class="py-4">
                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Category
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{ form.category || '-' }}
                                </p>
                            </div>

                            <div class="py-4">
                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Audience
                                </p>

                                <div class="mt-2 grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <p
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            Gender
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-medium"
                                        >
                                            {{
                                                form.audience_gender[0] ||
                                                '-'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            Age
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-medium"
                                        >
                                            {{
                                                form.audience_age[0] ||
                                                '-'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            Location
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-medium"
                                        >
                                            {{
                                                form.audience_location[0] ||
                                                '-'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="py-4">
                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Profile Link
                                </p>

                                <a
                                    v-if="form.profile_link"
                                    :href="form.profile_link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                >
                                    Lihat profile
                                    <ExternalLink
                                        class="h-3.5 w-3.5"
                                    />
                                </a>

                                <p
                                    v-else
                                    class="mt-1 text-sm font-medium"
                                >
                                    -
                                </p>
                            </div>

                            <div class="py-4">
                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Notes
                                </p>

                                <p
                                    class="mt-1 whitespace-pre-line text-sm leading-6"
                                >
                                    {{ form.notes || '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Completion -->
                    <div
                        class="rounded-xl border border-border bg-card p-4 shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <div>
                                <p class="text-sm font-medium">
                                    Kelengkapan data
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    Data tambahan dapat dilengkapi
                                    kembali setelah creator dibuat.
                                </p>
                            </div>

                            <span
                                class="text-sm font-semibold"
                            >
                                {{ completion }}%
                            </span>
                        </div>

                        <div
                            class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-all duration-300"
                                :style="{
                                    width: `${completion}%`,
                                }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div
                    class="mt-5 flex items-center justify-between gap-3"
                >
                    <div>
                        <Link
                            v-if="currentStep === 1"
                            href="/creators"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition hover:bg-muted"
                        >
                            Batal
                        </Link>

                        <button
                            v-else
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition hover:bg-muted"
                            @click="previousStep"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Kembali
                        </button>
                    </div>

                    <div>
                        <button
                            v-if="currentStep < 4"
                            type="button"
                            :disabled="
                                currentStep === 1 &&
                                !canContinueFromProfile
                            "
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="nextStep"
                        >
                            Lanjutkan
                            <ArrowRight class="h-4 w-4" />
                        </button>

                        <button
                            v-else
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />

                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Creator'
                            }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Live Preview -->
            <aside class="hidden xl:block">
                <div
                    class="sticky top-6 rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold">
                                Creator Preview
                            </p>

                            <p
                                class="mt-0.5 text-xs text-muted-foreground"
                            >
                                Preview akan mengikuti data form.
                            </p>
                        </div>

                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted"
                        >
                            <User
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </div>
                    </div>

                    <!-- Profile -->
                    <div
                        class="mt-6 rounded-xl border border-border bg-muted/20 p-5 text-center"
                    >
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border border-border bg-muted"
                        >
                            <img
                                v-if="previewImage"
                                :src="previewImage"
                                alt="Creator preview"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-else
                                class="text-2xl font-semibold text-muted-foreground"
                            >
                                {{ profileInitial }}
                            </span>
                        </div>

                        <p class="mt-4 truncate font-semibold">
                            {{ form.name || 'Nama Creator' }}
                        </p>

                        <p
                            class="mt-1 truncate text-sm text-muted-foreground"
                        >
                            {{ usernameDisplay }}
                        </p>

                        <div
                            class="mt-4 flex flex-wrap items-center justify-center gap-2"
                        >
                            <span
                                v-if="form.platform"
                                class="rounded-md bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                            >
                                {{ form.platform }}
                            </span>

                            <span
                                v-if="form.status"
                                class="rounded-md px-2.5 py-1 text-xs font-medium"
                                :class="
                                    form.status === 'active'
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                {{
                                    form.status === 'active'
                                        ? 'Active'
                                        : 'Inactive'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Metrics -->
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div
                            class="rounded-lg border border-border bg-background p-3"
                        >
                            <p
                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                            >
                                Followers
                            </p>

                            <p class="mt-1 text-lg font-semibold">
                                {{ shortFollowers }}
                            </p>
                        </div>

                        <div
                            class="rounded-lg border border-border bg-background p-3"
                        >
                            <p
                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                            >
                                Category
                            </p>

                            <p
                                class="mt-1 truncate text-sm font-semibold"
                            >
                                {{ form.category || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Audience -->
                    <div class="mt-4">
                        <p
                            class="text-xs font-medium text-muted-foreground"
                        >
                            Audience
                        </p>

                        <div class="mt-2 space-y-2">
                            <div
                                class="flex items-center justify-between rounded-lg bg-muted/40 px-3 py-2"
                            >
                                <span class="text-xs text-muted-foreground">
                                    Gender
                                </span>

                                <span class="max-w-[160px] truncate text-xs font-medium">
                                    {{
                                        form.audience_gender[0] || '-'
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-lg bg-muted/40 px-3 py-2"
                            >
                                <span class="text-xs text-muted-foreground">
                                    Age
                                </span>

                                <span class="max-w-[160px] truncate text-xs font-medium">
                                    {{
                                        form.audience_age[0] || '-'
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-lg bg-muted/40 px-3 py-2"
                            >
                                <span class="text-xs text-muted-foreground">
                                    Location
                                </span>

                                <span class="max-w-[160px] truncate text-xs font-medium">
                                    {{
                                        form.audience_location[0] || '-'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Completion -->
                    <div class="mt-5 border-t border-border pt-4">
                        <div
                            class="flex items-center justify-between"
                        >
                            <span
                                class="text-xs text-muted-foreground"
                            >
                                Data completeness
                            </span>

                            <span class="text-xs font-semibold">
                                {{ completion }}%
                            </span>
                        </div>

                        <div
                            class="mt-2 h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-all duration-300"
                                :style="{
                                    width: `${completion}%`,
                                }"
                            />
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>