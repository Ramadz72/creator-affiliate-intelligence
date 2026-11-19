<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    Clock3,
    ExternalLink,
    FileText,
    ImagePlus,
    Megaphone,
    Package,
    Pencil,
    Rocket,
    ShieldCheck,
    Sparkles,
    User,
    Users,
    WalletCards,
    Save,
    X,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    profile_image?: string | null;
}

const props = defineProps<{
    creators: Creator[];
}>();

const currentStep = ref(1);
const processing = ref(false);

const form = ref({
    creator_id: '',
    campaign_name: '',
    product_name: '',
    platform: '',
    deliverable: '',
    agreed_price: '',
    start_date: '',
    end_date: '',
    status: 'planned',
    notes: '',
});

const errors = ref<Record<string, string>>({});

const steps = [
    {
        number: 1,
        title: 'Campaign',
        description: 'Informasi utama',
        icon: Megaphone,
    },
    {
        number: 2,
        title: 'Deal & Timeline',
        description: 'Kerja sama',
        icon: CircleDollarSign,
    },
    {
        number: 3,
        title: 'Review',
        description: 'Konfirmasi',
        icon: CheckCircle2,
    },
];

const platforms = [
    {
        value: 'TikTok',
        short: 'TT',
    },
    {
        value: 'Instagram',
        short: 'IG',
    },
    {
        value: 'YouTube',
        short: 'YT',
    },
];

const deliverables = [
    '1x Video',
    '2x Video',
    '3x Video',
    '1x Live',
    '1x Video + 1x Live',
    'Custom',
];

const statuses = [
    {
        value: 'planned',
        label: 'Planned',
        description: 'Campaign belum dimulai',
        icon: CalendarDays,
    },
    {
        value: 'running',
        label: 'Running',
        description: 'Campaign sedang berjalan',
        icon: Zap,
    },
    {
        value: 'completed',
        label: 'Completed',
        description: 'Campaign sudah selesai',
        icon: CheckCircle2,
    },
    {
        value: 'cancelled',
        label: 'Cancelled',
        description: 'Campaign dibatalkan',
        icon: X,
    },
];

const selectedCreator = computed(() => {
    return props.creators.find(
        (creator) => String(creator.id) === String(form.value.creator_id),
    );
});

const reviewSections = ref({
    creator: true,
    campaign: true,
    deal: true,
    timeline: true,
    notes: false,
});

const toggleReviewSection = (section: keyof typeof reviewSections.value) => {
    reviewSections.value[section] = !reviewSections.value[section];
};

const readinessItems = computed(() => {
    return [
        {
            label: 'Creator dipilih',
            complete: !!form.value.creator_id,
        },
        {
            label: 'Informasi campaign lengkap',
            complete:
                !!form.value.campaign_name &&
                !!form.value.product_name &&
                !!form.value.platform,
        },
        {
            label: 'Deliverable ditentukan',
            complete: !!form.value.deliverable,
        },
        {
            label: 'Nilai kerja sama ditentukan',
            complete:
                !!form.value.agreed_price &&
                Number(form.value.agreed_price) > 0,
        },
        {
            label: 'Timeline ditentukan',
            complete: !!form.value.start_date,
        },
        {
            label: 'Status campaign ditentukan',
            complete: !!form.value.status,
        },
    ];
});

const completedReadiness = computed(() => {
    return readinessItems.value.filter((item) => item.complete).length;
});

const readiness = computed(() => {
    return Math.round(
        (completedReadiness.value / readinessItems.value.length) * 100,
    );
});

const campaignDuration = computed(() => {
    if (!form.value.start_date) return null;

    const start = new Date(`${form.value.start_date}T00:00:00`);
    const end = new Date(
        `${form.value.end_date || form.value.start_date}T00:00:00`,
    );

    const diff =
        Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) +
        1;

    return Math.max(diff, 1);
});

const formattedAgreedPrice = computed(() => {
    const value = Number(form.value.agreed_price || 0);

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
});

const formattedStartDate = computed(() => {
    if (!form.value.start_date) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${form.value.start_date}T00:00:00`));
});

const formattedEndDate = computed(() => {
    if (!form.value.end_date) {
        return formattedStartDate.value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${form.value.end_date}T00:00:00`));
});

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        planned: 'Planned',
        running: 'Running',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    return labels[form.value.status] || form.value.status;
});

const statusClass = computed(() => {
    const classes: Record<string, string> = {
        planned: 'bg-muted text-muted-foreground',
        running: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        completed: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        cancelled: 'bg-red-500/10 text-red-600 dark:text-red-400',
    };

    return classes[form.value.status] || classes.planned;
});

const selectedPlatform = computed(() => {
    return platforms.find((platform) => platform.value === form.value.platform);
});

const selectedStatus = computed(() => {
    return statuses.find((status) => status.value === form.value.status);
});

const formattedPrice = computed(() => {
    const value = Number(form.value.agreed_price);

    if (!value) return 'Rp 0';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
});

const isStepOneComplete = computed(() => {
    return Boolean(
        form.value.creator_id &&
        form.value.campaign_name &&
        form.value.product_name &&
        form.value.platform &&
        form.value.deliverable,
    );
});

const isStepTwoComplete = computed(() => {
    return Boolean(
        form.value.agreed_price && form.value.start_date && form.value.status,
    );
});

const isReady = computed(() => {
    return isStepOneComplete.value && isStepTwoComplete.value;
});

const clearError = (field: string) => {
    delete errors.value[field];
};

