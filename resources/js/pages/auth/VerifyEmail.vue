<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Mail } from '@lucide/vue';

import { Button } from '@/components/ui/button';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Verifikasi email" />

    <div
        class="flex min-h-screen items-center justify-center bg-slate-950 px-6 py-12"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/60 p-8 text-slate-100 shadow-2xl"
        >
            <div
                class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400"
            >
                <Mail class="h-6 w-6" />
            </div>

            <h1 class="text-xl font-semibold">Verifikasi alamat email Anda</h1>

            <p class="mt-3 text-sm leading-6 text-slate-400">
                Kami sudah mengirimkan tautan verifikasi ke email Anda. Klik
                tautan tersebut sebelum mengakses dashboard.
            </p>

            <p
                v-if="status"
                class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs text-emerald-400"
            >
                {{ status }}
            </p>

            <Form
                action="/email/verification-notification"
                method="post"
                :options="{ preserveScroll: true }"
                class="mt-6 space-y-3"
                v-slot="{ processing }"
            >
                <Button type="submit" class="w-full" :disabled="processing">
                    {{ processing ? 'Mengirim...' : 'Kirim ulang tautan' }}
                </Button>
            </Form>

            <Link
                href="/logout"
                method="post"
                as="button"
                class="mt-4 block w-full text-center text-xs text-slate-400 hover:text-slate-200"
            >
                Keluar
            </Link>
        </div>
    </div>
</template>
