<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowLeft,
    Check,
    CheckCircle2,
    Circle,
    LockKeyhole,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue'

import InputError from '@/components/InputError.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import { update } from '@/routes/password'

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
        fullScreen: true,
    },
})

const props = defineProps<{
    token: string
    email: string
    passwordRules: string
}>()

const inputEmail = ref(props.email)
const password = ref('')
const passwordConfirmation = ref('')

const passwordChecks = computed(() => ({
    length: password.value.length >= 8,
    lowercase: /[a-z]/.test(password.value),
    uppercase: /[A-Z]/.test(password.value),
    number: /[0-9]/.test(password.value),
    special: /[^A-Za-z0-9]/.test(password.value),
}))

const passwordScore = computed(() => {
    return Object.values(passwordChecks.value).filter(Boolean).length
})

const passwordStrength = computed(() => {
    if (!password.value) {
        return {
            label: 'Belum diisi',
            color: 'bg-slate-200',
            text: 'text-slate-400',
            width: '0%',
        }
    }

    if (passwordScore.value <= 2) {
        return {
            label: 'Lemah',
            color: 'bg-rose-500',
            text: 'text-rose-500',
            width: '35%',
        }
    }

    if (passwordScore.value === 3) {
        return {
            label: 'Cukup',
            color: 'bg-amber-500',
            text: 'text-amber-500',
            width: '60%',
        }
    }

    if (passwordScore.value === 4) {
        return {
            label: 'Kuat',
            color: 'bg-sky-500',
            text: 'text-sky-500',
            width: '80%',
        }
    }

    return {
        label: 'Sangat kuat',
        color: 'bg-emerald-500',
        text: 'text-emerald-500',
        width: '100%',
    }
})

const passwordMatches = computed(() => {
    return (
        passwordConfirmation.value.length > 0 &&
        password.value === passwordConfirmation.value
    )
})

const formReady = computed(() => {
    return passwordScore.value >= 4 && passwordMatches.value
})

const progressWidth = computed(() => {
    if (formReady.value) return '100%'
    if (passwordScore.value >= 4 || passwordConfirmation.value) return '75%'
    if (password.value) return '50%'

    return '0%'
})

const requirements = computed(() => [
    {
        label: 'Minimal 8 karakter',
        valid: passwordChecks.value.length,
    },
    {
        label: 'Huruf kecil',
        valid: passwordChecks.value.lowercase,
    },
    {
        label: 'Huruf besar',
        valid: passwordChecks.value.uppercase,
    },
    {
        label: 'Angka',
        valid: passwordChecks.value.number,
    },
    {
        label: 'Karakter khusus',
        valid: passwordChecks.value.special,
    },
])
</script>

