<script setup lang="ts">
import { computed, ref } from 'vue'
import { Form, Head } from '@inertiajs/vue3'
import {
    Check,
    CircleAlert,
    CircleCheck,
    KeyRound,
    LockKeyhole,
    ShieldCheck,
    X,
} from 'lucide-vue-next'

import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController'
import Heading from '@/components/Heading.vue'
import InputError from '@/components/InputError.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { edit } from '@/routes/security'

type Props = {
    passwordRules: string
}

const props = defineProps<Props>()

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
})

const newPassword = ref('')
const confirmPassword = ref('')
const currentPassword = ref('')

const passwordFocused = ref(false)

const passwordChecks = computed(() => ({
    length: newPassword.value.length >= 8,
    lowercase: /[a-z]/.test(newPassword.value),
    uppercase: /[A-Z]/.test(newPassword.value),
    number: /\d/.test(newPassword.value),
    special: /[^A-Za-z0-9]/.test(newPassword.value),
}))

const passwordScore = computed(() => {
    return Object.values(passwordChecks.value).filter(Boolean).length
})

const passwordStrength = computed(() => {
    if (!newPassword.value) {
        return {
            label: 'Belum diisi',
            width: '0%',
        }
    }

    if (passwordScore.value <= 2) {
        return {
            label: 'Lemah',
            width: '35%',
        }
    }

    if (passwordScore.value === 3) {
        return {
            label: 'Cukup',
            width: '55%',
        }
    }

    if (passwordScore.value === 4) {
        return {
            label: 'Kuat',
            width: '75%',
        }
    }

    return {
        label: 'Sangat kuat',
        width: '100%',
    }
})

const passwordsMatch = computed(() => {
    if (!confirmPassword.value) return null

    return newPassword.value === confirmPassword.value
})

const sameAsCurrent = computed(() => {
    if (!currentPassword.value || !newPassword.value) {
        return false
    }

    return currentPassword.value === newPassword.value
})
</script>

