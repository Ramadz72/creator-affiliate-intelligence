<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save } from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    profile_image: string | null
}

interface Campaign {
    id: number
    campaign_name: string
    product_name: string
    agreed_price: string | number
    creator: Creator
}

interface Performance {
    id: number
    views: number
    likes: number
    comments: number
    shares: number
    saves: number
    clicks: number
    orders: number
    buyers: number
    gmv: string | number
}

const props = defineProps<{
    campaign: Campaign
    performance: Performance
}>()

const form = useForm({
    views: props.performance.views,
    likes: props.performance.likes,
    comments: props.performance.comments,
    shares: props.performance.shares,
    saves: props.performance.saves,
    clicks: props.performance.clicks,
    orders: props.performance.orders,
    buyers: props.performance.buyers,
    gmv: String(props.performance.gmv),
})

const submit = () => {
    form.put(
        `/campaigns/${props.campaign.id}/performance/${props.performance.id}`,
    )
}

const rupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value))
}
</script>

<template>
    <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

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
                    Edit Actual Performance
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Perbarui hasil aktual campaign
                    <span class="font-medium text-foreground">
                        {{ campaign.campaign_name }}
                    </span>.
                </p>
            </div>
        </div>

        <!-- Campaign Info -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center gap-4">

                <div
                    class="h-12 w-12 shrink-0 overflow-hidden rounded-full border border-border bg-muted"
                >
                    <img
                        v-if="campaign.creator?.profile_image"
                        :src="`/storage/${campaign.creator.profile_image}`"
                        :alt="campaign.creator.name"
                        class="h-full w-full object-cover"
                    />

                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center font-semibold text-muted-foreground"
                    >
                        {{ campaign.creator?.name?.charAt(0).toUpperCase() }}
                    </div>
                </div>

                <div>
                    <p class="font-semibold">
                        {{ campaign.creator?.name }}
                    </p>

                    <p class="text-sm text-muted-foreground">
                        @{{ campaign.creator?.username }}
                    </p>
                </div>

                <div class="ml-auto text-right">
                    <p class="text-xs text-muted-foreground">
                        Agreed Price
                    </p>

                    <p class="font-semibold">
                        {{ rupiah(campaign.agreed_price) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form
            @submit.prevent="submit"
            class="rounded-lg border border-border bg-card shadow-sm"
        >
            <div class="space-y-8 p-6">

                <!-- Engagement -->
                <div>
                    <div class="mb-4">
                        <h2 class="font-semibold">
                            Engagement Metrics
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Perbarui performa konten campaign.
                        </p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Views
                            </label>

                            <input
                                v-model="form.views"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Likes
                            </label>

                            <input
                                v-model="form.likes"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Comments
                            </label>

                            <input
                                v-model="form.comments"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Shares
                            </label>

                            <input
                                v-model="form.shares"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Saves
                            </label>

                            <input
                                v-model="form.saves"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>
                    </div>
                </div>

                <!-- Conversion -->
                <div class="border-t border-border pt-8">
                    <div class="mb-4">
                        <h2 class="font-semibold">
                            Conversion Metrics
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Perbarui data klik dan transaksi campaign.
                        </p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Clicks
                            </label>

                            <input
                                v-model="form.clicks"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Orders
                            </label>

                            <input
                                v-model="form.orders"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Buyers
                            </label>

                            <input
                                v-model="form.buyers"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="border-t border-border pt-8">
                    <div class="mb-4">
                        <h2 class="font-semibold">
                            Revenue
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Perbarui total GMV yang dihasilkan campaign.
                        </p>
                    </div>

                    <div class="max-w-xl">
                        <label class="mb-2 block text-sm font-medium">
                            GMV
                        </label>

                        <div class="flex">
                            <div
                                class="flex items-center rounded-l-lg border border-r-0 border-input bg-muted/40 px-4 text-sm font-medium text-muted-foreground"
                            >
                                Rp
                            </div>

                            <input
                                v-model="form.gmv"
                                type="number"
                                min="0"
                                class="w-full rounded-r-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/20"
                            />
                        </div>

                        <p class="mt-1.5 text-xs text-muted-foreground">
                            Masukkan total GMV yang dapat diatribusikan ke campaign.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex justify-end gap-3 border-t border-border bg-muted/20 px-6 py-4"
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