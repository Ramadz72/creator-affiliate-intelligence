<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { Plus, Eye, Pencil, Trash2, Megaphone } from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
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
    creator: Creator
    created_at: string
}

defineProps<{
    campaigns: Campaign[]
}>()

const formatRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value))
}

const formatDate = (date: string | null) => {
    if (!date) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

const statusLabel = (status: Campaign['status']) => {
    const labels = {
        planned: 'Planned',
        running: 'Running',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }

    return labels[status]
}

const statusClass = (status: Campaign['status']) => {
    const classes = {
        planned: 'bg-muted text-muted-foreground',
        running: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        completed: 'bg-green-500/10 text-green-600 dark:text-green-400',
        cancelled: 'bg-red-500/10 text-red-600 dark:text-red-400',
    }

    return classes[status]
}

const deleteCampaign = (campaign: Campaign) => {
    if (!confirm(`Hapus campaign "${campaign.campaign_name}"?`)) {
        return
    }

    router.delete(`/campaigns/${campaign.id}`)
}
</script>

<template>
    <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-start justify-between gap-6">
            <div>
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-card"
                    >
                        <Megaphone class="h-5 w-5 text-muted-foreground" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Campaign
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Kelola campaign creator setelah proses analisis dan keputusan deal.
                        </p>
                    </div>
                </div>
            </div>

            <Link
                href="/campaigns/create"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
            >
                <Plus class="h-4 w-4" />
                Tambah Campaign
            </Link>
        </div>

        <!-- Empty State -->
        <div
            v-if="campaigns.length === 0"
            class="rounded-lg border border-border bg-card p-10 text-center shadow-sm"
        >
            <div
                class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
            >
                <Megaphone class="h-6 w-6 text-muted-foreground" />
            </div>

            <h2 class="text-base font-semibold">
                Belum ada campaign
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                Campaign dibuat setelah creator dianalisis dan keputusan kerja sama
                sudah ditentukan.
            </p>

            <Link
                href="/campaigns/create"
                class="mt-5 inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
            >
                <Plus class="h-4 w-4" />
                Tambah Campaign
            </Link>
        </div>

        <!-- Campaign Table -->
        <div
            v-else
            class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr class="text-left">
                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Campaign
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Creator
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Platform
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Deal
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Periode
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="campaign in campaigns"
                            :key="campaign.id"
                            class="transition-colors hover:bg-muted/20"
                        >
                            <!-- Campaign -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">
                                        {{ campaign.campaign_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ campaign.product_name }}
                                    </p>
                                </div>
                            </td>

                            <!-- Creator -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">
                                        {{ campaign.creator?.name ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        @{{ campaign.creator?.username ?? '-' }}
                                    </p>
                                </div>
                            </td>

                            <!-- Platform -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">
                                        {{ campaign.platform }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ campaign.deliverable }}
                                    </p>
                                </div>
                            </td>

                            <!-- Deal -->
                            <td class="px-6 py-4">
                                <p class="font-medium">
                                    {{ formatRupiah(campaign.agreed_price) }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Agreed price
                                </p>
                            </td>

                            <!-- Periode -->
                            <td class="px-6 py-4">
                                <p class="whitespace-nowrap">
                                    {{ formatDate(campaign.start_date) }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    s/d {{ formatDate(campaign.end_date) }}
                                </p>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusClass(campaign.status)"
                                >
                                    {{ statusLabel(campaign.status) }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    <Link
                                        :href="`/campaigns/${campaign.id}`"
                                        class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        title="Lihat"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>

                                    <Link
                                        :href="`/campaigns/${campaign.id}/edit`"
                                        class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        title="Edit"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Link>

                                    <button
                                        type="button"
                                        class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400"
                                        title="Hapus"
                                        @click="deleteCampaign(campaign)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>