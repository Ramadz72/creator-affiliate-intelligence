<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Check,
    CheckCircle2,
    Eye,
    EyeOff,
    ShieldCheck,
    UserPlus,
    X,
} from '@lucide/vue';

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    status?: string;
}>();

const password = ref('');
const passwordConfirmation = ref('');

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const passwordRules = computed(() => ({
    length: password.value.length >= 8,
    lowercase: /[a-z]/.test(password.value),
    uppercase: /[A-Z]/.test(password.value),
    number: /\d/.test(password.value),
}));

const passwordScore = computed(() => {
    return Object.values(passwordRules.value).filter(Boolean).length;
});

const passwordStrength = computed(() => {
    if (!password.value) {
        return {
            label: 'Belum diisi',
            width: '0%',
            class: 'bg-slate-200',
        };
    }

    if (passwordScore.value <= 1) {
        return {
            label: 'Lemah',
            width: '25%',
            class: 'bg-red-500',
        };
    }

    if (passwordScore.value === 2) {
        return {
            label: 'Sedang',
            width: '50%',
            class: 'bg-amber-500',
        };
    }

    if (passwordScore.value === 3) {
        return {
            label: 'Bagus',
            width: '75%',
            class: 'bg-sky-500',
        };
    }

    return {
        label: 'Kuat',
        width: '100%',
        class: 'bg-emerald-500',
    };
});

const passwordsMatch = computed(() => {
    if (!passwordConfirmation.value) {
        return null;
    }

    return password.value === passwordConfirmation.value;
});