<template>
    <Head title="Security settings" />

    <h1 class="sr-only">Security settings</h1>

    <div class="space-y-8">

        <!-- Header -->
        <Heading
            variant="small"
            title="Update password"
            description="Kelola password akun Anda dan pastikan akun tetap terlindungi."
        />

        <!-- Security Status -->
        <div
            class="relative overflow-hidden rounded-2xl border border-border bg-card"
        >
            <div
                class="absolute right-0 top-0 h-40 w-40 rounded-full bg-emerald-500/10 blur-3xl"
            />

            <div class="relative flex items-start gap-4 p-5 sm:p-6">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                >
                    <ShieldCheck class="h-5 w-5" />
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold text-foreground">
                            Account security
                        </h2>

                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                            />
                            Protected
                        </span>
                    </div>

                    <p class="mt-1 text-sm leading-6 text-muted-foreground">
                        Gunakan password yang unik dan sulit ditebak. Hindari
                        menggunakan password yang sama untuk akun lain.
                    </p>
                </div>
            </div>
        </div>

        <!-- Password Form -->
        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <!-- Password Card -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card"
            >
                <div class="border-b border-border px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <LockKeyhole class="h-4 w-4" />
                        </div>

                        <div>
                            <h2 class="text-sm font-semibold text-foreground">
                                Password credentials
                            </h2>

                            <p class="text-xs text-muted-foreground">
                                Perbarui password login akun Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-5 sm:p-6">

                    <!-- Current Password -->
                    <div class="grid gap-2">
                        <Label for="current_password">
                            Current password
                        </Label>

                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            v-model="currentPassword"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Masukkan password saat ini"
                        />

                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="h-px bg-border" />

                    <!-- New Password -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between gap-4">
                            <Label for="password">
                                New password
                            </Label>

                            <span
                                v-if="newPassword"
                                class="text-xs font-medium"
                                :class="
                                    passwordScore >= 4
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : passwordScore >= 3
                                          ? 'text-amber-600 dark:text-amber-400'
                                          : 'text-destructive'
                                "
                            >
                                {{ passwordStrength.label }}
                            </span>
                        </div>

                        <PasswordInput
                            id="password"
                            name="password"
                            v-model="newPassword"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Masukkan password baru"
                            :passwordrules="props.passwordRules"
                            @focus="passwordFocused = true"
                        />

                        <InputError :message="errors.password" />

                        <!-- Strength -->
                        <div
                            v-if="newPassword"
                            class="space-y-3 rounded-xl border border-border bg-muted/30 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Password strength
                                </span>

                                <span
                                    class="text-xs font-medium text-foreground"
                                >
                                    {{ passwordScore }}/5
                                </span>
                            </div>

                            <div
                                class="h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="
                                        passwordScore >= 4
                                            ? 'bg-emerald-500'
                                            : passwordScore === 3
                                              ? 'bg-amber-500'
                                              : 'bg-destructive'
                                    "
                                    :style="{
                                        width: passwordStrength.width,
                                    }"
                                />
                            </div>

                            <!-- Requirements -->
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div
                                    v-for="item in [
                                        {
                                            label: 'Minimal 8 karakter',
                                            valid: passwordChecks.length,
                                        },
                                        {
                                            label: 'Huruf kecil',
                                            valid: passwordChecks.lowercase,
                                        },
                                        {
                                            label: 'Huruf kapital',
                                            valid: passwordChecks.uppercase,
                                        },
                                        {
                                            label: 'Angka',
                                            valid: passwordChecks.number,
                                        },
                                        {
                                            label: 'Karakter khusus',
                                            valid: passwordChecks.special,
                                        },
                                    ]"
                                    :key="item.label"
                                    class="flex items-center gap-2 text-xs"
                                    :class="
                                        item.valid
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    <Check
                                        v-if="item.valid"
                                        class="h-3.5 w-3.5"
                                    />

                                    <X
                                        v-else
                                        class="h-3.5 w-3.5"
                                    />

                                    {{ item.label }}
                                </div>
                            </div>
                        </div>

                        <!-- Same password warning -->
                        <div
                            v-if="sameAsCurrent"
                            class="flex items-start gap-2 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-xs text-amber-700 dark:text-amber-400"
                        >
                            <CircleAlert class="mt-0.5 h-4 w-4 shrink-0" />

                            <span>
                                Password baru sebaiknya berbeda dari password
                                saat ini.
                            </span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm password
                        </Label>

                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            v-model="confirmPassword"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            :passwordrules="props.passwordRules"
                        />

                        <InputError
                            :message="errors.password_confirmation"
                        />

                        <div
                            v-if="passwordsMatch !== null"
                            class="flex items-center gap-2 text-xs"
                            :class="
                                passwordsMatch
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-destructive'
                            "
                        >
                            <CircleCheck
                                v-if="passwordsMatch"
                                class="h-3.5 w-3.5"
                            />

                            <CircleAlert
                                v-else
                                class="h-3.5 w-3.5"
                            />

                            <span>
                                {{
                                    passwordsMatch
                                        ? 'Password cocok.'
                                        : 'Password belum cocok.'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tips -->
            <div
                class="rounded-2xl border border-blue-500/20 bg-blue-500/5 p-5 sm:p-6"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400"
                    >
                        <KeyRound class="h-4 w-4" />
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-foreground">
                            Tips membuat password yang aman
                        </h3>

                        <ul
                            class="mt-2 space-y-1.5 text-xs leading-5 text-muted-foreground"
                        >
                            <li>
                                • Gunakan password unik untuk akun ini.
                            </li>
                            <li>
                                • Hindari nama, tanggal lahir, atau informasi
                                yang mudah ditebak.
                            </li>
                            <li>
                                • Gunakan kombinasi huruf, angka, dan karakter
                                khusus.
                            </li>
                            <li>
                                • Jangan membagikan password kepada siapa pun.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action -->
            <div
                class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-muted-foreground">
                    Pastikan password baru sudah benar sebelum menyimpan.
                </p>

                <Button
                    :disabled="
                        processing ||
                        !newPassword ||
                        !confirmPassword ||
                        passwordsMatch !== true
                    "
                    data-test="update-password-button"
                    class="min-w-[150px]"
                >
                    <span v-if="processing">
                        Updating...
                    </span>

                    <span v-else class="flex items-center gap-2">
                        <LockKeyhole class="h-4 w-4" />
                        Update password
                    </span>
                </Button>
            </div>
        </Form>
    </div>
</template>