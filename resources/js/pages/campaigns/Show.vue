<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
    Activity,
    ArrowLeft,
    ArrowUpRight,
    BarChart3,
    CalendarDays,
    Check,
    ChevronDown,
    ChevronRight,
    CircleDollarSign,
    Clock3,
    Eye,
    FileText,
    Megaphone,
    MousePointerClick,
    Package,
    Pencil,
    ShoppingBag,
    Target,
    Trash2,
    TrendingDown,
    TrendingUp,
    Users,
    WalletCards,
    X,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    profile_image: string | null
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
    engagement_rate: string | number
    conversion_rate: string | number
    cost_per_view: string | number
    cost_per_order: string | number
    roas: string | number
    roi: string | number
}

interface Campaign {
    id: number
    campaign_name: string
    product_name: string
    platform: string
    deliverable: string
    agreed_price: string | number
    start_date: string | null
    end_date: string | null
    status: 'planned' | 'running' | 'completed' | 'cancelled'
    notes: string | null
    creator: Creator
    performances: Performance[]
}

type ChartMetric = 'views' | 'gmv' | 'orders' | 'roas' | 'roi'

const props = defineProps<{
    campaign: Campaign
}>()

const activeTab = ref<'overview' | 'performance'>('overview')
const expandedPerformance = ref<number | null>(null)
const showDeleteModal = ref(false)
const deleting = ref(false)
const selectedMetric = ref<ChartMetric>('views')
const chartSection = ref<HTMLElement | null>(null)

const performance = computed(() => {
    return props.campaign.performances[0] ?? null
})

const hasPerformance = computed(() => {
    return props.campaign.performances.length > 0
})

const formatRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const formatEfficiencyRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0))
}

const formatNumber = (value: string | number) => {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0))
}

const formatCompact = (value: string | number) => {
    const number = Number(value || 0)

    if (number >= 1_000_000_000) {
        return `${(number / 1_000_000_000).toFixed(1)}B`
    }

    if (number >= 1_000_000) {
        return `${(number / 1_000_000).toFixed(1)}M`
    }

    if (number >= 1_000) {
        return `${(number / 1_000).toFixed(1)}K`
    }

    return String(number)
}

const formatPercent = (value: string | number) => {
    return `${Number(value || 0).toFixed(2)}%`
}

const formatRoas = (value: string | number) => {
    return `${Number(value || 0).toFixed(2)}x`
}

const formatRoi = (value: string | number) => {
    return `${Number(value || 0).toFixed(2)}%`
}

const formatDate = (date: string | null | undefined) => {
    if (
        !date ||
        date === '0000-00-00' ||
        date === '0000-00-00 00:00:00'
    ) {
        return '-'
    }

    const parsedDate = new Date(date)

    if (Number.isNaN(parsedDate.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsedDate)
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
        running:
            'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        completed:
            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        cancelled:
            'bg-red-500/10 text-red-600 dark:text-red-400',
    }

    return classes[status]
}

const campaignDuration = computed(() => {
    if (!props.campaign.start_date) return 1

    const start = new Date(
        `${props.campaign.start_date}T00:00:00`,
    )

    const end = new Date(
        `${props.campaign.end_date || props.campaign.start_date}T00:00:00`,
    )

    return Math.max(
        1,
        Math.ceil(
            (end.getTime() - start.getTime()) /
                (1000 * 60 * 60 * 24),
        ) + 1,
    )
})

const engagementTotal = computed(() => {
    if (!performance.value) return 0

    return (
        Number(performance.value.likes || 0) +
        Number(performance.value.comments || 0) +
        Number(performance.value.shares || 0) +
        Number(performance.value.saves || 0)
    )
})

const conversionRate = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.conversion_rate || 0)
})

const engagementRate = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.engagement_rate || 0)
})

const roas = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.roas || 0)
})

const roi = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.roi || 0)
})

const gmv = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.gmv || 0)
})

const views = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.views || 0)
})

const orders = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.orders || 0)
})

const buyers = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.buyers || 0)
})

const clicks = computed(() => {
    if (!performance.value) return 0

    return Number(performance.value.clicks || 0)
})

const roasStatus = computed(() => {
    if (!hasPerformance.value) {
        return {
            label: 'No Data',
            description: 'Belum ada data performance.',
            class: 'bg-muted text-muted-foreground',
            textClass: 'text-muted-foreground',
        }
    }

    if (roas.value >= 3) {
        return {
            label: 'Excellent',
            description:
                'GMV mencapai lebih dari 3x nilai campaign.',
            class:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            textClass:
                'text-emerald-600 dark:text-emerald-400',
        }
    }

    if (roas.value >= 1.5) {
        return {
            label: 'Healthy',
            description:
                'GMV menghasilkan return yang cukup kuat terhadap biaya campaign.',
            class:
                'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            textClass:
                'text-sky-600 dark:text-sky-400',
        }
    }

    if (roas.value >= 1) {
        return {
            label: 'Monitor',
            description:
                'GMV sudah menutup biaya campaign, tetapi masih perlu dipantau.',
            class:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            textClass:
                'text-amber-600 dark:text-amber-400',
        }
    }

    return {
        label: 'Need Attention',
        description: 'GMV masih di bawah biaya campaign.',
        class:
            'bg-red-500/10 text-red-600 dark:text-red-400',
        textClass:
            'text-red-600 dark:text-red-400',
    }
})

const roiStatus = computed(() => {
    if (!hasPerformance.value) {
        return {
            label: 'No Data',
            class: 'text-muted-foreground',
        }
    }

    if (roi.value >= 0) {
        return {
            label: 'Positive Return',
            class:
                'text-emerald-600 dark:text-emerald-400',
        }
    }

    return {
        label: 'Negative Return',
        class: 'text-red-600 dark:text-red-400',
    }
})

const engagementStatus = computed(() => {
    if (!hasPerformance.value) {
        return 'No Data'
    }

    if (engagementRate.value >= 5) {
        return 'Strong'
    }

    if (engagementRate.value >= 2) {
        return 'Healthy'
    }

    return 'Low'
})

const engagementStatusClass = computed(() => {
    if (!hasPerformance.value) {
        return 'text-muted-foreground'
    }

    if (engagementRate.value >= 5) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (engagementRate.value >= 2) {
        return 'text-sky-600 dark:text-sky-400'
    }

    return 'text-amber-600 dark:text-amber-400'
})

