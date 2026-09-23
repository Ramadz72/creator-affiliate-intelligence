<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-slate-950">
        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- LEFT : BRAND -->
            <div
                class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-12 xl:p-16"
            >
                <!-- background glow -->
                <div
                    class="absolute -left-32 top-1/4 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"
                />

                <div
                    class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"
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

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm"
                        >
                            <p class="text-2xl font-bold text-white">
                                Creator
                            </p>
                            <p class="mt-1 text-sm text-slate-400">
                                Intelligence
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm"
                        >
                            <p class="text-2xl font-bold text-white">
                                Affiliate
                            </p>
                            <p class="mt-1 text-sm text-slate-400">
                                Performance
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm"
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

            <!-- RIGHT : LOGIN -->
            <div
                class="flex min-h-screen items-center justify-center bg-white px-6 py-12 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-md">

                    <!-- Logo mobile -->
                    <div class="mb-8 flex justify-center lg:hidden">
                        <img
                            src="/images/creator kecil.png"
                            alt="Creator Affiliate Intelligence"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <div class="mb-8">
                        <p
                            class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-sky-500"
                        >
                            Welcome back
                        </p>

                        <h2
                            class="font-heading text-3xl font-extrabold tracking-tight text-slate-900"
                        >
                            Selamat datang kembali
                        </h2>

                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Masuk untuk melanjutkan ke Creator & Affiliate
                            Intelligence.
                        </p>
                    </div>

                    <!-- FORM KAMU YANG TADI -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-5">

                            <div class="grid gap-2">
                                <Label
                                    for="email"
                                    class="font-semibold text-slate-700"
                                >
                                    Email address
                                </Label>

                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 transition focus:border-sky-400 focus:bg-white focus:ring-sky-400/10"
                                />

                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
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
                                        class="text-sm font-medium text-sky-500 hover:text-sky-600"
                                    >
                                        Forgot your password?
                                    </TextLink>
                                </div>

                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Password"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 transition focus:border-sky-400 focus:bg-white focus:ring-sky-400/10"
                                />

                                <InputError :message="errors.password" />
                            </div>

                            <div class="flex items-center">
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
                            </div>

                            <Button
                                type="submit"
                                class="mt-2 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-400"
                                :tabindex="4"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />
                                <span v-else>Log in</span>
                            </Button>
                        </div>

                        <div
                            class="border-t border-slate-100 pt-5 text-center text-sm text-slate-500"
                        >
                            Don't have an account?

                            <TextLink
                                :href="register()"
                                class="font-semibold text-sky-500 hover:text-sky-600"
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