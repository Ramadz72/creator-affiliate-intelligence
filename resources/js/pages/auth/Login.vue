<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Check,
    CheckCircle2,
    Mail,
    ShieldCheck,
} from '@lucide/vue';

import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const email = ref('');
const password = ref('');

const emailIsValid = computed(() => {
    if (!email.value) {
        return false;
    }

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value);
});

const passwordFilled = computed(() => {
    return password.value.length > 0;
});

const formReady = computed(() => {
    return emailIsValid.value && passwordFilled.value;
});

const progressWidth = computed(() => {
    if (emailIsValid.value && passwordFilled.value) {
        return '100%';
    }

    if (emailIsValid.value || passwordFilled.value) {
        return '50%';
    }

    return '0%';
});
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-slate-950">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- ================================================= -->
            <!-- LEFT : BRAND -->
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
                />

                <!-- Logo -->
                <div class="relative z-10">
                    <img
                        src="/images/creator kecil.png"
                        alt="Creator Affiliate Intelligence"
                        class="h-14 w-auto object-contain"
                    />
                </div>

                <!-- Main content -->
                <div class="relative z-10 max-w-xl">
                    <p
                        class="mb-4 text-sm font-semibold uppercase tracking-[0.25em] text-sky-400"
                    >
                        Creator Intelligence
                    </p>

                    <h1
                        class="font-heading text-5xl font-extrabold leading-tight tracking-tight text-white xl:text-6xl"
                    >
                        Kelola Creator &
                        <span class="text-sky-400">
                            Affiliate
                        </span>
                        lebih cerdas.
                    </h1>

                    <p
                        class="mt-6 max-w-lg text-lg leading-relaxed text-slate-400"
                    >
                        Analisis performa creator, pantau affiliate,
                        dan temukan peluang kolaborasi terbaik dalam
                        satu platform.
                    </p>

                    <!-- Feature cards -->
                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-2xl font-bold text-white">
                                Creator
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Intelligence
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-2xl font-bold text-white">
                                Affiliate
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Performance
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <p class="text-2xl font-bold text-white">
                                ROI
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Analysis
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative z-10 text-sm text-slate-500">
                    Creator & Affiliate Intelligence
                </div>
            </div>

            <!-- ================================================= -->
            <!-- RIGHT : LOGIN -->
            <!-- ================================================= -->
            <div
                class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-md">

                    <!-- BACK TO WELCOME -->
                    <div class="mb-7">
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
                            <CheckCircle2 class="size-3.5" />

                            Welcome back
                        </div>

                        <h2
                            class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Selamat datang kembali
                        </h2>

                        <p
                            class="mt-3 text-sm leading-relaxed text-slate-500"
                        >
                            Masuk untuk melanjutkan ke Creator &
                            Affiliate Intelligence.
                        </p>
                    </div>

                    <!-- FORM -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-5">

                            <!-- Progress -->
                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Login progress
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
                                                : 'Lengkapi form'
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="h-1.5 overflow-hidden rounded-full bg-slate-100"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-500"
                                        :style="{
                                            width: progressWidth,
                                        }"
                                    />
                                </div>
                            </div>

                            <!-- EMAIL -->
                            <div class="grid gap-2.5">
                                <Label
                                    for="email"
                                    class="font-semibold text-slate-700"
                                >
                                    Email address
                                </Label>

                                <div class="relative">
                                    <Input
                                        id="email"
                                        v-model="email"
                                        type="email"
                                        name="email"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        placeholder="email@example.com"
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 pr-11 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                        :class="{
                                            'border-emerald-300 bg-emerald-50/40':
                                                emailIsValid,
                                        }"
                                    />

                                    <div
                                        v-if="emailIsValid"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500"
                                    >
                                        <CheckCircle2 class="size-4" />
                                    </div>
                                </div>

                                <p
                                    v-if="emailIsValid"
                                    class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                >
                                    <Check class="size-3.5" />

                                    Email terlihat valid
                                </p>

                                <InputError
                                    :message="errors.email"
                                />
                            </div>

                            <!-- PASSWORD -->
                            <div class="grid gap-2.5">
                                <div class="flex items-center justify-between">
                                    <Label
                                        for="password"
                                        class="font-semibold text-slate-700"
                                    >
                                        Password
                                    </Label>

                                    <TextLink
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-sm font-medium text-sky-500 transition hover:text-sky-600"
                                    >
                                        Forgot your password?
                                    </TextLink>
                                </div>

                                <div class="relative">
                                    <PasswordInput
                                        id="password"
                                        v-model="password"
                                        name="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        placeholder="Password"
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50 pr-11 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                        :class="{
                                            'border-emerald-300 bg-emerald-50/40':
                                                passwordFilled,
                                        }"
                                    />

                                    <div
                                        v-if="passwordFilled"
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500"
                                    >
                                        <CheckCircle2 class="size-4" />
                                    </div>
                                </div>

                                <p
                                    v-if="passwordFilled"
                                    class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                >
                                    <Check class="size-3.5" />

                                    Password terisi
                                </p>

                                <InputError
                                    :message="errors.password"
                                />
                            </div>

                            <!-- REMEMBER -->
                            <div class="flex items-center justify-between">
                                <Label
                                    for="remember"
                                    class="flex cursor-pointer items-center gap-3 text-sm text-slate-500"
                                >
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        :tabindex="3"
                                    />

                                    <span>Remember me</span>
                                </Label>

                                <div
                                    class="flex items-center gap-1.5 text-xs text-slate-400"
                                >
                                    <ShieldCheck class="size-3.5" />

                                    Secure login
                                </div>
                            </div>

                            <!-- STATUS -->
                            <div
                                v-if="status"
                                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-600"
                            >
                                {{ status }}
                            </div>

                            <!-- LOGIN BUTTON -->
                            <Button
                                type="submit"
                                class="mt-1 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                :tabindex="4"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />

                                <template v-else>
                                    <Check
                                        v-if="formReady"
                                        class="mr-2 size-4"
                                    />

                                    <span>
                                        {{
                                            formReady
                                                ? 'Log in sekarang'
                                                : 'Log in'
                                        }}
                                    </span>
                                </template>
                            </Button>
                        </div>

                        <!-- REGISTER -->
                        <div
                            class="border-t border-slate-100 pt-5 text-center text-sm text-slate-500"
                        >
                            Don't have an account?

                            <TextLink
                                :href="register()"
                                class="ml-1 font-semibold text-sky-500 transition hover:text-sky-600"
                            >
                                Sign up
                            </TextLink>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-grid {
    background-image:
        linear-gradient(
            rgba(56, 189, 248, 0.035) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(56, 189, 248, 0.035) 1px,
            transparent 1px
        );
    background-size: 48px 48px;
}
</style>