<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
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
                />

                <div
                    class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"
                />

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
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
                        >
                            <p class="text-base font-bold text-white">
                                Creator
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Intelligence
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
                        >
                            <p class="text-base font-bold text-white">
                                Affiliate
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                Performance
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
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
                class="flex min-h-screen items-center justify-center bg-white px-6 py-10 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-lg">

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
                        <p
                            class="mb-3 text-xs font-bold uppercase tracking-[0.25em] text-sky-500"
                        >
                            Get started
                        </p>

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
                        :reset-on-success="['password', 'password_confirmation']"
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
                                    name="name"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="Your name"
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
                                    Email address
                                </Label>

                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    placeholder="email@example.com"
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

                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    :tabindex="3"
                                    autocomplete="new-password"
                                    placeholder="Password"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                />

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

                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                    class="h-12 rounded-xl border-slate-200 bg-slate-50 px-4 text-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-400/10"
                                />

                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>

                            <!-- BUTTON -->
                            <Button
                                type="submit"
                                class="mt-1 h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-400 hover:shadow-blue-500/30"
                                :tabindex="5"
                                :disabled="processing"
                                data-test="register-button"
                            >
                                <Spinner v-if="processing" />

                                <span v-else>
                                    Create account
                                </span>
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