<template>
    <Head title="Reset password" />

    <div class="min-h-screen bg-slate-950">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- ================================================= -->
            <!-- LEFT : BRAND / SECURITY -->
            <!-- ================================================= -->
            <div
                class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-12 lg:flex lg:flex-col lg:justify-between xl:p-16"
            >
                <!-- Glow -->
                <div
                    class="absolute -left-32 top-1/4 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"
                />

                <div
                    class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"
                />

                <!-- Grid -->
                <div
                    class="pointer-events-none absolute inset-0 opacity-30"
                    style="
                        background-image:
                            linear-gradient(
                                rgba(96, 165, 250, 0.06) 1px,
                                transparent 1px
                            ),
                            linear-gradient(
                                90deg,
                                rgba(96, 165, 250, 0.06) 1px,
                                transparent 1px
                            );
                        background-size: 48px 48px;
                    "
                />

                <!-- Logo -->
                <div class="relative z-10">
                    <img
                        src="/images/creator kecil.png"
                        alt="Creator Affiliate Intelligence"
                        class="h-14 w-auto object-contain"
                    />
                </div>

                <!-- Main -->
                <div class="relative z-10 max-w-xl">

                    <div
                        class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-400/20 bg-sky-400/10 px-3 py-1.5 text-xs font-semibold text-sky-300"
                    >
                        <LockKeyhole class="size-3.5" />

                        Account Security
                    </div>

                    <h1
                        class="font-heading text-5xl font-extrabold leading-tight tracking-tight text-white xl:text-6xl"
                    >
                        Amankan akunmu dengan
                        <span class="text-sky-400">
                            password baru.
                        </span>
                    </h1>

                    <p
                        class="mt-6 max-w-lg text-lg leading-relaxed text-slate-400"
                    >
                        Buat password baru yang kuat untuk menjaga
                        akses ke Creator & Affiliate Intelligence
                        tetap aman.
                    </p>

                    <!-- Security cards -->
                    <div class="mt-10 grid gap-4 sm:grid-cols-3">

                        <div
                            class="group rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <div
                                class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-sky-400/10 text-sky-300 transition duration-300 group-hover:scale-110"
                            >
                                <LockKeyhole class="size-4" />
                            </div>

                            <p class="font-semibold text-white">
                                Password
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                                Gunakan password yang unik.
                            </p>
                        </div>

                        <div
                            class="group rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <div
                                class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-sky-400/10 text-sky-300 transition duration-300 group-hover:scale-110"
                            >
                                <ShieldCheck class="size-4" />
                            </div>

                            <p class="font-semibold text-white">
                                Secure
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                                Lindungi akses akunmu.
                            </p>
                        </div>

                        <div
                            class="group rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <div
                                class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-sky-400/10 text-sky-300 transition duration-300 group-hover:scale-110"
                            >
                                <Sparkles class="size-4" />
                            </div>

                            <p class="font-semibold text-white">
                                Ready
                            </p>

                            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                                Login kembali setelah selesai.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="relative z-10 flex items-center gap-2 text-sm text-slate-500"
                >
                    <ShieldCheck class="size-4" />

                    Creator & Affiliate Intelligence
                </div>
            </div>

            <!-- ================================================= -->
            <!-- RIGHT : FORM -->
            <!-- ================================================= -->
            <div
                class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-lg">

                    <!-- Back -->
                    <div class="mb-7">
                        <Link
                            href="/login"
                            class="group inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            <ArrowLeft
                                class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                            />

                            Kembali ke Login
                        </Link>
                    </div>

                    <!-- Mobile logo -->
                    <div class="mb-7 flex justify-center lg:hidden">
                        <img
                            src="/images/creator kecil.png"
                            alt="Creator Affiliate Intelligence"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <!-- Heading -->
                    <div class="mb-7">

                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-600"
                        >
                            <LockKeyhole class="size-3.5" />

                            Reset password
                        </div>

                        <h2
                            class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Buat password baru
                        </h2>

                        <p
                            class="mt-3 text-sm leading-relaxed text-slate-500"
                        >
                            Buat password baru yang kuat untuk
                            mengamankan kembali akun kamu.
                        </p>

                    </div>

                    <!-- Account -->
                    <div
                        class="mb-6 flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-sky-500 shadow-sm"
                        >
                            <ShieldCheck class="size-4" />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Reset akun
                            </p>

                            <p
                                class="truncate text-sm font-semibold text-slate-700"
                            >
                                {{ inputEmail }}
                            </p>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="mb-7">

                        <div
                            class="mb-2 flex items-center justify-between"
                        >
                            <span
                                class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Password setup
                            </span>

                            <span
                                class="text-xs font-semibold"
                                :class="
                                    formReady
                                        ? 'text-emerald-500'
                                        : 'text-slate-400'
                                "
                            >
                                {{
                                    formReady
                                        ? 'Ready'
                                        : 'Lengkapi password'
                                }}
                            </span>
                        </div>

                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-slate-100"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-500"
                                :style="{ width: progressWidth }"
                            />
                        </div>

                    </div>

                    <!-- ================================================= -->
                    <!-- FORM -->
                    <!-- ================================================= -->
                    <Form
                        v-bind="update.form()"
                        :transform="(data) => ({
                            ...data,
                            token,
                            email,
                        })"
                        :reset-on-success="[
                            'password',
                            'password_confirmation',
                        ]"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >

                        <div class="grid gap-5">

                            <!-- EMAIL -->
                            <div class="grid gap-2.5">

                                <Label
                                    for="email"
                                    class="font-semibold text-slate-700"
                                >
                                    Email
                                </Label>

                                <div class="relative">

                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        v-model="inputEmail"
                                        readonly
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50 pr-11 text-sm text-slate-500 transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                    />

                                    <CheckCircle2
                                        class="absolute right-3 top-1/2 size-4 -translate-y-1/2 text-emerald-500"
                                    />

                                </div>

                                <InputError :message="errors.email" />

                            </div>

                            <!-- PASSWORD -->
                            <div class="grid gap-2.5">

                                <Label
                                    for="password"
                                    class="font-semibold text-slate-700"
                                >
                                    Password baru
                                </Label>

                                <PasswordInput
                                    id="password"
                                    v-model="password"
                                    name="password"
                                    autocomplete="new-password"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                    autofocus
                                    placeholder="Masukkan password baru"
                                    :passwordrules="passwordRules"
                                    :class="{
                                        'border-emerald-300 bg-emerald-50/40':
                                            passwordScore >= 4,
                                    }"
                                />

                                <InputError :message="errors.password" />

                                <!-- Strength -->
                                <div
                                    v-if="password"
                                    class="mt-1"
                                >

                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <span class="text-xs text-slate-400">
                                            Kekuatan password
                                        </span>

                                        <span
                                            class="text-xs font-semibold"
                                            :class="passwordStrength.text"
                                        >
                                            {{ passwordStrength.label }}
                                        </span>
                                    </div>

                                    <div
                                        class="h-1.5 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="passwordStrength.color"
                                            :style="{
                                                width: passwordStrength.width,
                                            }"
                                        />
                                    </div>

                                </div>

                                <!-- Requirements -->
                                <div
                                    class="mt-2 rounded-xl border border-slate-100 bg-slate-50/70 p-3"
                                >

                                    <p
                                        class="mb-2 text-xs font-semibold text-slate-600"
                                    >
                                        Password sebaiknya memiliki:
                                    </p>

                                    <div
                                        class="grid gap-1.5 sm:grid-cols-2"
                                    >

                                        <div
                                            v-for="item in requirements"
                                            :key="item.label"
                                            class="flex items-center gap-2 text-xs transition-colors duration-200"
                                            :class="
                                                item.valid
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'
                                            "
                                        >

                                            <CheckCircle2
                                                v-if="item.valid"
                                                class="size-3.5 shrink-0"
                                            />

                                            <Circle
                                                v-else
                                                class="size-3.5 shrink-0"
                                            />

                                            {{ item.label }}

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- CONFIRM -->
                            <div class="grid gap-2.5">

                                <Label
                                    for="password_confirmation"
                                    class="font-semibold text-slate-700"
                                >
                                    Konfirmasi password
                                </Label>

                                <PasswordInput
                                    id="password_confirmation"
                                    v-model="passwordConfirmation"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                    placeholder="Ulangi password baru"
                                    :passwordrules="passwordRules"
                                    :class="{
                                        'border-emerald-300 bg-emerald-50/40':
                                            passwordMatches,
                                        'border-rose-300 bg-rose-50/40':
                                            passwordConfirmation &&
                                            !passwordMatches,
                                    }"
                                />

                                <p
                                    v-if="
                                        passwordConfirmation &&
                                        passwordMatches
                                    "
                                    class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                >
                                    <Check class="size-3.5" />

                                    Password cocok
                                </p>

                                <p
                                    v-else-if="
                                        passwordConfirmation &&
                                        !passwordMatches
                                    "
                                    class="text-xs font-medium text-rose-500"
                                >
                                    Password belum cocok.
                                </p>

                                <InputError
                                    :message="errors.password_confirmation"
                                />

                            </div>

                            <!-- SECURITY NOTE -->
                            <div
                                class="flex gap-3 rounded-xl border border-sky-100 bg-sky-50/70 p-4"
                            >

                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-sky-500 shadow-sm"
                                >
                                    <ShieldCheck class="size-4" />
                                </div>

                                <div>

                                    <p
                                        class="text-xs font-semibold text-sky-700"
                                    >
                                        Tips keamanan
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-relaxed text-sky-600/80"
                                    >
                                        Gunakan password yang berbeda dari
                                        akun lain dan hindari informasi pribadi
                                        yang mudah ditebak.
                                    </p>

                                </div>

                            </div>

                            <!-- SUBMIT -->
                            <Button
                                type="submit"
                                class="mt-1 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-blue-500/30 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="processing"
                                data-test="reset-password-button"
                            >

                                <Spinner v-if="processing" />

                                <template v-else>

                                    <Check
                                        v-if="formReady"
                                        class="mr-2 size-4"
                                    />

                                    {{
                                        formReady
                                            ? 'Update password'
                                            : 'Buat password baru'
                                    }}

                                </template>

                            </Button>

                        </div>

                        <!-- Footer -->
                        <div
                            class="border-t border-slate-100 pt-5 text-center text-sm text-slate-500"
                        >
                            Ingat password kamu?

                            <Link
                                href="/login"
                                class="ml-1 font-semibold text-sky-500 transition hover:text-sky-600"
                            >
                                Kembali ke login
                            </Link>
                        </div>

                    </Form>

                </div>
            </div>
        </div>
    </div>
</template>