<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    status?: string;
}>();

const mode = ref<'code' | 'recovery'>('code');
</script>

<template>
    <Head title="Verifikasi dua langkah" />

    <div
        class="flex min-h-screen items-center justify-center bg-slate-950 px-6 py-12"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/60 p-8 text-slate-100 shadow-2xl"
        >
            <div
                class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/15 text-blue-400"
            >
                <ShieldCheck class="h-6 w-6" />
            </div>

            <h1 class="text-xl font-semibold">Verifikasi dua langkah</h1>

            <p class="mt-3 text-sm leading-6 text-slate-400">
                Masukkan kode dari aplikasi autentikator Anda untuk melanjutkan.
            </p>

            <p
                v-if="status"
                class="mt-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs text-emerald-400"
            >
                {{ status }}
            </p>

            <Form
                action="/two-factor-challenge"
                method="post"
                :options="{ preserveScroll: true }"
                class="mt-6 space-y-5"
                v-slot="{ errors, processing }"
            >
                <div v-if="mode === 'code'" class="grid gap-2">
                    <Label for="code">Kode autentikator</Label>

                    <Input
                        id="code"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        autofocus
                    />

                    <InputError :message="errors.code" />

                    <button
                        type="button"
                        class="mt-1 text-left text-xs text-blue-400 hover:text-blue-300"
                        @click="mode = 'recovery'"
                    >
                        Gunakan recovery code
                    </button>
                </div>

                <div v-else class="grid gap-2">
                    <Label for="recovery_code">Recovery code</Label>

                    <Input
                        id="recovery_code"
                        name="recovery_code"
                        autocomplete="one-time-code"
                        placeholder="abcdef-123456"
                        autofocus
                    />

                    <InputError :message="errors.recovery_code" />

                    <button
                        type="button"
                        class="mt-1 text-left text-xs text-blue-400 hover:text-blue-300"
                        @click="mode = 'code'"
                    >
                        Gunakan kode autentikator
                    </button>
                </div>

                <Button type="submit" class="w-full" :disabled="processing">
                    {{ processing ? 'Memverifikasi...' : 'Lanjutkan' }}
                </Button>
            </Form>
        </div>
    </div>
</template>
