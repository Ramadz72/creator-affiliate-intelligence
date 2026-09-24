<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save } from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
}

interface Campaign {
    id: number
    creator_id: number
    campaign_name: string
    product_name: string
    platform: string
    deliverable: string
    agreed_price: string | number
    start_date: string
    end_date: string | null
    status: 'planned' | 'running' | 'completed' | 'cancelled'
    notes: string | null
}

const props = defineProps<{
    campaign: Campaign
    creators: Creator[]
}>()

const form = useForm({
    creator_id: String(props.campaign.creator_id),
    campaign_name: props.campaign.campaign_name,
    product_name: props.campaign.product_name,
    platform: props.campaign.platform,
    deliverable: props.campaign.deliverable,
    agreed_price: String(props.campaign.agreed_price),
    start_date: props.campaign.start_date
        ? props.campaign.start_date.substring(0, 10)
        : '',
    end_date: props.campaign.end_date
        ? props.campaign.end_date.substring(0, 10)
        : '',
    status: props.campaign.status,
    notes: props.campaign.notes ?? '',
})

const submit = () => {
    form.put(`/campaigns/${props.campaign.id}`)
}
</script>

<template>
    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-start gap-4">
            <Link
                :href="`/campaigns/${campaign.id}`"
                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-colors hover:bg-muted hover:text-foreground"
                title="Kembali"
            >
                <ArrowLeft class="h-5 w-5" />
            </Link>

            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Edit Campaign
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Perbarui informasi campaign dan detail kerja sama creator.
                </p>
            </div>
        </div>

        <!-- Form -->
        <form
            @submit.prevent="submit"
            class="rounded-lg border border-border bg-card shadow-sm"
        >
            <div class="space-y-6 p-6">

                <!-- Creator -->
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Creator
                    </label>

                    <select
                        v-model="form.creator_id"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                    >
                        <option
                            v-for="creator in creators"
                            :key="creator.id"
                            :value="String(creator.id)"
                        >
                            {{ creator.name }} (@{{ creator.username }})
                        </option>
                    </select>

                    <p
                        v-if="form.errors.creator_id"
                        class="mt-1.5 text-sm text-red-600"
                    >
                        {{ form.errors.creator_id }}
                    </p>
                </div>

                <!-- Campaign & Product -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Nama Campaign
                        </label>

                        <input
                            v-model="form.campaign_name"
                            type="text"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />

                        <p
                            v-if="form.errors.campaign_name"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.campaign_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Nama Produk
                        </label>

                        <input
                            v-model="form.product_name"
                            type="text"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />

                        <p
                            v-if="form.errors.product_name"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.product_name }}
                        </p>
                    </div>
                </div>

                <!-- Platform & Deliverable -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Platform
                        </label>

                        <select
                            v-model="form.platform"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        >
                            <option value="TikTok">TikTok</option>
                            <option value="Instagram">Instagram</option>
                            <option value="YouTube">YouTube</option>
                            <option value="Other">Other</option>
                        </select>

                        <p
                            v-if="form.errors.platform"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.platform }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Deliverable
                        </label>

                        <input
                            v-model="form.deliverable"
                            type="text"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />

                        <p
                            v-if="form.errors.deliverable"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.deliverable }}
                        </p>
                    </div>
                </div>

                <!-- Agreed Price -->
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Agreed Price
                    </label>

                    <div class="flex">
                        <div
                            class="flex items-center rounded-l-lg border border-r-0 border-input bg-muted/40 px-4 text-sm font-medium text-muted-foreground"
                        >
                            Rp
                        </div>

                        <input
                            v-model="form.agreed_price"
                            type="number"
                            min="0"
                            class="w-full rounded-r-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />
                    </div>

                    <p class="mt-1.5 text-xs text-muted-foreground">
                        Harga final yang telah disepakati dengan creator.
                    </p>

                    <p
                        v-if="form.errors.agreed_price"
                        class="mt-1.5 text-sm text-red-600"
                    >
                        {{ form.errors.agreed_price }}
                    </p>
                </div>

                <!-- Date -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Tanggal Mulai
                        </label>

                        <input
                            v-model="form.start_date"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />

                        <p
                            v-if="form.errors.start_date"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Tanggal Selesai
                        </label>

                        <input
                            v-model="form.end_date"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        />

                        <p
                            v-if="form.errors.end_date"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Status
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                    >
                        <option value="planned">
                            Planned
                        </option>

                        <option value="running">
                            Running
                        </option>

                        <option value="completed">
                            Completed
                        </option>

                        <option value="cancelled">
                            Cancelled
                        </option>
                    </select>

                    <p
                        v-if="form.errors.status"
                        class="mt-1.5 text-sm text-red-600"
                    >
                        {{ form.errors.status }}
                    </p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Notes
                    </label>

                    <textarea
                        v-model="form.notes"
                        rows="4"
                        class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                    ></textarea>

                    <p
                        v-if="form.errors.notes"
                        class="mt-1.5 text-sm text-red-600"
                    >
                        {{ form.errors.notes }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex items-center justify-end gap-3 border-t border-border bg-muted/20 px-6 py-4"
            >
                <Link
                    :href="`/campaigns/${campaign.id}`"
                    class="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                >
                    Batal
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Save class="h-4 w-4" />

                    {{
                        form.processing
                            ? 'Menyimpan...'
                            : 'Simpan Perubahan'
                    }}
                </button>
            </div>
        </form>
    </div>
</template>