const canSubmit = computed(() => {
    return (
        password.value.length >= 8 &&
        passwordScore.value >= 3 &&
        passwordConfirmation.value.length > 0 &&
        passwordsMatch.value === true
    );
});
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-slate-950">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- ================================================= -->
            <!-- LEFT SIDE -->
            <!-- ================================================= -->
            <div
                class="relative hidden min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-10 lg:flex lg:flex-col lg:justify-between xl:p-16"
            >
                <!-- Glow -->
                <div
                    class="absolute -left-32 top-1/4 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"
                >
                </div>

                <div
                    class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"
                >
                </div>

                <!-- Decorative dots -->
                <div
                    class="pointer-events-none absolute inset-0 opacity-30"
                    style="
                        background-image: radial-gradient(
                            rgba(96, 165, 250, 0.35) 1px,
                            transparent 1px
                        );
                        background-size: 28px 28px;
                    "
                >
                </div>

                <!-- Logo -->
                <div class="relative z-10">
                    <img
                        src="/images/logo-creator-affiliate.png"
                        alt="Creator Affiliate Intelligence"
                        class="h-16 w-auto object-contain"
                    />
                </div>

                <!-- Content -->
                <div class="relative z-10 max-w-xl">
                    <p
                        class="mb-5 text-sm font-bold uppercase tracking-[0.25em] text-sky-400"
                    >
                        Creator Intelligence
                    </p>

                    <h1
                        class="font-heading text-5xl font-extrabold leading-[1.08] tracking-tight text-white xl:text-6xl"
                    >
                        Mulai kelola
                        <span class="text-sky-400">
                            Creator &
                        </span>
                        Affiliate lebih cerdas.
                    </h1>

                    <p
                        class="mt-7 max-w-lg text-lg leading-relaxed text-slate-400"
                    >
                        Buat akun dan mulai analisis performa creator,
                        affiliate, serta peluang kolaborasi dalam satu
                        platform.
                    </p>

                    <!-- Feature cards -->
                    <div class="mt-10 grid max-w-2xl grid-cols-3 gap-4">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-base font-bold text-white">
                                Creator
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Intelligence
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-base font-bold text-white">
                                Affiliate
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Performance
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-base font-bold text-white">
                                ROI
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Analysis
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="relative z-10 text-sm text-slate-500"
                >
                    Creator & Affiliate Intelligence
                </div>
            </div>

            <!-- ================================================= -->
            <!-- RIGHT SIDE -->
            <!-- ================================================= -->
            <div
                class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-lg">

                    <!-- BACK TO WELCOME -->
                    <div class="mb-8">
                        <Link
                            href="/"
                            class="group inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            <ArrowLeft
                                class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                            />

                            <span>
                                Kembali ke Welcome
                            </span>
                        </Link>
                    </div>

                    <!-- Mobile logo -->
                    <div class="mb-8 flex justify-center lg:hidden">
                        <img
                            src="/images/logo-creator-affiliate.png"
                            alt="Creator Affiliate Intelligence"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <!-- Heading -->
                    <div class="mb-8">
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-600"
                        >
                            <UserPlus class="size-3.5" />

                            Create your account
                        </div>

                        <h2
                            class="font-heading text-4xl font-extrabold tracking-tight text-slate-900"
                        >
                            Buat akun baru
                        </h2>

                        <p
                            class="mt-3 text-base leading-relaxed text-slate-500"
                        >
                            Daftar untuk mulai menggunakan Creator &
                            Affiliate Intelligence.
                        </p>
                    </div>

                    <!-- Progress -->
                    <div class="mb-7">
                        <div class="mb-2 flex items-center justify-between">
                            <span
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Account setup
                            </span>

                            <span
                                class="text-xs font-semibold text-sky-500"
                            >
                                {{ canSubmit ? '100%' : 'Progress' }}
                            </span>
                        </div>

                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-slate-100"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-500"
                                :style="{
                                    width: canSubmit
                                        ? '100%'
                                        : passwordScore >= 3
                                          ? '75%'
                                            : password
                                            ? '50%'
                                            : '25%',
                                }"
                            >
                        </div>
                    </div>

                    </div>

                    <!-- Status -->
                    <div
                        v-if="status"
                        class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-600"
                    >
                        {{ status }}
                    </div>

                    <!-- FORM -->
                    <Form
                        v-bind="store.form()"
                        v-slot="{ errors, processing }"
                        :reset-on-success="[
                            'password',
                            'password_confirmation',
                        ]"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-5">

                            <!-- NAME -->
                            <div class="grid gap-2.5">
                                <Label
                                    for="name"
                                    class="font-semibold text-slate-700"
                                >
                                    Name
                                </Label>

                                <Input
                                    id="name"
                                    type="text"
                                    name="nama"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="Masukan nama"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                />

                                <InputError
                                    :message="errors.name"
                                />
                            </div>

                            <!-- EMAIL -->
                            <div class="grid gap-2.5">
                                <Label
                                    for="email"
                                    class="font-semibold text-slate-700"
                                >
                                    Alamat Email
                                </Label>

                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    placeholder="email@contoh.com"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                />

                                <InputError
                                    :message="errors.email"
                                />
                            </div>

                            <!-- PASSWORD -->
                            <div class="grid gap-2.5">
                                <Label
                                    for="password"
                                    class="font-semibold text-slate-700"
                                >
                                    Password
                                </Label>

                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="password"
                                        :type="
                                            showPassword
                                                ? 'text'
                                                : 'password'
                                        "
                                        name="password"
                                        required
                                        :tabindex="3"
                                        autocomplete="new-password"
                                        placeholder="Buat password"
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 pr-12 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                    />

                                    <button
                                        type="button"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                        :aria-label="
                                            showPassword
                                                ? 'Sembunyikan password'
                                                : 'Tampilkan password'
                                        "
                                        @click="
                                            showPassword = !showPassword
                                        "
                                    >
                                        <EyeOff
                                            v-if="showPassword"
                                            class="size-4"
                                        />

                                        <Eye
                                            v-else
                                            class="size-4"
                                        />
                                    </button>
                                </div>

                                <!-- Password strength -->
                                <div
                                    v-if="password"
                                    class="rounded-xl border border-slate-100 bg-slate-50 p-3"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-medium text-slate-500"
                                        >
                                            Kekuatan password
                                        </span>

                                        <span
                                            class="text-xs font-bold"
                                            :class="
                                                passwordScore <= 1
                                                    ? 'text-red-500'
                                                    : passwordScore === 2
                                                      ? 'text-amber-500'
                                                      : passwordScore === 3
                                                        ? 'text-sky-500'
                                                        : 'text-emerald-500'
                                            "
                                        >
                                            {{
                                                passwordStrength.label
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="h-1.5 overflow-hidden rounded-full bg-slate-200"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="
                                                passwordStrength.class
                                            "
                                            :style="{
                                                width:
                                                    passwordStrength.width,
                                            }"
                                        />
                                    </div>

                                    <div
                                        class="mt-3 grid grid-cols-2 gap-2"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-xs"
                                            :class="
                                                passwordRules.length
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    passwordRules.length
                                                "
                                                class="size-3.5"
                                            />

                                            <X
                                                v-else
                                                class="size-3.5"
                                            />

                                            Minimal 8 karakter
                                        </div>

                                        <div
                                            class="flex items-center gap-1.5 text-xs"
                                            :class="
                                                passwordRules.lowercase
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    passwordRules.lowercase
                                                "
                                                class="size-3.5"
                                            />

                                            <X
                                                v-else
                                                class="size-3.5"
                                            />

                                            Huruf kecil
                                        </div>

                                        <div
                                            class="flex items-center gap-1.5 text-xs"
                                            :class="
                                                passwordRules.uppercase
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    passwordRules.uppercase
                                                "
                                                class="size-3.5"
                                            />

                                            <X
                                                v-else
                                                class="size-3.5"
                                            />

                                            Huruf besar
                                        </div>

                                        <div
                                            class="flex items-center gap-1.5 text-xs"
                                            :class="
                                                passwordRules.number
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    passwordRules.number
                                                "
                                                class="size-3.5"
                                            />

                                            <X
                                                v-else
                                                class="size-3.5"
                                            />

                                            Angka
                                        </div>
                                    </div>
                                </div>

                                <InputError
                                    :message="errors.password"
                                />
                            </div>

                            <!-- CONFIRM PASSWORD -->
                            <div class="grid gap-2.5">
                                <Label
                                    for="password_confirmation"
                                    class="font-semibold text-slate-700"
                                >
                                    Confirm password
                                </Label>

                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="passwordConfirmation"
                                        :type="
                                            showPasswordConfirmation
                                                ? 'text'
                                                : 'password'
                                        "
                                        name="password_confirmation"
                                        required
                                        :tabindex="4"
                                        autocomplete="new-password"
                                        placeholder="Ulangi password"
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 pr-12 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                        :class="{
                                            'border-emerald-300 focus:border-emerald-400 focus:ring-emerald-400/10':
                                                passwordsMatch === true,
                                            'border-red-300 focus:border-red-400 focus:ring-red-400/10':
                                                passwordsMatch === false,
                                        }"
                                    />

                                    <button
                                        type="button"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                        :aria-label="
                                            showPasswordConfirmation
                                                ? 'Sembunyikan password'
                                                : 'Tampilkan password'
                                        "
                                        @click="
                                            showPasswordConfirmation =
                                                !showPasswordConfirmation
                                        "
                                    >
                                        <EyeOff
                                            v-if="
                                                showPasswordConfirmation
                                            "
                                            class="size-4"
                                        />

                                        <Eye
                                            v-else
                                            class="size-4"
                                        />
                                    </button>
                                </div>

                                <div
                                    v-if="passwordConfirmation"
                                    class="flex items-center gap-1.5 text-xs font-medium"
                                    :class="
                                        passwordsMatch
                                            ? 'text-emerald-600'
                                            : 'text-red-500'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="passwordsMatch"
                                        class="size-3.5"
                                    />

                                    <X
                                        v-else
                                        class="size-3.5"
                                    />

                                    {{
                                        passwordsMatch
                                            ? 'Password cocok'
                                            : 'Password belum sama'
                                    }}
                                </div>

                                <InputError
                                    :message="
                                        errors.password_confirmation
                                    "
                                />
                            </div>

                            <!-- SECURITY INFO -->
                            <div
                                class="flex gap-3 rounded-xl border border-sky-100 bg-sky-50 p-4"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-sky-500 shadow-sm"
                                >
                                    <ShieldCheck class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Akun kamu aman
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-relaxed text-slate-500"
                                    >
                                        Gunakan password yang unik dan
                                        jangan bagikan kepada orang lain.
                                    </p>
                                </div>
                            </div>

                            <!-- BUTTON -->
                            <Button
                                type="submit"
                                class="mt-1 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                :tabindex="5"
                                :disabled="processing"
                                data-test="register-button"
                            >
                                <Spinner v-if="processing" />

                                <template v-else>
                                    <Check
                                        v-if="canSubmit"
                                        class="mr-2 size-4"
                                    />

                                    <span>
                                        {{
                                            canSubmit
                                                ? 'Buat akun sekarang'
                                                : 'Create account'
                                        }}
                                    </span>
                                </template>
                            </Button>
                        </div>

                        <!-- LOGIN -->
                        <div
                            class="border-t border-slate-100 pt-6 text-center text-sm text-slate-500"
                        >
                            Already have an account?

                            <TextLink
                                :href="login()"
                                class="ml-1 font-semibold text-sky-500 transition hover:text-sky-600"
                                :tabindex="6"
                            >
                                Log in
                            </TextLink>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>