const validateStepOne = () => {
    const nextErrors: Record<string, string> = {};

    if (!form.value.creator_id) {
        nextErrors.creator_id = 'Pilih creator terlebih dahulu.';
    }

    if (!form.value.campaign_name.trim()) {
        nextErrors.campaign_name = 'Nama campaign wajib diisi.';
    }

    if (!form.value.product_name.trim()) {
        nextErrors.product_name = 'Nama produk wajib diisi.';
    }

    if (!form.value.platform) {
        nextErrors.platform = 'Pilih platform.';
    }

    if (!form.value.deliverable) {
        nextErrors.deliverable = 'Pilih deliverable.';
    }

    errors.value = nextErrors;

    return Object.keys(nextErrors).length === 0;
};

const validateStepTwo = () => {
    const nextErrors: Record<string, string> = {};

    if (!form.value.agreed_price) {
        nextErrors.agreed_price = 'Agreed price wajib diisi.';
    }

    if (form.value.agreed_price && Number(form.value.agreed_price) < 0) {
        nextErrors.agreed_price = 'Harga tidak boleh negatif.';
    }

    if (!form.value.start_date) {
        nextErrors.start_date = 'Tanggal mulai wajib diisi.';
    }

    if (
        form.value.start_date &&
        form.value.end_date &&
        new Date(form.value.end_date) < new Date(form.value.start_date)
    ) {
        nextErrors.end_date =
            'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    }

    if (!form.value.status) {
        nextErrors.status = 'Pilih status campaign.';
    }

    errors.value = nextErrors;

    return Object.keys(nextErrors).length === 0;
};

const nextStep = () => {
    if (currentStep.value === 1) {
        if (!validateStepOne()) return;
    }

    if (currentStep.value === 2) {
        if (!validateStepTwo()) return;
    }

    errors.value = {};

    if (currentStep.value < 3) {
        currentStep.value++;
    }
};

const previousStep = () => {
    errors.value = {};

    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goToStep = (step: number) => {
    if (step >= currentStep.value) return;

    currentStep.value = step;
    errors.value = {};
};

const submit = () => {
    if (!validateStepOne()) {
        currentStep.value = 1;
        return;
    }

    if (!validateStepTwo()) {
        currentStep.value = 2;
        return;
    }

    processing.value = true;

    router.post('/campaigns', form.value, {
        preserveScroll: true,

        onError: (serverErrors) => {
            errors.value = serverErrors;
        },

        onFinish: () => {
            processing.value = false;
        },
    });
};

const setPlatform = (platform: string) => {
    form.value.platform = platform;
    clearError('platform');
};

const setStatus = (status: string) => {
    form.value.status = status;
    clearError('status');
};

const selectCreator = (creatorId: string) => {
    form.value.creator_id = creatorId;
    clearError('creator_id');

    const creator = props.creators.find(
        (item) => String(item.id) === creatorId,
    );

    if (creator && !form.value.platform) {
        form.value.platform = creator.platform;
    }
};
</script>

<template>
    <Head title="Tambah Campaign" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <!-- Header -->
        <div class="mb-6 flex items-center gap-4">
            <Link
                href="/campaigns"
                class="group border-border bg-card hover:bg-muted inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border transition-all duration-200 hover:-translate-x-0.5 hover:shadow-sm"
            >
                <ArrowLeft
                    class="h-5 w-5 transition-transform duration-200 group-hover:-translate-x-0.5"
                />
            </Link>

            <div>
                <div class="flex items-center gap-2">
                    <div
                        class="border-border bg-card flex h-9 w-9 items-center justify-center rounded-lg border"
                    >
                        <Megaphone class="h-4 w-4" />
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight">
                        Tambah Campaign
                    </h1>
                </div>

                <p class="text-muted-foreground mt-1 text-sm">
                    Buat dan konfigurasi kerja sama baru dengan creator.
                </p>
            </div>
        </div>

        <!-- Stepper -->
        <div class="border-border bg-card mb-6 rounded-xl border p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <template v-for="(step, index) in steps" :key="step.number">
                    <button
                        type="button"
                        class="group flex min-w-0 items-center gap-3 text-left"
                        :disabled="step.number > currentStep"
                        @click="goToStep(step.number)"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border text-sm font-semibold transition-all duration-200"
                            :class="
                                step.number === currentStep
                                    ? 'border-primary text-primary-foreground bg-sky-600 shadow-sm'
                                    : step.number < currentStep
                                      ? 'border-green-500 bg-green-500 text-white'
                                      : 'border-border bg-muted text-muted-foreground'
                            "
                        >
                            <Check
                                v-if="step.number < currentStep"
                                class="h-4 w-4"
                            />

                            <component v-else :is="step.icon" class="h-4 w-4" />
                        </div>

                        <div class="hidden sm:block">
                            <p
                                class="text-sm font-medium"
                                :class="
                                    step.number === currentStep
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ step.title }}
                            </p>

                            <p class="text-muted-foreground text-xs">
                                {{ step.description }}
                            </p>
                        </div>
                    </button>

                    <div
                        v-if="index < steps.length - 1"
                        class="bg-border mx-3 h-px flex-1"
                    />
                </template>
            </div>
        </div>

        <!-- Main -->
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <!-- Form -->
            <div class="min-w-0">
                <!-- STEP 1 -->
                <div v-if="currentStep === 1" class="space-y-5">
                    <!-- Creator -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                            >
                                <Users class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="font-semibold">Creator</h2>

                                <p class="text-muted-foreground text-xs">
                                    Tentukan creator yang akan menjalankan
                                    campaign.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                v-for="creator in creators"
                                :key="creator.id"
                                type="button"
                                class="group flex items-center gap-3 rounded-xl border p-3 text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                :class="
                                    String(creator.id) === form.creator_id
                                        ? 'border-primary bg-primary/5 ring-primary/20 ring-1'
                                        : 'border-border bg-background hover:bg-muted/40'
                                "
                                @click="selectCreator(String(creator.id))"
                            >
                                <div
                                    class="bg-muted text-muted-foreground flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full text-xs font-semibold"
                                >
                                    <img
                                        v-if="creator.profile_image"
                                        :src="`/storage/${creator.profile_image}`"
                                        :alt="creator.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span v-else>
                                        {{
                                            creator.name.charAt(0).toUpperCase()
                                        }}
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">
                                        {{ creator.name }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-0.5 truncate text-xs"
                                    >
                                        @{{ creator.username }}
                                    </p>
                                </div>

                                <div
                                    v-if="
                                        String(creator.id) === form.creator_id
                                    "
                                    class="text-primary-foreground flex h-5 w-5 items-center justify-center rounded-full bg-sky-600"
                                >
                                    <Check class="h-3 w-3" />
                                </div>
                            </button>
                        </div>

                        <p
                            v-if="creators.length === 0"
                            class="bg-muted text-muted-foreground rounded-lg p-4 text-sm"
                        >
                            Belum ada creator aktif.
                        </p>

                        <p
                            v-if="errors.creator_id"
                            class="mt-2 text-xs text-red-500"
                        >
                            {{ errors.creator_id }}
                        </p>
                    </div>

                    <!-- Campaign Identity -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                            >
                                <FileText class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="font-semibold">
                                    Campaign Information
                                </h2>

                                <p class="text-muted-foreground text-xs">
                                    Informasi utama campaign dan produk.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <!-- Campaign Name -->
                            <div class="sm:col-span-2">
                                <label class="text-sm font-medium">
                                    Nama Campaign
                                </label>

                                <input
                                    v-model="form.campaign_name"
                                    type="text"
                                    placeholder="Contoh: Campaign 10.10 Miracle"
                                    class="border-border bg-background focus:border-primary focus:ring-primary/20 mt-2 h-11 w-full rounded-lg border px-3 text-sm transition outline-none focus:ring-2"
                                    :class="{
                                        'border-red-500': errors.campaign_name,
                                    }"
                                    @input="clearError('campaign_name')"
                                />

                                <p
                                    v-if="errors.campaign_name"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.campaign_name }}
                                </p>
                            </div>

                            <!-- Product -->
                            <div>
                                <label class="text-sm font-medium">
                                    Nama Produk
                                </label>

                                <div class="relative mt-2">
                                    <Package
                                        class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                    />

                                    <input
                                        v-model="form.product_name"
                                        type="text"
                                        placeholder="Contoh: Miracle Serum"
                                        class="border-border bg-background focus:border-primary focus:ring-primary/20 h-11 w-full rounded-lg border pr-3 pl-9 text-sm transition outline-none focus:ring-2"
                                        :class="{
                                            'border-red-500':
                                                errors.product_name,
                                        }"
                                        @input="clearError('product_name')"
                                    />
                                </div>

                                <p
                                    v-if="errors.product_name"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.product_name }}
                                </p>
                            </div>

                            <!-- Deliverable -->
                            <div>
                                <label class="text-sm font-medium">
                                    Deliverable
                                </label>

                                <select
                                    v-model="form.deliverable"
                                    class="border-border bg-background focus:border-primary focus:ring-primary/20 mt-2 h-11 w-full rounded-lg border px-3 text-sm transition outline-none focus:ring-2"
                                    :class="{
                                        'border-red-500': errors.deliverable,
                                    }"
                                    @change="clearError('deliverable')"
                                >
                                    <option value="">Pilih deliverable</option>

                                    <option
                                        v-for="item in deliverables"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>

                                <p
                                    v-if="errors.deliverable"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.deliverable }}
                                </p>
                            </div>
                        </div>

                        <!-- Platform -->
                        <div class="mt-5">
                            <label class="text-sm font-medium">
                                Platform
                            </label>

                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                <button
                                    v-for="platform in platforms"
                                    :key="platform.value"
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg border p-3 text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                    :class="
                                        form.platform === platform.value
                                            ? 'border-primary bg-primary/5 ring-primary/20 ring-1'
                                            : 'border-border bg-background hover:bg-muted/40'
                                    "
                                    @click="setPlatform(platform.value)"
                                >
                                    <span
                                        class="bg-muted flex h-8 w-8 items-center justify-center rounded-md text-xs font-bold"
                                    >
                                        {{ platform.short }}
                                    </span>

                                    <span class="text-sm font-medium">
                                        {{ platform.value }}
                                    </span>

                                    <Check
                                        v-if="form.platform === platform.value"
                                        class="text-primary ml-auto h-4 w-4"
                                    />
                                </button>
                            </div>

                            <p
                                v-if="errors.platform"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ errors.platform }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div v-if="currentStep === 2" class="space-y-5">
                    <!-- Deal -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                            >
                                <CircleDollarSign class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="font-semibold">Deal Information</h2>

                                <p class="text-muted-foreground text-xs">
                                    Atur nilai kerja sama dengan creator.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Agreed Price
                            </label>

                            <div class="relative mt-2">
                                <span
                                    class="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm"
                                >
                                    Rp
                                </span>

                                <input
                                    v-model="form.agreed_price"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="border-border bg-background focus:border-primary focus:ring-primary/20 h-12 w-full rounded-lg border pr-3 pl-10 text-lg font-semibold transition outline-none focus:ring-2"
                                    :class="{
                                        'border-red-500': errors.agreed_price,
                                    }"
                                    @input="clearError('agreed_price')"
                                />
                            </div>

                            <div class="mt-2 flex items-center justify-between">
                                <p
                                    v-if="errors.agreed_price"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.agreed_price }}
                                </p>

                                <p v-else class="text-muted-foreground text-xs">
                                    Nilai deal yang disepakati dengan creator.
                                </p>

                                <span class="text-sm font-semibold">
                                    {{ formattedPrice }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                            >
                                <CalendarDays class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="font-semibold">Campaign Timeline</h2>

                                <p class="text-muted-foreground text-xs">
                                    Tentukan kapan campaign dimulai dan
                                    berakhir.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium">
                                    Start Date
                                </label>

                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="border-border bg-background focus:border-primary focus:ring-primary/20 mt-2 h-11 w-full rounded-lg border px-3 text-sm transition outline-none focus:ring-2"
                                    :class="{
                                        'border-red-500': errors.start_date,
                                    }"
                                    @change="clearError('start_date')"
                                />

                                <p
                                    v-if="errors.start_date"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium">
                                    End Date
                                    <span
                                        class="text-muted-foreground font-normal"
                                    >
                                        (opsional)
                                    </span>
                                </label>

                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    class="border-border bg-background focus:border-primary focus:ring-primary/20 mt-2 h-11 w-full rounded-lg border px-3 text-sm transition outline-none focus:ring-2"
                                    :class="{
                                        'border-red-500': errors.end_date,
                                    }"
                                    @change="clearError('end_date')"
                                />

                                <p
                                    v-if="errors.end_date"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.end_date }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="campaignDuration"
                            class="bg-muted/50 mt-4 flex items-center justify-between rounded-lg px-4 py-3"
                        >
                            <span class="text-muted-foreground text-sm">
                                Durasi campaign
                            </span>

                            <span class="font-semibold">
                                {{ campaignDuration }} hari
                            </span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-5">
                            <h2 class="font-semibold">Campaign Status</h2>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Tentukan status awal campaign.
                            </p>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <button
                                v-for="item in statuses"
                                :key="item.value"
                                type="button"
                                class="flex items-center gap-3 rounded-lg border p-3 text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                :class="
                                    form.status === item.value
                                        ? 'border-primary bg-primary/5 ring-primary/20 ring-1'
                                        : 'border-border bg-background hover:bg-muted/40'
                                "
                                @click="setStatus(item.value)"
                            >
                                <div
                                    class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-medium">
                                        {{ item.label }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-0.5 text-xs"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>

                                <Check
                                    v-if="form.status === item.value"
                                    class="text-primary ml-auto h-4 w-4 shrink-0"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="mb-4">
                            <h2 class="font-semibold">Notes</h2>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Catatan tambahan untuk campaign ini.
                            </p>
                        </div>

                        <textarea
                            v-model="form.notes"
                            rows="5"
                            placeholder="Contoh: Creator wajib upload sebelum pukul 20.00..."
                            class="border-border bg-background focus:border-primary focus:ring-primary/20 w-full resize-none rounded-lg border px-3 py-3 text-sm leading-6 transition outline-none focus:ring-2"
                        />
                    </div>
                </div>

                <!-- STEP 3 -->
                <div v-if="currentStep === 3" class="space-y-5">
                    <!-- REVIEW HEADER -->
                    <div
                        class="border-border bg-card overflow-hidden rounded-2xl border"
                    >
                        <div class="relative overflow-hidden p-6">
                            <div
                                class="pointer-events-none absolute -top-16 -right-16 h-40 w-40 rounded-full bg-sky-500/10 blur-3xl"
                            />

                            <div
                                class="relative flex items-start justify-between gap-4"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white shadow-sm"
                                    >
                                        <ShieldCheck class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h2 class="text-base font-semibold">
                                                Final Campaign Review
                                            </h2>

                                            <span
                                                class="rounded-full bg-sky-500/10 px-2 py-0.5 text-[11px] font-medium text-sky-600 dark:text-sky-400"
                                            >
                                                Step 3 of 3
                                            </span>
                                        </div>

                                        <p
                                            class="text-muted-foreground mt-1 text-sm"
                                        >
                                            Pastikan seluruh detail kerja sama
                                            sudah sesuai sebelum campaign
                                            dibuat.
                                        </p>
                                    </div>
                                </div>

                                <div class="hidden text-right sm:block">
                                    <p class="text-muted-foreground text-xs">
                                        Campaign readiness
                                    </p>

                                    <p
                                        class="mt-1 text-lg font-bold text-sky-600"
                                    >
                                        {{ readiness }}%
                                    </p>
                                </div>
                            </div>

                            <!-- READINESS -->
                            <div class="relative mt-6">
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span class="text-muted-foreground text-xs">
                                        {{ completedReadiness }} dari
                                        {{ readinessItems.length }} informasi
                                        lengkap
                                    </span>

                                    <span
                                        v-if="readiness === 100"
                                        class="flex items-center gap-1 text-xs font-medium text-emerald-600"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Ready to launch
                                    </span>

                                    <span
                                        v-else
                                        class="flex items-center gap-1 text-xs font-medium text-amber-600"
                                    >
                                        <AlertCircle class="h-3.5 w-3.5" />
                                        Perlu dilengkapi
                                    </span>
                                </div>

                                <div
                                    class="bg-muted h-2 overflow-hidden rounded-full"
                                >
                                    <div
                                        class="h-full rounded-full bg-sky-600 transition-all duration-500"
                                        :style="{ width: `${readiness}%` }"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- READINESS CHECKLIST -->
                        <div
                            class="border-border bg-muted/20 border-t px-6 py-4"
                        >
                            <div
                                class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    v-for="item in readinessItems"
                                    :key="item.label"
                                    class="flex items-center gap-2 text-xs"
                                >
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
                                        :class="
                                            item.complete
                                                ? 'bg-emerald-500 text-white'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        <Check
                                            v-if="item.complete"
                                            class="h-3 w-3"
                                        />

                                        <span
                                            v-else
                                            class="h-1.5 w-1.5 rounded-full bg-current"
                                        />
                                    </div>

                                    <span
                                        :class="
                                            item.complete
                                                ? 'text-foreground'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{ item.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CAMPAIGN HERO -->
                    <div
                        class="border-border bg-card overflow-hidden rounded-2xl border"
                    >
                        <div class="relative p-6">
                            <div
                                class="absolute inset-x-0 top-0 h-1 bg-sky-600"
                            />

                            <div
                                class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center"
                            >
                                <div class="flex min-w-0 items-center gap-4">
                                    <!-- CREATOR AVATAR -->
                                    <div
                                        class="bg-muted text-muted-foreground ring-muted/50 flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-lg font-semibold ring-4"
                                    >
                                        <img
                                            v-if="
                                                selectedCreator?.profile_image
                                            "
                                            :src="`/storage/${selectedCreator.profile_image}`"
                                            :alt="selectedCreator.name"
                                            class="h-full w-full object-cover"
                                        />

                                        <span v-else>
                                            {{
                                                selectedCreator?.name
                                                    ?.charAt(0)
                                                    .toUpperCase() || '?'
                                            }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <h3
                                                class="truncate text-xl font-bold tracking-tight"
                                            >
                                                {{
                                                    form.campaign_name ||
                                                    'Untitled Campaign'
                                                }}
                                            </h3>

                                            <span
                                                class="rounded-full px-2.5 py-1 text-[11px] font-medium"
                                                :class="statusClass"
                                            >
                                                {{ statusLabel }}
                                            </span>
                                        </div>

                                        <p
                                            class="text-muted-foreground mt-1 text-sm"
                                        >
                                            {{
                                                form.product_name ||
                                                'Product belum ditentukan'
                                            }}
                                        </p>

                                        <div
                                            class="text-muted-foreground mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs"
                                        >
                                            <span
                                                class="flex items-center gap-1.5"
                                            >
                                                <User class="h-3.5 w-3.5" />
                                                {{
                                                    selectedCreator?.name || '-'
                                                }}
                                            </span>

                                            <span
                                                v-if="selectedCreator?.username"
                                                class="text-muted-foreground/70"
                                            >
                                                @{{ selectedCreator.username }}
                                            </span>

                                            <span
                                                class="flex items-center gap-1.5"
                                            >
                                                <Megaphone
                                                    class="h-3.5 w-3.5"
                                                />
                                                {{ form.platform || '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="border-border hover:bg-muted inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                    @click="currentStep = 1"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    Edit Campaign
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KEY METRICS -->
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- DEAL -->
                        <div
                            class="group border-border bg-card rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">
                                    Total Deal
                                </span>

                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                >
                                    <CircleDollarSign class="h-4 w-4" />
                                </div>
                            </div>

                            <p class="mt-3 text-lg font-bold tracking-tight">
                                {{ formattedAgreedPrice }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-[11px]">
                                Nilai kerja sama
                            </p>
                        </div>

                        <!-- DURATION -->
                        <div
                            class="group border-border bg-card rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">
                                    Durasi
                                </span>

                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                >
                                    <Clock3 class="h-4 w-4" />
                                </div>
                            </div>

                            <p class="mt-3 text-lg font-bold">
                                {{ campaignDuration || 1 }} hari
                            </p>

                            <p class="text-muted-foreground mt-1 text-[11px]">
                                Periode campaign
                            </p>
                        </div>

                        <!-- DELIVERABLE -->
                        <div
                            class="group border-border bg-card rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">
                                    Deliverable
                                </span>

                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                >
                                    <Package class="h-4 w-4" />
                                </div>
                            </div>

                            <p class="mt-3 truncate text-lg font-bold">
                                {{ form.deliverable || '-' }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-[11px]">
                                Output creator
                            </p>
                        </div>

                        <!-- STATUS -->
                        <div
                            class="group border-border bg-card rounded-xl border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground text-xs">
                                    Status
                                </span>

                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                >
                                    <Zap class="h-4 w-4" />
                                </div>
                            </div>

                            <p class="mt-3 text-lg font-bold">
                                {{ statusLabel }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-[11px]">
                                Status awal campaign
                            </p>
                        </div>
                    </div>

                    <!-- MAIN REVIEW GRID -->
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
                        <!-- LEFT -->
                        <div class="space-y-4">
                            <!-- CREATOR -->
                            <div
                                class="border-border bg-card overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="hover:bg-muted/30 flex w-full items-center justify-between gap-4 p-5 text-left transition-colors"
                                    @click="toggleReviewSection('creator')"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                        >
                                            <Users class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold">
                                                Creator / KOL
                                            </h4>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Partner campaign
                                            </p>
                                        </div>
                                    </div>

                                    <ChevronDown
                                        class="text-muted-foreground h-4 w-4 transition-transform duration-200"
                                        :class="
                                            reviewSections.creator
                                                ? 'rotate-180'
                                                : ''
                                        "
                                    />
                                </button>

                                <div
                                    v-if="reviewSections.creator"
                                    class="border-border border-t p-5"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="bg-muted flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold"
                                        >
                                            <img
                                                v-if="
                                                    selectedCreator?.profile_image
                                                "
                                                :src="`/storage/${selectedCreator.profile_image}`"
                                                :alt="selectedCreator.name"
                                                class="h-full w-full object-cover"
                                            />

                                            <span v-else>
                                                {{
                                                    selectedCreator?.name
                                                        ?.charAt(0)
                                                        .toUpperCase() || '?'
                                                }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="font-semibold">
                                                {{
                                                    selectedCreator?.name || '-'
                                                }}
                                            </p>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                @{{
                                                    selectedCreator?.username ||
                                                    '-'
                                                }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            class="ml-auto inline-flex items-center gap-1.5 text-xs font-medium text-sky-600 transition-colors hover:text-sky-700"
                                            @click="currentStep = 1"
                                        >
                                            Edit
                                            <ChevronRight class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- CAMPAIGN DETAILS -->
                            <div
                                class="border-border bg-card overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="hover:bg-muted/30 flex w-full items-center justify-between gap-4 p-5 text-left transition-colors"
                                    @click="toggleReviewSection('campaign')"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                        >
                                            <Megaphone class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold">
                                                Campaign Details
                                            </h4>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Informasi utama kerja sama
                                            </p>
                                        </div>
                                    </div>

                                    <ChevronDown
                                        class="text-muted-foreground h-4 w-4 transition-transform duration-200"
                                        :class="
                                            reviewSections.campaign
                                                ? 'rotate-180'
                                                : ''
                                        "
                                    />
                                </button>

                                <div
                                    v-if="reviewSections.campaign"
                                    class="border-border bg-border grid gap-px border-t sm:grid-cols-2"
                                >
                                    <div class="bg-card p-5">
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Campaign
                                        </p>

                                        <p class="mt-1 font-medium">
                                            {{ form.campaign_name || '-' }}
                                        </p>
                                    </div>

                                    <div class="bg-card p-5">
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Product
                                        </p>

                                        <p class="mt-1 font-medium">
                                            {{ form.product_name || '-' }}
                                        </p>
                                    </div>

                                    <div class="bg-card p-5">
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Platform
                                        </p>

                                        <p class="mt-1 font-medium">
                                            {{ form.platform || '-' }}
                                        </p>
                                    </div>

                                    <div class="bg-card p-5">
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Deliverable
                                        </p>

                                        <p class="mt-1 font-medium">
                                            {{ form.deliverable || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- DEAL -->
                            <div
                                class="border-border bg-card overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="hover:bg-muted/30 flex w-full items-center justify-between gap-4 p-5 text-left transition-colors"
                                    @click="toggleReviewSection('deal')"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                        >
                                            <WalletCards class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold">
                                                Deal Information
                                            </h4>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Nilai dan status kerja sama
                                            </p>
                                        </div>
                                    </div>

                                    <ChevronDown
                                        class="text-muted-foreground h-4 w-4 transition-transform duration-200"
                                        :class="
                                            reviewSections.deal
                                                ? 'rotate-180'
                                                : ''
                                        "
                                    />
                                </button>

                                <div
                                    v-if="reviewSections.deal"
                                    class="border-border border-t p-5"
                                >
                                    <div
                                        class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div>
                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Agreed Price
                                            </p>

                                            <p
                                                class="mt-1 text-2xl font-bold tracking-tight"
                                            >
                                                {{ formattedAgreedPrice }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="bg-border h-10 w-px" />

                                            <div>
                                                <p
                                                    class="text-muted-foreground text-xs"
                                                >
                                                    Status
                                                </p>

                                                <span
                                                    class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                    :class="statusClass"
                                                >
                                                    {{ statusLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TIMELINE -->
                            <div
                                class="border-border bg-card overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="hover:bg-muted/30 flex w-full items-center justify-between gap-4 p-5 text-left transition-colors"
                                    @click="toggleReviewSection('timeline')"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                        >
                                            <CalendarDays class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold">
                                                Campaign Timeline
                                            </h4>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Periode pelaksanaan
                                            </p>
                                        </div>
                                    </div>

                                    <ChevronDown
                                        class="text-muted-foreground h-4 w-4 transition-transform duration-200"
                                        :class="
                                            reviewSections.timeline
                                                ? 'rotate-180'
                                                : ''
                                        "
                                    />
                                </button>

                                <div
                                    v-if="reviewSections.timeline"
                                    class="border-border border-t p-5"
                                >
                                    <div class="relative">
                                        <div
                                            class="bg-border absolute top-5 left-4 h-px w-[calc(100%-2rem)]"
                                        />

                                        <div
                                            class="relative grid grid-cols-2 gap-8"
                                        >
                                            <div>
                                                <div
                                                    class="border-card flex h-8 w-8 items-center justify-center rounded-full border-4 bg-sky-600 text-white shadow-sm"
                                                >
                                                    <span
                                                        class="h-2 w-2 rounded-full bg-white"
                                                    />
                                                </div>

                                                <p
                                                    class="text-muted-foreground mt-3 text-xs"
                                                >
                                                    Mulai
                                                </p>

                                                <p class="mt-1 font-semibold">
                                                    {{ formattedStartDate }}
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <div
                                                    class="border-card ml-auto flex h-8 w-8 items-center justify-center rounded-full border-4 bg-sky-600 text-white shadow-sm"
                                                >
                                                    <Check
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </div>

                                                <p
                                                    class="text-muted-foreground mt-3 text-xs"
                                                >
                                                    Selesai
                                                </p>

                                                <p class="mt-1 font-semibold">
                                                    {{ formattedEndDate }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-muted/40 mt-5 flex items-center justify-between rounded-lg px-4 py-3 text-xs"
                                    >
                                        <span class="text-muted-foreground">
                                            Durasi campaign
                                        </span>

                                        <span class="font-semibold">
                                            {{ campaignDuration || 1 }} hari
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- NOTES -->
                            <div
                                v-if="form.notes"
                                class="border-border bg-card overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="hover:bg-muted/30 flex w-full items-center justify-between gap-4 p-5 text-left transition-colors"
                                    @click="toggleReviewSection('notes')"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600"
                                        >
                                            <FileText class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-semibold">
                                                Notes
                                            </h4>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Catatan internal campaign
                                            </p>
                                        </div>
                                    </div>

                                    <ChevronDown
                                        class="text-muted-foreground h-4 w-4 transition-transform duration-200"
                                        :class="
                                            reviewSections.notes
                                                ? 'rotate-180'
                                                : ''
                                        "
                                    />
                                </button>

                                <div
                                    v-if="reviewSections.notes"
                                    class="border-border border-t p-5"
                                >
                                    <p
                                        class="text-muted-foreground text-sm leading-6 whitespace-pre-wrap"
                                    >
                                        {{ form.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SUMMARY -->
                        <div class="space-y-4">
                            <!-- LAUNCH CARD -->
                            <div
                                class="overflow-hidden rounded-xl border border-sky-200 bg-sky-50/60 dark:border-sky-900/50 dark:bg-sky-950/20"
                            >
                                <div class="p-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white"
                                        >
                                            <Rocket class="h-5 w-5" />
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold">
                                                Campaign siap dibuat
                                            </p>

                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Semua informasi utama sudah
                                                tersedia.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-5 space-y-3">
                                        <div
                                            class="flex items-center justify-between text-xs"
                                        >
                                            <span class="text-muted-foreground">
                                                Creator
                                            </span>

                                            <span class="font-medium">
                                                {{
                                                    selectedCreator?.name || '-'
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between text-xs"
                                        >
                                            <span class="text-muted-foreground">
                                                Platform
                                            </span>

                                            <span class="font-medium">
                                                {{ form.platform || '-' }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between text-xs"
                                        >
                                            <span class="text-muted-foreground">
                                                Deliverable
                                            </span>

                                            <span
                                                class="max-w-[170px] truncate font-medium"
                                            >
                                                {{ form.deliverable || '-' }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between border-t border-sky-200/70 pt-3 text-sm dark:border-sky-900/50"
                                        >
                                            <span class="font-medium">
                                                Total Deal
                                            </span>

                                            <span
                                                class="font-bold text-sky-600"
                                            >
                                                {{ formattedAgreedPrice }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-background/80 mt-5 rounded-lg p-3"
                                    >
                                        <div class="flex gap-2.5">
                                            <Sparkles
                                                class="mt-0.5 h-4 w-4 shrink-0 text-sky-600"
                                            />

                                            <p
                                                class="text-muted-foreground text-xs leading-5"
                                            >
                                                Setelah dibuat, campaign akan
                                                masuk ke
                                                <span
                                                    class="text-foreground font-medium"
                                                >
                                                    Campaign Command Center
                                                </span>
                                                dan dapat dipantau melalui
                                                halaman detail.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QUICK SUMMARY -->
                            <div
                                class="border-border bg-card rounded-xl border p-5"
                            >
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold">
                                        Quick Summary
                                    </h4>

                                    <button
                                        type="button"
                                        class="text-xs font-medium text-sky-600 hover:text-sky-700"
                                        @click="currentStep = 2"
                                    >
                                        Edit
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div>
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Campaign
                                        </p>

                                        <p
                                            class="mt-1 truncate text-sm font-medium"
                                        >
                                            {{ form.campaign_name || '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Product
                                        </p>

                                        <p
                                            class="mt-1 truncate text-sm font-medium"
                                        >
                                            {{ form.product_name || '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Timeline
                                        </p>

                                        <p class="mt-1 text-sm font-medium">
                                            {{ formattedStartDate }}
                                            <span
                                                class="text-muted-foreground mx-1"
                                            >
                                                →
                                            </span>
                                            {{ formattedEndDate }}
                                        </p>
                                    </div>

                                    <div>
                                        <p
                                            class="text-muted-foreground text-[11px]"
                                        >
                                            Deliverable
                                        </p>

                                        <p class="mt-1 text-sm font-medium">
                                            {{ form.deliverable || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-5 flex items-center justify-between">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        class="border-border bg-card hover:bg-muted inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-all duration-200"
                        @click="previousStep"
                    >
                        <ChevronLeft class="h-4 w-4" />
                        Kembali
                    </button>

                    <div v-else />

                    <button
                        v-if="currentStep < 3"
                        type="button"
                        class="text-primary-foreground inline-flex items-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        @click="nextStep"
                    >
                        Lanjut
                        <ArrowRight class="h-4 w-4" />
                    </button>

                    <div
                        v-if="currentStep === 3"
                        class="border-border flex flex-col-reverse gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center"
                        >
                            <p
                                v-if="readiness < 100"
                                class="text-center text-xs text-amber-600 sm:mr-3 sm:text-right"
                            >
                                Lengkapi data sebelum membuat campaign.
                            </p>

                            <button
                                type="button"
                                :disabled="readiness < 100 || processing"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md active:translate-y-0 disabled:pointer-events-none disabled:opacity-50"
                                @click="submit"
                            >
                                <Rocket v-if="!processing" class="h-4 w-4" />

                                <span
                                    v-else
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                />

                                {{
                                    processing
                                        ? 'Membuat Campaign...'
                                        : 'Buat Campaign'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LIVE PREVIEW -->
            <aside class="hidden xl:block">
                <div class="sticky top-6 space-y-4">
                    <!-- Preview -->
                    <div
                        class="border-border bg-card overflow-hidden rounded-xl border shadow-sm"
                    >
                        <div
                            class="border-border bg-muted/20 border-b px-5 py-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-muted-foreground text-xs font-medium"
                                    >
                                        LIVE PREVIEW
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        Campaign
                                    </p>
                                </div>

                                <div
                                    class="bg-muted flex h-8 w-8 items-center justify-center rounded-lg"
                                >
                                    <Megaphone class="h-4 w-4" />
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <!-- Campaign title -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="bg-muted flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-semibold"
                                >
                                    {{ selectedPlatform?.short ?? 'CAI' }}
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate font-semibold">
                                        {{
                                            form.campaign_name ||
                                            'Nama Campaign'
                                        }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 truncate text-xs"
                                    >
                                        {{ form.product_name || 'Nama Produk' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Creator -->
                            <div
                                class="border-border bg-muted/20 mt-5 rounded-lg border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="bg-muted flex h-9 w-9 items-center justify-center rounded-full"
                                    >
                                        <User
                                            class="text-muted-foreground h-4 w-4"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">
                                            {{
                                                selectedCreator?.name ||
                                                'Pilih Creator'
                                            }}
                                        </p>

                                        <p
                                            class="text-muted-foreground truncate text-xs"
                                        >
                                            @{{
                                                selectedCreator?.username ||
                                                'username'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deal -->
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div
                                    class="border-border rounded-lg border p-3"
                                >
                                    <p
                                        class="text-muted-foreground text-[11px]"
                                    >
                                        Deal
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{ formattedPrice }}
                                    </p>
                                </div>

                                <div
                                    class="border-border rounded-lg border p-3"
                                >
                                    <p
                                        class="text-muted-foreground text-[11px]"
                                    >
                                        Status
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{ selectedStatus?.label || 'Planned' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div
                                class="border-border mt-4 rounded-lg border p-3"
                            >
                                <div class="flex items-center gap-2">
                                    <CalendarDays
                                        class="text-muted-foreground h-4 w-4"
                                    />

                                    <span class="text-xs font-medium">
                                        Timeline
                                    </span>
                                </div>

                                <div
                                    class="mt-3 flex items-center justify-between text-xs"
                                >
                                    <span>
                                        {{ form.start_date || 'Start date' }}
                                    </span>

                                    <ArrowRight
                                        class="text-muted-foreground h-3.5 w-3.5"
                                    />

                                    <span>
                                        {{ form.end_date || 'End date' }}
                                    </span>
                                </div>

                                <div
                                    v-if="campaignDuration"
                                    class="text-muted-foreground mt-3 text-xs"
                                >
                                    Durasi:
                                    <span class="text-foreground font-medium">
                                        {{ campaignDuration }} hari
                                    </span>
                                </div>
                            </div>

                            <!-- Deliverable -->
                            <div class="mt-4">
                                <p class="text-muted-foreground text-[11px]">
                                    Deliverable
                                </p>

                                <div
                                    class="bg-muted mt-2 inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium"
                                >
                                    <Package class="h-3.5 w-3.5" />
                                    {{ form.deliverable || 'Belum dipilih' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Readiness -->
                    <div
                        class="border-border bg-card rounded-xl border p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold">
                                    Campaign Readiness
                                </p>

                                <p class="text-muted-foreground mt-1 text-xs">
                                    Kelengkapan data
                                </p>
                            </div>

                            <span
                                class="text-sm font-semibold"
                                :class="
                                    isReady
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{
                                    isReady
                                        ? 'Ready'
                                        : currentStep === 3
                                          ? 'Review'
                                          : 'Draft'
                                }}
                            </span>
                        </div>

                        <div
                            class="bg-muted mt-4 h-2 overflow-hidden rounded-full"
                        >
                            <div
                                class="h-full rounded-full bg-sky-600 transition-all duration-500"
                                :style="{
                                    width:
                                        currentStep === 1
                                            ? isStepOneComplete
                                                ? '66%'
                                                : '33%'
                                            : currentStep === 2
                                              ? isStepTwoComplete
                                                  ? '100%'
                                                  : '80%'
                                              : '100%',
                                }"
                            />
                        </div>

                        <div
                            class="text-muted-foreground mt-3 flex items-center justify-between text-[11px]"
                        >
                            <span> Step {{ currentStep }} / 3 </span>

                            <span>
                                {{
                                    isReady
                                        ? 'Semua data lengkap'
                                        : 'Masih ada data'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div
                        class="border-border bg-muted/30 rounded-xl border p-4"
                    >
                        <div class="flex items-start gap-3">
                            <Rocket
                                class="text-muted-foreground mt-0.5 h-4 w-4 shrink-0"
                            />

                            <p class="text-muted-foreground text-xs leading-5">
                                Campaign yang sudah dibuat dapat diedit kembali
                                dan performance creator dapat dicatat melalui
                                halaman detail campaign.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