const performanceHealth = computed(() => {
    if (!hasPerformance.value) return 0

    let score = 0

    if (roas.value >= 3) score += 40
    else if (roas.value >= 1.5) score += 32
    else if (roas.value >= 1) score += 22
    else score += 8

    if (roi.value >= 100) score += 25
    else if (roi.value >= 50) score += 20
    else if (roi.value >= 0) score += 12
    else score += 5

    if (engagementRate.value >= 5) score += 20
    else if (engagementRate.value >= 2) score += 15
    else score += 8

    if (conversionRate.value >= 5) score += 15
    else if (conversionRate.value >= 2) score += 10
    else score += 5

    return Math.min(score, 100)
})

const performanceHealthLabel = computed(() => {
    if (performanceHealth.value >= 80) return 'Strong'
    if (performanceHealth.value >= 60) return 'Healthy'
    if (performanceHealth.value >= 40) return 'Monitor'

    return 'Need Attention'
})

const performanceHealthClass = computed(() => {
    if (performanceHealth.value >= 80) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (performanceHealth.value >= 60) {
        return 'text-sky-600 dark:text-sky-400'
    }

    if (performanceHealth.value >= 40) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-red-600 dark:text-red-400'
})

const campaignAction = computed(() => {
    if (!hasPerformance.value) {
        return {
            status: 'No Data',
            eyebrow: 'Performance Action',
            title: 'Add Performance Data',
            description:
                'Masukkan data aktual campaign terlebih dahulu untuk mendapatkan rekomendasi tindakan berdasarkan performa.',
            actions: [
                'Tambahkan data views, clicks, orders, dan GMV',
                'Review engagement dan conversion rate',
            ],
            cta: 'Add Performance',
            class: 'bg-muted text-muted-foreground',
            iconClass: 'bg-muted text-muted-foreground',
        }
    }

    if (roas.value >= 3) {
        return {
            status: 'Excellent',
            eyebrow: 'Recommended Action',
            title: 'Scale Campaign',
            description:
                'Campaign menghasilkan GMV-based return yang sangat kuat. Campaign dapat dipertimbangkan untuk diperbesar secara bertahap.',
            actions: [
                'Pertahankan strategi campaign yang berjalan',
                'Tambah exposure atau budget secara bertahap',
                'Replikasi konten atau strategi creator yang berhasil',
                'Pertimbangkan memperpanjang kerja sama',
            ],
            cta: 'Scale Campaign',
            class:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            iconClass:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        }
    }

    if (roas.value >= 1.5) {
        return {
            status: 'Healthy',
            eyebrow: 'Recommended Action',
            title: 'Maintain & Optimize',
            description:
                'Campaign berada dalam kondisi sehat. Pertahankan strategi utama sambil mengoptimalkan area yang masih bisa ditingkatkan.',
            actions: [
                'Pertahankan creator dan strategi campaign',
                'Optimalkan engagement dan conversion',
                'Cari peluang meningkatkan GMV tanpa menaikkan biaya secara berlebihan',
                'Hindari perubahan besar pada campaign yang sudah sehat',
            ],
            cta: 'Optimize Campaign',
            class: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            iconClass: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        }
    }

    if (roas.value >= 1) {
        return {
            status: 'Monitor',
            eyebrow: 'Recommended Action',
            title: 'Review Performance',
            description:
                'GMV sudah menutup biaya campaign, tetapi return masih relatif terbatas sehingga perlu optimasi sebelum melakukan scale.',
            actions: [
                'Review CTR dan conversion performance',
                'Evaluasi efektivitas konten',
                'Optimalkan CTA dan product positioning',
                'Pantau performa sebelum menambah budget',
            ],
            cta: 'Review Performance',
            class:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            iconClass:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        }
    }

    return {
        status: 'Need Attention',
        eyebrow: 'Recommended Action',
        title: 'Investigate Campaign',
        description:
            'Campaign menghasilkan GMV di bawah biaya campaign. Evaluasi penyebabnya sebelum campaign dilanjutkan atau di-scale.',
        actions: [
            'Review creator performance',
            'Cek engagement dan conversion rate',
            'Evaluasi kualitas konten dan CTA',
            'Review product offer, pricing, atau promotion',
            'Jangan scale budget sebelum performa membaik',
        ],
        cta: 'Investigate Campaign',
        class: 'bg-red-500/10 text-red-600 dark:text-red-400',
        iconClass: 'bg-red-500/10 text-red-600 dark:text-red-400',
    }
})

const metricConfig = computed(() => {
    const configs: Record<
        ChartMetric,
        {
            label: string
            icon: typeof Eye
            color: string
            value: number
            formatted: string
        }
    > = {
        views: {
            label: 'Views',
            icon: Eye,
            color: '#0284c7',
            value: views.value,
            formatted: formatCompact(views.value),
        },
        gmv: {
            label: 'GMV',
            icon: CircleDollarSign,
            color: '#059669',
            value: gmv.value,
            formatted: formatRupiah(gmv.value),
        },
        orders: {
            label: 'Orders',
            icon: ShoppingBag,
            color: '#7c3aed',
            value: orders.value,
            formatted: formatNumber(orders.value),
        },
        roas: {
            label: 'ROAS',
            icon: TrendingUp,
            color: '#0284c7',
            value: roas.value,
            formatted: formatRoas(roas.value),
        },
        roi: {
            label: 'ROI',
            icon: roi.value >= 0 ? TrendingUp : TrendingDown,
            color: roi.value >= 0 ? '#059669' : '#dc2626',
            value: roi.value,
            formatted: formatRoi(roi.value),
        },
    }

    return configs[selectedMetric.value]
})

const chartData = computed(() => {
    return [...props.campaign.performances]
        .reverse()
        .map((item, index) => {
            let value = 0

            switch (selectedMetric.value) {
                case 'gmv':
                    value = Number(item.gmv || 0)
                    break

                case 'orders':
                    value = Number(item.orders || 0)
                    break

                case 'roas':
                    value = Number(item.roas || 0)
                    break

                case 'roi':
                    value = Number(item.roi || 0)
                    break

                default:
                    value = Number(item.views || 0)
            }

            return {
                label: `Record ${index + 1}`,
                value,
            }
        })
})

