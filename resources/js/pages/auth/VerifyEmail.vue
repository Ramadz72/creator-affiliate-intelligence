<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, MailCheck, ShieldCheck } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
    email?: string;
}>();
</script>

<template>
    <Head title="Verifikasi email" />

    <div class="min-h-screen bg-slate-950">
        <div class="grid min-h-screen lg:grid-cols-2">
            <div
                class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-12 lg:flex lg:flex-col lg:justify-between xl:p-16"
            >
                <div
                    class="absolute top-1/4 -left-32 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"
                />

                <div
                    class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"
                />

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

                <div class="relative z-10">
                    <img
                        src="/images/creator kecil.webp"
                        alt="Creator Affiliate Intelligence"
                        class="h-14 w-auto object-contain"
                    />
                </div>

                <div class="relative z-10 max-w-xl">
                    <p
                        class="mb-4 text-sm font-semibold tracking-[0.25em] text-sky-400 uppercase"
                    >
                        Verifikasi Akun
                    </p>

                    <h1
                        class="font-heading text-5xl leading-tight font-extrabold tracking-tight text-white xl:text-6xl"
                    >
                        Satu langkah lagi
                        <span class="text-sky-400"> untuk mulai. </span>
                    </h1>

                    <p
                        class="mt-6 max-w-lg text-lg leading-relaxed text-slate-400"
                    >
                        Verifikasi alamat email kamu untuk mengamankan akun dan
                        membuka seluruh fitur analitik creator & affiliate.
                    </p>

                    <div
                        class="mt-10 flex max-w-lg items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
                    >
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-sky-400/15 text-sky-300"
                        >
                            <MailCheck class="size-5" />
                        </div>

                        <div>
                            <p class="font-semibold text-white">
                                Cek inbox kamu
                            </p>

                            <p
                                class="mt-1 text-sm leading-relaxed text-slate-400"
                            >
                                Klik tautan di email verifikasi yang baru saja
                                kami kirim.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 text-sm text-slate-500">
                    Creator & Affiliate Intelligence
                </div>
            </div>

            <div
                class="relative flex min-h-screen items-center justify-center overflow-y-auto bg-white px-6 py-8 sm:px-10 lg:px-14 xl:px-20"
            >
                <div class="w-full max-w-md">
                    <div class="mb-7">
                        <Link
                            href="/"
                            class="group inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            <ArrowLeft
                                class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                            />

                            <span> Kembali ke Welcome </span>
                        </Link>
                    </div>

                    <div class="mb-7 flex justify-center lg:hidden">
                        <img
                            src="/images/creator kecil.webp"
                            alt="Creator Affiliate Intelligence"
                            class="h-14 w-auto object-contain"
                        />
                    </div>

                    <div class="mb-7">
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-600"
                        >
                            <ShieldCheck class="size-3.5" />

                            Keamanan akun
                        </div>

                        <h2
                            class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Verifikasi alamat email Anda
                        </h2>

                        <p class="mt-3 text-sm leading-relaxed text-slate-500">
                            Kami sudah mengirimkan tautan verifikasi
                            <span
                                v-if="email"
                                class="font-medium text-slate-700"
                            >
                                ke {{ email }} </span
                            >. Klik tautan tersebut sebelum mengakses dashboard.
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-600"
                    >
                        {{
                            status === 'verification-link-sent'
                                ? 'Tautan verifikasi baru sudah dikirim ke email kamu.'
                                : status
                        }}
                    </div>

                    <Form
                        v-bind="send.form()"
                        :options="{ preserveScroll: true }"
                        v-slot="{ processing }"
                    >
                        <Button
                            type="submit"
                            class="h-12 w-full rounded-xl bg-blue-500 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-400 hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="processing"
                        >
                            <CheckCircle2 v-if="!processing" class="size-4" />

                            {{
                                processing
                                    ? 'Mengirim...'
                                    : 'Kirim ulang tautan'
                            }}
                        </Button>
                    </Form>

                    <Link
                        :href="logout.url()"
                        method="post"
                        as="button"
                        class="mt-6 block w-full text-center text-sm font-medium text-slate-500 transition hover:text-slate-900"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
