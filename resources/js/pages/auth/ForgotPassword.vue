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
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();

const emailValue = ref('');

const emailIsValid = computed(() => {
    if (!emailValue.value) {
        return false;
    }

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
        emailValue.value,
    );
});
</script>

<template>
    <Head title="Forgot password" />

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
                        Account Security
                    </p>

                    <h1
                        class="font-heading text-5xl font-extrabold leading-tight tracking-tight text-white xl:text-6xl"
                    >
                        Amankan kembali
                        <span class="text-sky-400">
                            akses akun
                        </span>
                        kamu.
                    </h1>

                    <p
                        class="mt-6 max-w-lg text-lg leading-relaxed text-slate-400"
                    >
                        Masukkan email akun kamu dan kami akan
                        mengirimkan link untuk membuat password baru.
                    </p>

                    <!-- Security cards -->
                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <Mail class="size-5 text-sky-400" />

                            <p
                                class="mt-3 text-sm font-bold text-white"
                            >
                                Email
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Verification
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <ShieldCheck class="size-5 text-sky-400" />

                            <p
                                class="mt-3 text-sm font-bold text-white"
                            >
                                Secure
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Account
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:bg-white/10"
                        >
                            <CheckCircle2
                                class="size-5 text-sky-400"
                            />

                            <p
                                class="mt-3 text-sm font-bold text-white"
                            >
                                Recovery
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Password
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
            <!-- RIGHT : FORGOT PASSWORD -->
            <!-- ================================================= -->
            <div
                class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-md">

                    <!-- BACK TO LOGIN -->
                    <div class="mb-7">
                        <Link
                            :href="login()"
                            class="group inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            <ArrowLeft
                                class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                            />

                            <span>
                                Kembali ke Login
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
                            <Mail class="size-3.5" />

                            Password recovery
                        </div>

                        <h2
                            class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Lupa password?
                        </h2>

                        <p
                            class="mt-3 text-sm leading-relaxed text-slate-500"
                        >
                            Jangan khawatir. Masukkan email akun kamu
                            untuk menerima link reset password.
                        </p>
                    </div>

                    <!-- FORM -->
                    <Form
                        v-bind="email.form()"
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
                                        Recovery progress
                                    </span>

                                    <span
                                        class="text-xs font-semibold"
                                        :class="
                                            emailIsValid
                                                ? 'text-emerald-500'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{
                                            emailIsValid
                                                ? 'Ready'
                                                : 'Enter email'
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="h-1.5 overflow-hidden rounded-full bg-slate-100"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-500"
                                        :style="{
                                            width: emailIsValid
                                                ? '100%'
                                                : emailValue
                                                  ? '50%'
                                                  : '0%',
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
                                        v-model="emailValue"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        autofocus
                                        required
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
                                        Link reset aman
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-relaxed text-slate-500"
                                    >
                                        Link reset akan dikirim ke
                                        email yang terdaftar pada akun.
                                    </p>
                                </div>
                            </div>

                            <!-- STATUS -->
                            <div
                                v-if="status"
                                class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-700"
                            >
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0"
                                />

                                <span>
                                    {{ status }}
                                </span>
                            </div>

                            <!-- BUTTON -->
                            <Button
                                type="submit"
                                class="mt-1 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />

                                <template v-else>
                                    <Mail
                                        v-if="!emailIsValid"
                                        class="mr-2 size-4"
                                    />

                                    <Check
                                        v-else
                                        class="mr-2 size-4"
                                    />

                                    <span>
                                        {{
                                            emailIsValid
                                                ? 'Kirim link reset'
                                                : 'Email password reset link'
                                        }}
                                    </span>
                                </template>
                            </Button>
                        </div>

                        <!-- LOGIN -->
                        <div
                            class="border-t border-slate-100 pt-5 text-center text-sm text-slate-500"
                        >
                            Ingat password kamu?

                            <TextLink
                                :href="login()"
                                class="ml-1 font-semibold text-sky-500 transition hover:text-sky-600"
                            >
                                Kembali ke Login
                            </TextLink>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>