const performanceChartOption = computed(() => {
    const config = metricConfig.value

    return {
        animation: true,
        animationDuration: 500,
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(15, 23, 42, 0.94)',
            borderWidth: 0,
            textStyle: {
                color: '#fff',
                fontSize: 12,
            },
            formatter: (params: any[]) => {
                const point = params[0]

                if (!point) return ''

                let formatted = String(point.value)

                if (selectedMetric.value === 'views') {
                    formatted = formatNumber(point.value)
                }

                if (selectedMetric.value === 'gmv') {
                    formatted = formatRupiah(point.value)
                }

                if (selectedMetric.value === 'orders') {
                    formatted = formatNumber(point.value)
                }

                if (selectedMetric.value === 'roas') {
                    formatted = formatRoas(point.value)
                }

                if (selectedMetric.value === 'roi') {
                    formatted = formatRoi(point.value)
                }

                return `
                    <div style="padding: 4px 6px;">
                        <div style="font-size: 11px; opacity: .7; margin-bottom: 5px;">
                            ${point.axisValue}
                        </div>
                        <div style="font-size: 14px; font-weight: 600;">
                            ${config.label}: ${formatted}
                        </div>
                    </div>
                `
            },
        },
        grid: {
            top: 25,
            right: 20,
            bottom: 35,
            left: 55,
            containLabel: true,
        },
        xAxis: {
            type: 'category',
            data: chartData.value.map(
                (item) => item.label,
            ),
            boundaryGap: false,
            axisTick: {
                show: false,
            },
            axisLine: {
                lineStyle: {
                    color: '#e5e7eb',
                },
            },
            axisLabel: {
                color: '#6b7280',
                fontSize: 11,
            },
        },
        yAxis: {
            type: 'value',
            axisLine: {
                show: false,
            },
            axisTick: {
                show: false,
            },
            axisLabel: {
                color: '#6b7280',
                fontSize: 11,
                formatter: (value: number) => {
                    if (selectedMetric.value === 'gmv') {
                        return formatCompact(value)
                    }

                    if (selectedMetric.value === 'roas') {
                        return `${value}x`
                    }

                    if (selectedMetric.value === 'roi') {
                        return `${value}%`
                    }

                    return formatCompact(value)
                },
            },
            splitLine: {
                lineStyle: {
                    color: '#e5e7eb',
                    type: 'dashed',
                },
            },
        },
        series: [
            {
                name: config.label,
                type: 'line',
                data: chartData.value.map(
                    (item) => item.value,
                ),
                smooth: true,
                symbol: 'circle',
                symbolSize: 9,
                showSymbol: true,
                lineStyle: {
                    width: 3,
                    color: config.color,
                },
                itemStyle: {
                    color: config.color,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                },
                areaStyle: {
                    color: config.color,
                    opacity: 0.08,
                },
            },
        ],
    }
})

const selectMetric = async (metric: ChartMetric) => {
    selectedMetric.value = metric

    await nextTick()

    chartSection.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    })
}

const togglePerformance = (id: number) => {
    expandedPerformance.value =
        expandedPerformance.value === id
            ? null
            : id
}

const confirmDelete = () => {
    deleting.value = true

    router.delete(`/campaigns/${props.campaign.id}`, {
        onFinish: () => {
            deleting.value = false
            showDeleteModal.value = false
        },
    })
}
</script>

<template>
    <div
        class="app-textured-bg min-h-full w-full px-4 py-6 sm:px-6 lg:px-8"
    >
        <!-- HEADER -->
        <div class="mb-6">
            <div class="mb-4 flex items-center gap-2 text-sm">
                <Link
                    href="/campaigns"
                    class="inline-flex items-center gap-1.5 text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Campaign
                </Link>

                <ChevronRight
                    class="h-4 w-4 text-muted-foreground/40"
                />

                <span class="truncate text-foreground">
                    {{ campaign.campaign_name }}
                </span>
            </div>

            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <div class="flex min-w-0 items-center gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white shadow-sm transition-transform duration-200 hover:scale-105"
                    >
                        <Megaphone class="h-5 w-5" />
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="truncate text-xl font-bold tracking-tight md:text-2xl"
                            >
                                {{ campaign.campaign_name }}
                            </h1>

                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium transition-transform duration-200 hover:-translate-y-0.5"
                                :class="statusClass(campaign.status)"
                            >
                                {{ statusLabel(campaign.status) }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ campaign.product_name }}
                            ·
                            {{ campaign.platform }}
                            ·
                            {{ campaign.deliverable }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="`/campaigns/${campaign.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-md"
                    >
                        <Pencil class="h-4 w-4" />
                        Edit Campaign
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400"
                        @click="showDeleteModal = true"
                    >
                        <Trash2 class="h-4 w-4" />
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- COMMAND CENTER -->
        <div
            class="mb-5 overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-shadow duration-300 hover:shadow-md"
        >
            <div class="relative overflow-hidden border-b border-border p-6">
                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-60 w-60 rounded-full bg-sky-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-center"
                >
                    <div class="flex min-w-0 items-center gap-4">
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-muted font-semibold text-muted-foreground ring-4 ring-muted/40 transition-transform duration-300 hover:scale-105"
                        >
                            <img
                                v-if="campaign.creator?.profile_image"
                                :src="`/storage/${campaign.creator.profile_image}`"
                                :alt="campaign.creator.name"
                                class="h-full w-full object-cover"
                            />

                            <span v-else>
                                {{
                                    campaign.creator?.name
                                        ?.charAt(0)
                                        .toUpperCase() || '?'
                                }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                            >
                                Creator / KOL
                            </p>

                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold">
                                    {{ campaign.creator?.name }}
                                </h2>

                                <span
                                    class="rounded-full bg-sky-500/10 px-2 py-0.5 text-[11px] font-medium text-sky-600 dark:text-sky-400"
                                >
                                    {{ campaign.creator?.platform }}
                                </span>
                            </div>

                            <p class="text-sm text-muted-foreground">
                                @{{ campaign.creator?.username }}
                            </p>
                        </div>
                    </div>

                    <div class="lg:text-right">
                        <p class="text-xs text-muted-foreground">
                            Agreed Price
                        </p>

                        <p class="mt-1 text-2xl font-bold tracking-tight">
                            {{ formatRupiah(campaign.agreed_price) }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ campaign.deliverable }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- QUICK KPI -->
            <div
                class="grid grid-cols-2 divide-x divide-y divide-border md:grid-cols-4 md:divide-y-0"
            >
                <button
                    type="button"
                    class="group p-5 text-left transition-colors hover:bg-muted/30"
                    @click="activeTab = 'performance'; selectMetric('views')"
                >
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Eye
                            class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                        />
                        <span class="text-xs">Views</span>
                    </div>

                    <p class="mt-2 text-xl font-bold">
                        {{ hasPerformance ? formatCompact(views) : '-' }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted-foreground">
                        {{ hasPerformance ? formatNumber(views) : 'Belum tersedia' }}
                    </p>
                </button>

                <button
                    type="button"
                    class="group p-5 text-left transition-colors hover:bg-muted/30"
                    @click="activeTab = 'performance'"
                >
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <Activity
                            class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                        />
                        <span class="text-xs">Engagement</span>
                    </div>

                    <p class="mt-2 text-xl font-bold">
                        {{ hasPerformance ? formatPercent(engagementRate) : '-' }}
                    </p>

                    <p
                        class="mt-1 text-[11px]"
                        :class="engagementStatusClass"
                    >
                        {{ hasPerformance ? engagementStatus : 'No Data' }}
                    </p>
                </button>

                <button
                    type="button"
                    class="group p-5 text-left transition-colors hover:bg-muted/30"
                    @click="activeTab = 'performance'"
                >
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <ShoppingBag
                            class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                        />
                        <span class="text-xs">Orders</span>
                    </div>

                    <p class="mt-2 text-xl font-bold">
                        {{ hasPerformance ? formatNumber(orders) : '-' }}
                    </p>

                    <p class="mt-1 text-[11px] text-muted-foreground">
                        {{ hasPerformance ? `${formatNumber(buyers)} buyers` : 'Belum tersedia' }}
                    </p>
                </button>

                <button
                    type="button"
                    class="group p-5 text-left transition-colors hover:bg-muted/30"
                    @click="activeTab = 'performance'; selectMetric('roas')"
                >
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <TrendingUp
                            class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                        />
                        <span class="text-xs">ROAS</span>
                    </div>

                    <p class="mt-2 text-xl font-bold">
                        {{ hasPerformance ? formatRoas(roas) : '-' }}
                    </p>

                    <p
                        class="mt-1 text-[11px]"
                        :class="
                            hasPerformance
                                ? roasStatus.textClass
                                : 'text-muted-foreground'
                        "
                    >
                        {{ hasPerformance ? roasStatus.label : 'No Data' }}
                    </p>
                </button>
            </div>
        </div>

        <!-- TABS -->
        <div
            class="mb-5 flex w-fit items-center gap-1 rounded-xl border border-border bg-card p-1 shadow-sm"
        >
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                :class="
                    activeTab === 'overview'
                        ? 'bg-sky-600 text-white shadow-sm'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                "
                @click="activeTab = 'overview'"
            >
                <Activity class="h-4 w-4" />
                Overview
            </button>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                :class="
                    activeTab === 'performance'
                        ? 'bg-sky-600 text-white shadow-sm'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                "
                @click="activeTab = 'performance'"
            >
                <BarChart3 class="h-4 w-4" />
                Performance
            </button>
        </div>

        <!-- OVERVIEW -->
        <div
            v-if="activeTab === 'overview'"
            class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]"
        >
            <div class="space-y-5">
                <!-- PERFORMANCE HEALTH -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm transition-all duration-200 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <Target class="h-5 w-5 text-sky-600" />

                                <h2 class="font-semibold">
                                    Campaign Performance
                                </h2>
                            </div>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Ringkasan performa aktual campaign.
                            </p>
                        </div>

                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="roasStatus.class"
                        >
                            {{ roasStatus.label }}
                        </span>
                    </div>

                    <div
                        v-if="hasPerformance"
                        class="mt-6 grid gap-4 sm:grid-cols-3"
                    >
                        <!-- ROAS -->
                        <button
                            type="button"
                            class="rounded-xl border border-transparent bg-muted/40 p-4 text-left transition-all duration-200 hover:-translate-y-0.5 hover:border-sky-500/20 hover:bg-muted/70"
                            @click="activeTab = 'performance'; selectMetric('roas')"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    ROAS
                                </span>

                                <TrendingUp class="h-4 w-4 text-sky-600" />
                            </div>

                            <p class="mt-3 text-2xl font-bold">
                                {{ formatRoas(roas) }}
                            </p>

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-background">
                                <div
                                    class="h-full rounded-full bg-sky-600 transition-all duration-700"
                                    :style="{
                                        width: `${Math.min(
                                            (roas / 3) * 100,
                                            100,
                                        )}%`,
                                    }"
                                />
                            </div>

                            <p class="mt-2 text-[11px] text-muted-foreground">
                                {{ roasStatus.description }}
                            </p>
                        </button>

                        <!-- ROI -->
                        <button
                            type="button"
                            class="rounded-xl border border-transparent bg-muted/40 p-4 text-left transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-500/20 hover:bg-muted/70"
                            @click="activeTab = 'performance'; selectMetric('roi')"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    ROI
                                </span>

                                <component
                                    :is="roi >= 0 ? TrendingUp : TrendingDown"
                                    class="h-4 w-4"
                                    :class="
                                        roi >= 0
                                            ? 'text-emerald-600'
                                            : 'text-red-600'
                                    "
                                />
                            </div>

                            <p class="mt-3 text-2xl font-bold">
                                {{ formatRoi(roi) }}
                            </p>

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-background">
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :class="
                                        roi >= 0
                                            ? 'bg-emerald-600'
                                            : 'bg-red-600'
                                    "
                                    :style="{
                                        width: `${Math.min(
                                            Math.abs(roi),
                                            100,
                                        )}%`,
                                    }"
                                />
                            </div>

                            <p class="mt-2 text-[11px] text-muted-foreground">
                                GMV dibandingkan dengan nilai campaign.
                            </p>
                        </button>

                        <!-- ENGAGEMENT -->
                        <button
                            type="button"
                            class="rounded-xl border border-transparent bg-muted/40 p-4 text-left transition-all duration-200 hover:-translate-y-0.5 hover:border-sky-500/20 hover:bg-muted/70"
                            @click="activeTab = 'performance'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    Engagement
                                </span>

                                <Activity class="h-4 w-4 text-sky-600" />
                            </div>

                            <p class="mt-3 text-2xl font-bold">
                                {{ formatPercent(engagementRate) }}
                            </p>

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-background">
                                <div
                                    class="h-full rounded-full bg-sky-600 transition-all duration-700"
                                    :style="{
                                        width: `${Math.min(
                                            engagementRate * 10,
                                            100,
                                        )}%`,
                                    }"
                                />
                            </div>

                            <p class="mt-2 text-[11px] text-muted-foreground">
                                {{ formatNumber(engagementTotal) }}
                                total interactions.
                            </p>
                        </button>
                    </div>

                    <div
                        v-else
                        class="mt-5 rounded-xl border border-dashed border-border bg-muted/20 p-8 text-center"
                    >
                        <BarChart3
                            class="mx-auto h-8 w-8 text-muted-foreground/50"
                        />

                        <p class="mt-3 text-sm font-medium">
                            Belum ada data performance
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Tambahkan performance untuk mulai melihat
                            intelligence campaign.
                        </p>
                    </div>
                </div>

                <!-- CAMPAIGN INFORMATION -->
                <div
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                >
                    <div class="border-b border-border p-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                            >
                                <Package class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold">
                                    Informasi Campaign
                                </h2>

                                <p class="text-xs text-muted-foreground">
                                    Detail kerja sama
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-px bg-border sm:grid-cols-2">
                        <div class="bg-card p-5">
                            <p class="text-xs text-muted-foreground">
                                Nama Campaign
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.campaign_name }}
                            </p>
                        </div>

                        <div class="bg-card p-5">
                            <p class="text-xs text-muted-foreground">
                                Produk
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.product_name }}
                            </p>
                        </div>

                        <div class="bg-card p-5">
                            <p class="text-xs text-muted-foreground">
                                Platform
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.platform }}
                            </p>
                        </div>

                        <div class="bg-card p-5">
                            <p class="text-xs text-muted-foreground">
                                Deliverable
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.deliverable }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- NOTES -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                        >
                            <FileText class="h-4 w-4" />
                        </div>

                        <div>
                            <h2 class="text-sm font-semibold">
                                Notes
                            </h2>

                            <p class="text-xs text-muted-foreground">
                                Catatan internal campaign
                            </p>
                        </div>
                    </div>

                    <p
                        v-if="campaign.notes"
                        class="mt-4 whitespace-pre-line text-sm leading-6 text-muted-foreground"
                    >
                        {{ campaign.notes }}
                    </p>

                    <p
                        v-else
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        Tidak ada catatan untuk campaign ini.
                    </p>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="space-y-5">
                <!-- CREATOR -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm transition-all duration-200 hover:shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold">
                                Creator Snapshot
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Creator yang digunakan campaign.
                            </p>
                        </div>

                        <Users class="h-4 w-4 text-muted-foreground" />
                    </div>

                    <div
                        v-if="campaign.creator"
                        class="mt-5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted font-semibold text-muted-foreground"
                            >
                                <img
                                    v-if="campaign.creator.profile_image"
                                    :src="`/storage/${campaign.creator.profile_image}`"
                                    :alt="campaign.creator.name"
                                    class="h-full w-full object-cover"
                                />

                                <span v-else>
                                    {{
                                        campaign.creator.name
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </span>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-semibold">
                                    {{ campaign.creator.name }}
                                </p>

                                <p class="truncate text-xs text-muted-foreground">
                                    @{{ campaign.creator.username }}
                                </p>

                                <span
                                    class="mt-2 inline-flex rounded-full bg-sky-500/10 px-2.5 py-1 text-[11px] font-medium text-sky-600 dark:text-sky-400"
                                >
                                    {{ campaign.creator.platform }}
                                </span>
                            </div>
                        </div>

                        <Link
                            :href="`/creators/${campaign.creator.id}`"
                            class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-xs font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                        >
                            Lihat Profil Creator
                            <ArrowUpRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- DEAL -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold">
                                Deal
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Nilai kerja sama campaign.
                            </p>
                        </div>

                        <CircleDollarSign
                            class="h-5 w-5 text-sky-600"
                        />
                    </div>

                    <p class="mt-5 text-2xl font-bold">
                        {{ formatRupiah(campaign.agreed_price) }}
                    </p>

                    <span
                        class="mt-3 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="statusClass(campaign.status)"
                    >
                        {{ statusLabel(campaign.status) }}
                    </span>
                </div>

                <!-- TIMELINE -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold">
                                Timeline
                            </h2>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ campaignDuration }} hari
                            </p>
                        </div>

                        <CalendarDays class="h-4 w-4 text-sky-600" />
                    </div>

                    <div class="mt-6 space-y-5">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-600 text-white"
                                >
                                    <Check class="h-3.5 w-3.5" />
                                </div>

                                <div class="mt-1 h-8 w-px bg-border" />
                            </div>

                            <div>
                                <p class="text-[11px] text-muted-foreground">
                                    Mulai
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatDate(campaign.start_date) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-muted text-muted-foreground"
                            >
                                <Clock3 class="h-3.5 w-3.5" />
                            </div>

                            <div>
                                <p class="text-[11px] text-muted-foreground">
                                    Selesai
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatDate(campaign.end_date) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COST EFFICIENCY -->
                <div
                    v-if="hasPerformance"
                    class="rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <WalletCards class="h-4 w-4 text-sky-600" />

                        <h2 class="text-sm font-semibold">
                            Cost Efficiency
                        </h2>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                Cost / View
                            </span>

                            <span class="text-sm font-semibold">
                                {{ formatEfficiencyRupiah(performance!.cost_per_view) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                Cost / Order
                            </span>

                            <span class="text-sm font-semibold">
                                {{ formatEfficiencyRupiah(performance!.cost_per_order) }}
                            </span>
                        </div>

                        <div class="border-t border-border pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    ROAS
                                </span>

                                <span
                                    class="text-sm font-bold"
                                    :class="roasStatus.textClass"
                                >
                                    {{ formatRoas(roas) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERFORMANCE -->
        <div
            v-else
            class="space-y-5"
        >
            <!-- HEADER -->
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-sky-600" />

                            <h2 class="font-semibold">
                                Performance Intelligence
                            </h2>
                        </div>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Eksplorasi performa aktual campaign secara lebih
                            mendalam.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="hasPerformance"
                            :href="`/campaigns/${campaign.id}/performance/${campaign.performances[0].id}/edit`"
                            class="inline-flex items-center gap-2 rounded-lg border border-border px-3.5 py-2 text-xs font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit Performance
                        </Link>

                        <Link
                            v-else
                            :href="`/campaigns/${campaign.id}/performance/create`"
                            class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                        >
                            Tambah Performance
                        </Link>
                    </div>
                </div>
            </div>

            <!-- NO PERFORMANCE -->
            <div
                v-if="!hasPerformance"
                class="rounded-xl border border-dashed border-border bg-card p-12 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500/10 text-sky-600"
                >
                    <BarChart3 class="h-6 w-6" />
                </div>

                <h3 class="mt-4 font-semibold">
                    Belum ada performance
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                    Masukkan data aktual campaign untuk melihat views,
                    engagement, conversion, GMV, ROAS, dan ROI.
                </p>

                <Link
                    :href="`/campaigns/${campaign.id}/performance/create`"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                >
                    Tambah Performance
                </Link>
            </div>

            <template v-else>
                <!-- PERFORMANCE HEALTH -->
                <div
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                >
                    <div class="grid lg:grid-cols-[1fr_280px]">
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Campaign Health
                                    </p>

                                    <h3 class="mt-1 text-xl font-bold">
                                        Performance Intelligence
                                    </h3>

                                    <p class="mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                                        Ringkasan kondisi campaign berdasarkan
                                        ROAS, ROI, engagement, dan conversion
                                        rate dari data performance yang tersedia.
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="roasStatus.class"
                                >
                                    {{ roasStatus.label }}
                                </span>
                            </div>

                            <div class="mt-6">
                                <div class="mb-2 flex items-end justify-between">
                                    <div>
                                        <span class="text-xs text-muted-foreground">
                                            Health Score
                                        </span>

                                        <p
                                            class="mt-1 text-3xl font-bold"
                                            :class="performanceHealthClass"
                                        >
                                            {{ performanceHealth }}
                                            <span class="text-base font-medium text-muted-foreground">
                                                / 100
                                            </span>
                                        </p>
                                    </div>

                                    <span
                                        class="text-xs font-semibold"
                                        :class="performanceHealthClass"
                                    >
                                        {{ performanceHealthLabel }}
                                    </span>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-sky-600 transition-all duration-700"
                                        :style="{
                                            width: `${performanceHealth}%`,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-t border-border bg-muted/20 p-6 lg:border-l lg:border-t-0"
                        >
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        ROAS
                                    </p>

                                    <p class="mt-1 text-xl font-bold">
                                        {{ formatRoas(roas) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        ROI
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-bold"
                                        :class="roiStatus.class"
                                    >
                                        {{ formatRoi(roi) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Engagement
                                    </p>

                                    <p class="mt-1 text-xl font-bold">
                                        {{ formatPercent(engagementRate) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Conversion
                                    </p>

                                    <p class="mt-1 text-xl font-bold">
                                        {{ formatPercent(conversionRate) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTION CENTER -->
                <div
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                >
                    <div class="grid lg:grid-cols-[minmax(0,1fr)_280px]">
                        <!-- MAIN ACTION -->
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                    :class="campaignAction.iconClass"
                                >
                                    <Target class="h-5 w-5" />
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p
                                            class="text-[11px] font-semibold uppercase tracking-[0.12em]"
                                            :class="campaignAction.class.split(' ').find((item) => item.startsWith('text-'))"
                                        >
                                            {{ campaignAction.eyebrow }}
                                        </p>

                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                            :class="campaignAction.class"
                                        >
                                            {{ campaignAction.status }}
                                        </span>
                                    </div>

                                    <h3 class="mt-2 text-xl font-bold tracking-tight">
                                        {{ campaignAction.title }}
                                    </h3>

                                    <p
                                        class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                                    >
                                        {{ campaignAction.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- RECOMMENDED ACTIONS -->
                            <div class="mt-6">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Recommended Actions
                                </p>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <div
                                        v-for="action in campaignAction.actions"
                                        :key="action"
                                        class="flex items-start gap-2.5 rounded-lg border border-border bg-muted/20 px-3.5 py-3 transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted/40"
                                    >
                                        <div
                                            class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                        >
                                            <Check class="h-3 w-3" />
                                        </div>

                                        <span class="text-xs leading-5 text-muted-foreground">
                                            {{ action }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION SUMMARY -->
                        <div
                            class="border-t border-border bg-muted/20 p-6 lg:border-l lg:border-t-0"
                        >
                            <p class="text-xs text-muted-foreground">
                                Current Return
                            </p>

                            <div class="mt-4">
                                <p
                                    class="text-3xl font-bold tracking-tight"
                                    :class="roasStatus.textClass"
                                >
                                    {{ formatRoas(roas) }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    ROAS
                                </p>
                            </div>

                            <div class="mt-5 border-t border-border pt-5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-muted-foreground">
                                        GMV
                                    </span>

                                    <span class="text-sm font-semibold">
                                        {{ formatRupiah(gmv) }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-muted-foreground">
                                        ROI
                                    </span>

                                    <span
                                        class="text-sm font-semibold"
                                        :class="roiStatus.class"
                                    >
                                        {{ formatRoi(roi) }}
                                    </span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <Link
                                v-if="hasPerformance"
                                :href="`/campaigns/${campaign.id}/performance/${campaign.performances[0].id}/edit`"
                                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                            >
                                {{ campaignAction.cta }}

                                <ArrowUpRight class="h-4 w-4" />
                            </Link>

                            <Link
                                v-else
                                :href="`/campaigns/${campaign.id}/performance/create`"
                                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                            >
                                {{ campaignAction.cta }}

                                <ArrowUpRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- KPI GRID -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <button
                        type="button"
                        class="group rounded-xl border border-border bg-card p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="selectMetric('views')"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                Total Views
                            </span>

                            <Eye
                                class="h-4 w-4 text-sky-600 transition-transform duration-200 group-hover:scale-110"
                            />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ formatCompact(views) }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ formatNumber(views) }} views
                        </p>
                    </button>

                    <button
                        type="button"
                        class="group rounded-xl border border-border bg-card p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="selectMetric('views')"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                Engagement
                            </span>

                            <Activity
                                class="h-4 w-4 text-sky-600 transition-transform duration-200 group-hover:scale-110"
                            />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ formatPercent(engagementRate) }}
                        </p>

                        <p
                            class="mt-1 text-xs"
                            :class="engagementStatusClass"
                        >
                            {{ engagementStatus }}
                        </p>
                    </button>

                    <button
                        type="button"
                        class="group rounded-xl border border-border bg-card p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="selectMetric('gmv')"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                GMV
                            </span>

                            <CircleDollarSign
                                class="h-4 w-4 text-emerald-600 transition-transform duration-200 group-hover:scale-110"
                            />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ formatRupiah(gmv) }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Revenue generated
                        </p>
                    </button>

                    <button
                        type="button"
                        class="group rounded-xl border border-border bg-card p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="selectMetric('roas')"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                ROAS
                            </span>

                            <TrendingUp
                                class="h-4 w-4 text-sky-600 transition-transform duration-200 group-hover:scale-110"
                            />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ formatRoas(roas) }}
                        </p>

                        <p
                            class="mt-1 text-xs"
                            :class="roasStatus.textClass"
                        >
                            {{ roasStatus.label }}
                        </p>
                    </button>

                    <button
                        type="button"
                        class="group rounded-xl border border-border bg-card p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="selectMetric('roi')"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">
                                ROI
                            </span>

                            <component
                                :is="roi >= 0 ? TrendingUp : TrendingDown"
                                class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                                :class="
                                    roi >= 0
                                        ? 'text-emerald-600'
                                        : 'text-red-600'
                                "
                            />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ formatRoi(roi) }}
                        </p>

                        <p
                            class="mt-1 text-xs"
                            :class="roiStatus.class"
                        >
                            {{ roiStatus.label }}
                        </p>
                    </button>
                </div>

                <!-- TREND -->
                <div
                    ref="chartSection"
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                >
                    <div class="border-b border-border p-5">
                        <div
                            class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <TrendingUp class="h-5 w-5 text-sky-600" />

                                    <div>
                                        <h3 class="font-semibold">
                                            Performance Trend
                                        </h3>

                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Eksplorasi metric performance campaign.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1 rounded-lg bg-muted p-1">
                                <button
                                    v-for="metric in [
                                        { key: 'views', label: 'Views' },
                                        { key: 'gmv', label: 'GMV' },
                                        { key: 'orders', label: 'Orders' },
                                        { key: 'roas', label: 'ROAS' },
                                        { key: 'roi', label: 'ROI' },
                                    ]"
                                    :key="metric.key"
                                    type="button"
                                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-all duration-200"
                                    :class="
                                        selectedMetric === metric.key
                                            ? 'bg-card text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    "
                                    @click="
                                        selectMetric(
                                            metric.key as ChartMetric,
                                        )
                                    "
                                >
                                    {{ metric.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-[1fr_220px]">
                        <div class="p-5">
                            <div
                                class="w-full"
                                style="height: 320px"
                            >
                                <VChart
                                    :option="performanceChartOption"
                                    autoresize
                                    style="width: 100%; height: 100%"
                                />
                            </div>
                        </div>

                        <div
                            class="border-t border-border bg-muted/20 p-5 lg:border-l lg:border-t-0"
                        >
                            <p class="text-xs text-muted-foreground">
                                Selected Metric
                            </p>

                            <div class="mt-3 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600"
                                >
                                    <component
                                        :is="metricConfig.icon"
                                        class="h-5 w-5"
                                    />
                                </div>

                                <div>
                                    <p class="text-sm font-medium">
                                        {{ metricConfig.label }}
                                    </p>

                                    <p class="text-xl font-bold">
                                        {{ metricConfig.formatted }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-border pt-5">
                                <p class="text-xs leading-5 text-muted-foreground">
                                    Klik metric di atas untuk mengubah
                                    visualisasi dan mengeksplorasi data
                                    performance yang tersedia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ENGAGEMENT + CONVERSION -->
                <div class="grid gap-5 lg:grid-cols-2">
                    <!-- ENGAGEMENT -->
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">
                                    Engagement Breakdown
                                </h3>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Interaksi yang dihasilkan.
                                </p>
                            </div>

                            <Activity class="h-5 w-5 text-sky-600" />
                        </div>

                        <div class="mt-6 space-y-4">
                            <div
                                v-for="item in [
                                    {
                                        label: 'Likes',
                                        value: performance!.likes,
                                        icon: '♥',
                                    },
                                    {
                                        label: 'Comments',
                                        value: performance!.comments,
                                        icon: '◌',
                                    },
                                    {
                                        label: 'Shares',
                                        value: performance!.shares,
                                        icon: '↗',
                                    },
                                    {
                                        label: 'Saves',
                                        value: performance!.saves,
                                        icon: '▢',
                                    },
                                ]"
                                :key="item.label"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-xs"
                                        >
                                            {{ item.icon }}
                                        </span>

                                        <span class="text-sm">
                                            {{ item.label }}
                                        </span>
                                    </div>

                                    <span class="text-sm font-semibold">
                                        {{ formatNumber(item.value) }}
                                    </span>
                                </div>

                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-sky-600 transition-all duration-700"
                                        :style="{
                                            width: `${
                                                engagementTotal
                                                    ? Math.min(
                                                          (Number(item.value) /
                                                              engagementTotal) *
                                                              100,
                                                          100,
                                                      )
                                                    : 0
                                            }%`,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex items-center justify-between border-t border-border pt-4"
                        >
                            <span class="text-xs text-muted-foreground">
                                Total interactions
                            </span>

                            <span class="font-bold">
                                {{ formatNumber(engagementTotal) }}
                            </span>
                        </div>
                    </div>

                    <!-- CONVERSION -->
                    <div
                        class="rounded-xl border border-border bg-card p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">
                                    Conversion Funnel
                                </h3>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Perjalanan audience hingga order.
                                </p>
                            </div>

                            <MousePointerClick
                                class="h-5 w-5 text-sky-600"
                            />
                        </div>

                        <div class="mt-6">
                            <div
                                class="relative overflow-hidden rounded-xl border border-border bg-muted/20 p-4 transition-all duration-200 hover:bg-muted/40"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-muted-foreground">
                                            Views
                                        </p>

                                        <p class="mt-1 text-xl font-bold">
                                            {{ formatNumber(views) }}
                                        </p>
                                    </div>

                                    <Eye class="h-5 w-5 text-muted-foreground" />
                                </div>
                            </div>

                            <div class="flex items-center gap-3 py-2 pl-5">
                                <div class="h-5 w-px bg-border" />

                                <span class="text-[11px] text-muted-foreground">
                                    CTR
                                    {{
                                        views > 0
                                            ? `${((clicks / views) * 100).toFixed(2)}%`
                                            : '0.00%'
                                    }}
                                </span>
                            </div>

                            <div
                                class="rounded-xl border border-border bg-muted/20 p-4 transition-all duration-200 hover:bg-muted/40"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-muted-foreground">
                                            Clicks
                                        </p>

                                        <p class="mt-1 text-xl font-bold">
                                            {{ formatNumber(clicks) }}
                                        </p>
                                    </div>

                                    <MousePointerClick class="h-5 w-5 text-muted-foreground" />
                                </div>
                            </div>

                            <div class="flex items-center gap-3 py-2 pl-5">
                                <div class="h-5 w-px bg-border" />

                                <span class="text-[11px] text-muted-foreground">
                                    Conversion
                                    {{ formatPercent(conversionRate) }}
                                </span>
                            </div>

                            <div
                                class="rounded-xl border border-sky-500/20 bg-sky-500/10 p-4 transition-all duration-200 hover:bg-sky-500/15"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-muted-foreground">
                                            Orders
                                        </p>

                                        <p class="mt-1 text-xl font-bold">
                                            {{ formatNumber(orders) }}
                                        </p>
                                    </div>

                                    <ShoppingBag class="h-5 w-5 text-sky-600" />
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex items-center justify-between border-t border-border pt-4"
                        >
                            <span class="text-xs text-muted-foreground">
                                Buyers
                            </span>

                            <span class="font-bold">
                                {{ formatNumber(buyers) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- EFFICIENCY -->
                <div
                    class="rounded-xl border border-border bg-card p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <WalletCards class="h-5 w-5 text-sky-600" />

                        <div>
                            <h3 class="font-semibold">
                                Campaign Efficiency
                            </h3>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Efisiensi biaya dan return campaign.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-lg bg-muted/40 p-4 transition-all duration-200 hover:-translate-y-0.5">
                            <p class="text-xs text-muted-foreground">
                                GMV
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ formatRupiah(gmv) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4 transition-all duration-200 hover:-translate-y-0.5">
                            <p class="text-xs text-muted-foreground">
                                Cost / View
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ formatEfficiencyRupiah(performance.cost_per_view) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4 transition-all duration-200 hover:-translate-y-0.5">
                            <p class="text-xs text-muted-foreground">
                                Cost / Order
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ formatEfficiencyRupiah(performance.cost_per_order) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg bg-sky-500/10 p-4 text-left transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-500/15"
                            @click="selectMetric('roas')"
                        >
                            <p class="text-xs text-muted-foreground">
                                ROAS
                            </p>

                            <p class="mt-2 text-lg font-bold text-sky-600">
                                {{ formatRoas(roas) }}
                            </p>
                        </button>

                        <button
                            type="button"
                            class="rounded-lg p-4 text-left transition-all duration-200 hover:-translate-y-0.5"
                            :class="
                                roi >= 0
                                    ? 'bg-emerald-500/10 hover:bg-emerald-500/15'
                                    : 'bg-red-500/10 hover:bg-red-500/15'
                            "
                            @click="selectMetric('roi')"
                        >
                            <p class="text-xs text-muted-foreground">
                                ROI
                            </p>

                            <p
                                class="mt-2 text-lg font-bold"
                                :class="
                                    roi >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-600 dark:text-red-400'
                                "
                            >
                                {{ formatRoi(roi) }}
                            </p>
                        </button>
                    </div>
                </div>

                <!-- PERFORMANCE RECORDS -->
                <div
                    class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                >
                    <div class="border-b border-border p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">
                                    Performance Records
                                </h3>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Riwayat data performance campaign.
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                            >
                                {{ campaign.performances.length }} record
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-border">
                        <div
                            v-for="(item, index) in campaign.performances"
                            :key="item.id"
                            class="transition-colors"
                            :class="
                                expandedPerformance === item.id
                                    ? 'bg-muted/20'
                                    : ''
                            "
                        >
                            <button
                                type="button"
                                class="w-full px-5 py-5 text-left transition-colors hover:bg-muted/30"
                                @click="togglePerformance(item.id)"
                            >
                                <div
                                    class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-xs font-bold text-sky-600"
                                        >
                                            {{ index + 1 }}
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold">
                                                Performance #{{ index + 1 }}
                                            </p>

                                            <p class="mt-1 text-xs text-muted-foreground">
                                                {{ formatNumber(item.views) }}
                                                views
                                                ·
                                                {{ formatNumber(item.orders) }}
                                                orders
                                                ·
                                                {{ formatNumber(item.buyers) }}
                                                buyers
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <div class="hidden text-right sm:block">
                                            <p class="text-[11px] text-muted-foreground">
                                                GMV
                                            </p>

                                            <p class="mt-1 text-sm font-semibold">
                                                {{ formatRupiah(item.gmv) }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-[11px] text-muted-foreground">
                                                ROAS
                                            </p>

                                            <p
                                                class="mt-1 text-sm font-semibold"
                                                :class="
                                                    Number(item.roas) >= 1
                                                        ? 'text-emerald-600 dark:text-emerald-400'
                                                        : 'text-red-600 dark:text-red-400'
                                                "
                                            >
                                                {{ formatRoas(item.roas) }}
                                            </p>
                                        </div>

                                        <div class="hidden text-right sm:block">
                                            <p class="text-[11px] text-muted-foreground">
                                                ROI
                                            </p>

                                            <p
                                                class="mt-1 text-sm font-semibold"
                                                :class="
                                                    Number(item.roi) >= 0
                                                        ? 'text-emerald-600 dark:text-emerald-400'
                                                        : 'text-red-600 dark:text-red-400'
                                                "
                                            >
                                                {{ formatRoi(item.roi) }}
                                            </p>
                                        </div>

                                        <ChevronDown
                                            class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                                            :class="
                                                expandedPerformance === item.id
                                                    ? 'rotate-180'
                                                    : ''
                                            "
                                        />
                                    </div>
                                </div>
                            </button>

                            <!-- EXPANDED -->
                            <div
                                v-if="expandedPerformance === item.id"
                                class="border-t border-border bg-muted/20 px-5 py-5"
                            >
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Views
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.views) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Engagement
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatPercent(item.engagement_rate) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Conversion
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatPercent(item.conversion_rate) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Buyers
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.buyers) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Likes
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.likes) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Comments
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.comments) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Shares
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.shares) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Saves
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.saves) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Clicks
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatNumber(item.clicks) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            GMV
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatRupiah(item.gmv) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Cost / View
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatEfficiencyRupiah(item.cost_per_view) }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-card p-4">
                                        <p class="text-[11px] text-muted-foreground">
                                            Cost / Order
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ formatEfficiencyRupiah(item.cost_per_order) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- DELETE MODAL -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="showDeleteModal = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-500/10 text-red-600"
                        >
                            <Trash2 class="h-5 w-5" />
                        </div>

                        <h3 class="mt-4 text-lg font-semibold">
                            Hapus campaign?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Campaign
                            <span class="font-medium text-foreground">
                                "{{ campaign.campaign_name }}"
                            </span>
                            akan dihapus dan tidak dapat dikembalikan.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-muted-foreground transition-all duration-200 hover:scale-105 hover:bg-muted hover:text-foreground"
                        @click="showDeleteModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                        @click="showDeleteModal = false"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        :disabled="deleting"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                        @click="confirmDelete"
                    >
                        <Trash2 class="h-4 w-4" />

                        {{
                            deleting
                                ? 'Menghapus...'
                                : 'Hapus Campaign'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>