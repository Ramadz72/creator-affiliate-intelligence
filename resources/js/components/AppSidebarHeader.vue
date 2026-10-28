<script setup lang="ts">
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    watch,
} from 'vue'
import { router, usePage  } from '@inertiajs/vue3';

import {
    Bell,
    CheckCheck,
    CheckCircle2,
    ChevronDown,
    Inbox,
    Info,
    LayoutGrid,
    Loader2,
    Moon,
    OctagonX,
    PanelLeft,
    Plus,
    Search,
    Sun,
    Trash2,
    TriangleAlert,
    UserCircle,
    X,
} from '@lucide/vue';
import { toast } from 'vue-sonner';

import { SidebarTrigger } from '@/components/ui/sidebar';

import {
    useAppTabs,
    type AppTab,
} from '@/composables/useAppTabs';
import { useAppearance } from '@/composables/useAppearance';

const {
    tabs,
    activeTabId,
    initializeTabs,
    createTab,
    updateActiveTab,
    updateActiveTabUrl,
    activateTab,
    closeTab,
} = useAppTabs();

const {
    resolvedAppearance,
    updateAppearance,
} = useAppearance();

const toggleAppearance = () => {
    updateAppearance(
        resolvedAppearance.value === 'dark'
            ? 'light'
            : 'dark',
    );
};

const searchOpen = ref(false);
const search = ref('');
const searchResults = ref<
    {
        title: string;
        href: string;
        type: string;
    }[]
>([
    {
        title: 'Dashboard',
        href: '/dashboard',
        type: 'Menu',
    },
    {
        title: 'Creator / KOL',
        href: '/creators',
        type: 'Menu',
    },
    {
        title: 'Affiliate',
        href: '/affiliates',
        type: 'Menu',
    },
    {
        title: 'Campaign',
        href: '/campaigns',
        type: 'Menu',
    },
    {
        title: 'Import Data',
        href: '/imports',
        type: 'Menu',
    },
    {
        title: 'History',
        href: '/history',
        type: 'Menu',
    },
    {
        title: 'Settings',
        href: '/settings',
        type: 'Menu',
    },
]);

const notificationOpen = ref(false);
const profileOpen = ref(false);

const notificationMenu = ref<HTMLElement | null>(null)
const profileMenu = ref<HTMLElement | null>(null)

const handleOutsideClick = (event: PointerEvent) => {
    const target = event.target as Node

    if (!notificationMenu.value?.contains(target)) {
        notificationOpen.value = false
    }

    if (!profileMenu.value?.contains(target)) {
        profileOpen.value = false
    }
}

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        notificationOpen.value = false
        profileOpen.value = false
        searchOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('pointerdown', handleOutsideClick)
    document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
    document.removeEventListener('pointerdown', handleOutsideClick)
    document.removeEventListener('keydown', handleEscape)
})

const page = usePage();

const user = computed(() => page.props.auth?.user as {
    name?: string;
    email?: string;
    business_name?: string | null;
    profile_photo?: string | null;
});

type NotificationSeverity = 'success' | 'info' | 'warning' | 'error';
type NotificationFilter = 'all' | 'unread';

type AppNotification = {
    id: string;
    title: string;
    message: string;
    time: string;
    created_at: string | null;
    href: string | null;
    category: string;
    severity: NotificationSeverity;
    action: string | null;
    meta: Record<string, unknown>;
    read: boolean;
};

const notifications = ref<AppNotification[]>([]);
const unreadCount = ref(0);
const notificationsLoading = ref(false);
const notificationsLoadingMore = ref(false);
const notificationError = ref(false);
const notificationFilter = ref<NotificationFilter>('all');
const notificationHasMore = ref(false);
const notificationNextPage = ref<number | null>(null);
const clearConfirm = ref(false);

let notificationPoll: ReturnType<typeof setInterval> | undefined;
let clearConfirmTimer: ReturnType<typeof setTimeout> | undefined;
let hasLoadedNotifications = false;

const knownNotificationIds = new Set<string>();

const notifyToast = (notification: AppNotification) => {
    const options = {
        description: notification.message,
    };

    switch (notification.severity) {
        case 'success':
            toast.success(notification.title, options);
            break;
        case 'warning':
            toast.warning(notification.title, options);
            break;
        case 'error':
            toast.error(notification.title, options);
            break;
        default:
            toast.info(notification.title, options);
    }
};

const fetchNotifications = async (
    page = 1,
    append = false,
) => {
    const loading = append
        ? notificationsLoadingMore
        : notificationsLoading;

    if (loading.value) return;

    loading.value = true;

    try {
        const params = new URLSearchParams({
            filter: notificationFilter.value,
            page: String(page),
            per_page: '10',
        });

        const response = await fetch(
            `/notifications?${params.toString()}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const incoming: AppNotification[] =
            data.notifications ?? [];

        if (append) {
            notifications.value = [
                ...notifications.value,
                ...incoming,
            ];
        } else {
            notifications.value = incoming;
        }

        unreadCount.value = data.unread_count ?? 0;
        notificationHasMore.value = data.has_more ?? false;
        notificationNextPage.value = data.next_page ?? null;
        notificationError.value = false;

        if (!hasLoadedNotifications) {
            incoming.forEach((notification) =>
                knownNotificationIds.add(notification.id),
            );
            hasLoadedNotifications = true;
        } else {
            const newUnread = incoming.filter(
                (notification) =>
                    !knownNotificationIds.has(notification.id) &&
                    !notification.read,
            );

            incoming.forEach((notification) =>
                knownNotificationIds.add(notification.id),
            );

            if (
                !notificationOpen.value &&
                newUnread.length > 0
            ) {
                notifyToast(newUnread[0]);
            }
        }
    } catch (error) {
        notificationError.value = true;
        console.error('Gagal memuat notifikasi:', error);
    } finally {
        loading.value = false;
    }
};

const loadNotifications = () => fetchNotifications(1, false);

const loadMoreNotifications = () => {
    if (
        !notificationHasMore.value ||
        !notificationNextPage.value
    ) {
        return;
    }

    void fetchNotifications(
        notificationNextPage.value,
        true,
    );
};

const setNotificationFilter = (filter: NotificationFilter) => {
    if (notificationFilter.value === filter) return;

    notificationFilter.value = filter;
    notifications.value = [];
    notificationHasMore.value = false;
    notificationNextPage.value = null;

    void loadNotifications();
};

const getCsrfToken = () =>
    document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content ?? '';

const markNotificationRead = async (
    notification: AppNotification,
) => {
    if (!notification.read) {
        try {
            const response = await fetch(
                `/notifications/${encodeURIComponent(notification.id)}/read`,
                {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                },
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            await loadNotifications();
        } catch (error) {
            console.error('Gagal menandai notifikasi:', error);
            return;
        }
    }

    notificationOpen.value = false;

    if (notification.href) {
        updateActiveTab(notification.title, notification.href);
        router.visit(notification.href);
    }
};

const deleteNotification = async (
    notification: AppNotification,
) => {
    try {
        const response = await fetch(
            `/notifications/${encodeURIComponent(notification.id)}`,
            {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        notifications.value = notifications.value.filter(
            (item) => item.id !== notification.id,
        );
        knownNotificationIds.delete(notification.id);
        unreadCount.value =
            data.unread_count ?? unreadCount.value;
    } catch (error) {
        console.error('Gagal menghapus notifikasi:', error);
    }
};

const markAllNotificationsAsRead = async () => {
    try {
        const response = await fetch('/notifications/read-all', {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        await loadNotifications();
    } catch (error) {
        console.error('Gagal menandai semua notifikasi:', error);
    }
};

const clearAllNotifications = async () => {
    if (!clearConfirm.value) {
        clearConfirm.value = true;

        clearConfirmTimer = setTimeout(() => {
            clearConfirm.value = false;
        }, 3000);

        return;
    }

    if (clearConfirmTimer) {
        clearTimeout(clearConfirmTimer);
        clearConfirmTimer = undefined;
    }

    clearConfirm.value = false;

    try {
        const response = await fetch('/notifications', {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        notifications.value = [];
        unreadCount.value = 0;
        notificationHasMore.value = false;
        notificationNextPage.value = null;
        knownNotificationIds.clear();
    } catch (error) {
        console.error('Gagal menghapus semua notifikasi:', error);
    }
};

const startOfDay = (date: Date) =>
    new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
    ).getTime();

const notificationDateLabel = (iso: string | null) => {
    if (!iso) return 'Lebih lama';

    const date = new Date(iso);
    const today = startOfDay(new Date());
    const day = startOfDay(date);
    const diffDays = Math.round(
        (today - day) / 86_400_000,
    );

    if (diffDays === 0) return 'Hari ini';
    if (diffDays === 1) return 'Kemarin';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const groupedNotifications = computed(() => {
    const groups: {
        label: string;
        items: AppNotification[];
    }[] = [];

    const index = new Map<string, number>();

    for (const notification of notifications.value) {
        const label = notificationDateLabel(
            notification.created_at,
        );

        if (!index.has(label)) {
            index.set(label, groups.length);
            groups.push({ label, items: [] });
        }

        groups[index.get(label)!].items.push(notification);
    }

    return groups;
});

const severityIconClass = (severity: NotificationSeverity) => {
    switch (severity) {
        case 'success':
            return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400';
        case 'warning':
            return 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400';
        case 'error':
            return 'bg-red-100 text-red-600 dark:bg-red-950/50 dark:text-red-400';
        default:
            return 'bg-sky-100 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400';
    }
};

const metaNumber = (
    notification: AppNotification,
    key: string,
) => {
    const value = notification.meta?.[key];

    return typeof value === 'number'
        ? new Intl.NumberFormat('id-ID').format(value)
        : '0';
};


let removeNavigateListener:
    (() => void) | undefined;

const openAppTab = (
    event: Event,
) => {
    const customEvent =
        event as CustomEvent<{
            href: string;
            title: string;
        }>;

    const { href, title } =
        customEvent.detail;

    if (!href) {
        return;
    }

    if (href === '/dashboard') {
        activateTab('dashboard');

        return;
    }

    updateActiveTab(
        title,
        href,
    );
};

const handleCreateTab = () => {
    createTab();
};

const handleTabClick = (
    tab: AppTab,
) => {
    activateTab(tab.id);

    if (tab.href) {
        router.visit(tab.href);
    }
};

const handleCloseTab = (
    tab: AppTab,
) => {
    if (!tab.closable) {
        return;
    }

    const wasActive =
        activeTabId.value === tab.id;

    const nextTab = closeTab(tab.id);

    if (
        wasActive &&
        nextTab?.href
    ) {
        router.visit(nextTab.href);
    }
};

const handleSearch = () => {
    const keyword = search.value
        .trim()
        .toLowerCase();

    if (!keyword) {
        return;
    }

    const result = searchResults.value.find(
        (item) =>
            item.title
                .toLowerCase()
                .includes(keyword),
    );

    if (result) {
        updateActiveTab(
            result.title,
            result.href,
        );

        router.visit(result.href);

        searchOpen.value = false;
        search.value = '';
    }
};

const getTabIcon = (tab: AppTab) => {
    if (tab.id === 'dashboard') {
        return LayoutGrid;
    }

    return undefined;
};

const getPageTitle = (url: string) => {
    const pathname = new URL(
        url,
        window.location.origin,
    ).pathname;

    // =========================
    // DASHBOARD
    // =========================
    if (pathname === '/dashboard') {
        return 'Dashboard';
    }

    // =========================
    // CREATOR
    // =========================
    if (
        /^\/creators\/[^/]+\/contents\/create$/.test(
            pathname,
        )
    ) {
        return 'Add Content';
    }

    if (
        /^\/creators\/[^/]+\/rate-cards\/create$/.test(
            pathname,
        )
    ) {
        return 'Add Rate Card';
    }

    if (
        /^\/creators\/[^/]+\/analysis\/history\/[^/]+$/.test(
            pathname,
        )
    ) {
        return 'Analysis Detail';
    }

    if (
        /^\/creators\/[^/]+\/analysis\/history$/.test(
            pathname,
        )
    ) {
        return 'Analysis History';
    }

    if (
        /^\/creators\/[^/]+\/analysis$/.test(
            pathname,
        )
    ) {
        return 'Analyze Creator';
    }

    if (
        /^\/creators\/[^/]+\/edit$/.test(
            pathname,
        )
    ) {
        return 'Edit Creator';
    }

    if (pathname === '/creators/create') return 'Tambah Creator';

    if (
        /^\/creators\/[^/]+$/.test(
            pathname,
        )
    ) {
        return 'Detail Creator';
    }

    if (pathname === '/creators') {
        return 'Creator / KOL';
    }

    // =========================
    // AFFILIATE
    // =========================
    if (
        /^\/affiliates\/[^/]+$/.test(
            pathname,
        )
    ) {
        return 'Detail Affiliate';
    }

    if (pathname === '/affiliates') {
        return 'Affiliate';
    }

      // =========================
    // INSIGHTS
    // =========================
    if (/^\/insights\/[^/]+$/.test(pathname)) {
        return 'Insight Detail'
    }

    if (pathname === '/insights') {
        return 'Insights'
    }
   

    // =========================
    // CAMPAIGN
    // =========================
    if (
        /^\/campaigns\/[^/]+\/performance\/[^/]+\/edit$/.test(
            pathname,
        )
    ) {
        return 'Edit Performance';
    }

    if (
        /^\/campaigns\/[^/]+\/performance\/create$/.test(
            pathname,
        )
    ) {
        return 'Add Performance';
    }

    if (
        /^\/campaigns\/[^/]+\/edit$/.test(
            pathname,
        )
    ) {
        return 'Edit Campaign';
    }

    if (pathname === '/campaigns/create') return 'Tambah Campaign';

    if (
        /^\/campaigns\/[^/]+$/.test(
            pathname,
        )
    ) {
        return 'Detail Campaign';
    }

    if (pathname === '/campaigns') {
        return 'Campaign';
    }

    // =========================
    // HISTORY
    // =========================
    if (
        /^\/history\/[^/]+$/.test(
            pathname,
        )
    ) {
        return 'Detail History';
    }

    if (pathname === '/history') {
        return 'History';
    }

    // =========================
    // IMPORT
    // =========================
    if (pathname === '/imports') {
        return 'Import Data';
    }

    // =========================
    // SETTINGS
    // =========================
    if (pathname.startsWith('/settings')) {
        return 'Settings';
    }

    return null;
};

const toggleNotifications = () => {
    notificationOpen.value = !notificationOpen.value
    profileOpen.value = false

    if (notificationOpen.value) {
        void loadNotifications()
    }
}

const toggleProfile = () => {
    profileOpen.value = !profileOpen.value
    notificationOpen.value = false
}

onMounted(() => {
    initializeTabs();

    void loadNotifications();

    notificationPoll = setInterval(() => {
        void loadNotifications();
    }, 30_000);

    window.addEventListener(
        'app:open-tab',
        openAppTab,
    );

    removeNavigateListener =
        router.on('navigate', (event) => {
            const url =
                event.detail.page.url;

            const title =
                getPageTitle(url);

            if (!title) {
                return;
            }

            updateActiveTab(
                title,
                new URL(
                    url,
                    window.location.origin,
                ).pathname,
            );
        });
});

onUnmounted(() => {
    window.removeEventListener('app:open-tab', openAppTab);
    removeNavigateListener?.();

    if (notificationPoll) {
        clearInterval(notificationPoll);
    }

    if (clearConfirmTimer) {
        clearTimeout(clearConfirmTimer);
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-13 shrink-0 border-b border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
    >
        <!-- SIDEBAR TOGGLE -->
        <div
            class="flex h-16 w-12 shrink-0 items-center justify-center"
        >
            <SidebarTrigger
                class="h-4 w-4"
            >
                <PanelLeft class="h-4 w-4" />

                <span class="sr-only">
                    Toggle Sidebar
                </span>
            </SidebarTrigger>
        </div>

        <!-- TAB BAR -->
        <div
            class="flex min-w-0 flex-1 items-end overflow-hidden"
        >
            <div
                class="flex h-full min-w-0 items-end gap-1 overflow-x-auto px-2 scrollbar-none"
            >
                <!-- TABS -->
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    @click="handleTabClick(tab)"
                    class="group relative flex h-10 min-w-[130px] max-w-[200px] items-center gap-2 rounded-t-lg border border-b-0 px-3 text-sm transition-all duration-200"
                    :class="
                        activeTabId === tab.id
                            ? 'border-border bg-background text-foreground shadow-[0_-1px_0_hsl(var(--border))] before:absolute before:inset-x-0 before:top-0 before:h-px before:bg-gradient-to-r before:from-transparent before:via-sky-400/40 before:to-transparent'
                            : 'border-transparent bg-transparent text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                    "
                >
                    <!-- ACTIVE INDICATOR -->
                    <span
                        v-if="
                            activeTabId ===
                            tab.id
                        "
                        class="absolute inset-x-2 bottom-0 h-[1px] bg-background"
                    />

                    <!-- ICON -->
                    <component
                        v-if="getTabIcon(tab)"
                        :is="getTabIcon(tab)"
                        class="h-4 w-4 shrink-0"
                    />

                    <!-- TITLE -->
                    <span
                        class="min-w-0 flex-1 truncate text-left font-medium"
                    >
                        {{ tab.title }}
                    </span>

                    <!-- CLOSE -->
                    <span
                        v-if="tab.closable"
                        @click.stop="
                            handleCloseTab(tab)
                        "
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md opacity-50 transition hover:bg-muted-foreground/10 hover:text-foreground hover:opacity-100"
                        title="Tutup tab"
                    >
                        <X
                            class="h-3.5 w-3.5"
                        />
                    </span>
                </button>

                <!-- NEW TAB -->
                <button
                    type="button"
                    @click="handleCreateTab"
                    class="mb-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-all duration-200 hover:bg-muted hover:text-foreground hover:scale-105"
                    title="Buka tab baru"
                >
                    <Plus class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- RIGHT ACTIONS -->
        <div
            class="flex h-13 shrink-0 items-center gap-1 px-3"
        >
            <!-- SEARCH -->
            <div class="relative flex items-center">
                <input
                    v-if="searchOpen"
                    v-model="search"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Cari menu..."
                    class="h-9 w-52 rounded-lg border bg-background px-3 pr-9 text-sm outline-none transition focus:border-sky-500"
                    autofocus
                />

                <button
                    type="button"
                    @click="
                        searchOpen = !searchOpen;
                        if (!searchOpen) search = '';
                    "
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    title="Search"
                >
                    <Search class="h-4 w-4" />
                </button>

                <!-- SEARCH RESULT -->
                <div
                    v-if="
                        searchOpen &&
                        search.trim()
                    "
                    class="absolute right-0 top-11 z-50 w-64 overflow-hidden rounded-xl border bg-background p-2 shadow-xl"
                >
                    <button
                        v-for="result in searchResults.filter(
                            (item) =>
                                item.title
                                    .toLowerCase()
                                    .includes(
                                        search
                                            .toLowerCase(),
                                    ),
                        )"
                        :key="result.href"
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left transition hover:bg-muted"
                        @click="
                            updateActiveTab(
                                result.title,
                                result.href,
                            );
                            router.visit(result.href);
                            searchOpen = false;
                            search = '';
                        "
                    >
                        <span class="text-sm">
                            {{ result.title }}
                        </span>

                        <span
                            class="text-[10px] text-muted-foreground"
                        >
                            {{ result.type }}
                        </span>
                    </button>

                    <div
                        v-if="
                            !searchResults.some(
                                (item) =>
                                    item.title
                                        .toLowerCase()
                                        .includes(
                                            search
                                                .toLowerCase(),
                                        ),
                            )
                        "
                        class="px-3 py-3 text-center text-xs text-muted-foreground"
                    >
                        Tidak ditemukan
                    </div>
                </div>
            </div>

            
            <!-- THEME -->
            <button
                type="button"
                @click="toggleAppearance"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-all duration-200 hover:bg-muted hover:text-foreground"
                :title="
                    resolvedAppearance === 'dark'
                        ? 'Gunakan mode terang'
                        : 'Gunakan mode gelap'
                "
            >
                <Sun
                    v-if="resolvedAppearance === 'dark'"
                    class="h-4 w-4 transition-transform duration-200"
                />
                <Moon
                    v-else
                    class="h-4 w-4 transition-transform duration-200"
                />
            </button>

            <!-- NOTIFICATION -->
            <div ref="notificationMenu" class="relative">
                <button
                    type="button"
                    @click="toggleNotifications"
                    class="relative flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    title="Notifications"
                >
                    <Bell class="h-4 w-4" />

                    <!-- BADGE -->
                    <span v-if="unreadCount > 0"
                    class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
                </button>

                <!-- DROPDOWN -->
                <div
                    v-if="notificationOpen"
                    class="absolute right-0 top-11 z-50 w-80 overflow-hidden rounded-xl border bg-background shadow-xl sm:w-96"
                >
                    <!-- HEADER -->
                    <div class="border-b px-4 pb-0 pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold">
                                    Notifications
                                </h3>

                                <p class="mt-0.5 text-[11px] text-muted-foreground">
                                    {{ unreadCount }} belum dibaca
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    v-if="unreadCount > 0"
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                    title="Tandai semua dibaca"
                                    @click="markAllNotificationsAsRead()"
                                >
                                    <CheckCheck class="h-4 w-4" />
                                </button>

                                <button
                                    v-if="notifications.length"
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-md transition"
                                    :class="
                                        clearConfirm
                                            ? 'bg-red-100 text-red-600 dark:bg-red-950/50 dark:text-red-400'
                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                    "
                                    :title="
                                        clearConfirm
                                            ? 'Klik lagi untuk hapus semua'
                                            : 'Hapus semua notifikasi'
                                    "
                                    @click="clearAllNotifications()"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="clearConfirm"
                            class="mt-1 text-right text-[10px] text-red-500"
                        >
                            Klik lagi untuk menghapus semua
                        </p>

                        <div class="mt-3 flex gap-1">
                            <button
                                type="button"
                                class="rounded-t-md px-2.5 pb-2 pt-1 text-xs font-medium transition"
                                :class="
                                    notificationFilter === 'all'
                                        ? 'border-b-2 border-sky-500 text-foreground'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                @click="setNotificationFilter('all')"
                            >
                                Semua
                            </button>

                            <button
                                type="button"
                                class="inline-flex items-center rounded-t-md px-2.5 pb-2 pt-1 text-xs font-medium transition"
                                :class="
                                    notificationFilter === 'unread'
                                        ? 'border-b-2 border-sky-500 text-foreground'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                @click="setNotificationFilter('unread')"
                            >
                                Belum dibaca

                                <span
                                    v-if="unreadCount > 0"
                                    class="ml-1.5 rounded-full bg-sky-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-sky-600"
                                >
                                    {{ unreadCount }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- LOADING -->
                    <div
                        v-if="notificationsLoading && !notifications.length"
                        class="px-4 py-8 text-center text-xs text-muted-foreground"
                    >
                        Memuat notifikasi...
                    </div>

                    <!-- ERROR -->
                    <div
                        v-else-if="notificationError && !notifications.length"
                        class="px-4 py-8 text-center"
                    >
                        <p class="text-xs text-muted-foreground">
                            Notifikasi gagal dimuat.
                        </p>

                        <button
                            type="button"
                            class="mt-2 text-xs font-medium text-sky-600 hover:text-sky-700"
                            @click="loadNotifications"
                        >
                            Coba lagi
                        </button>
                    </div>

                    <!-- NOTIFICATION LIST -->
                    <div
                        v-else-if="notifications.length"
                        class="max-h-[28rem] overflow-y-auto"
                    >
                        <div
                            v-for="group in groupedNotifications"
                            :key="group.label"
                        >
                            <p
                                class="sticky top-0 z-10 bg-background/95 px-4 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground backdrop-blur"
                            >
                                {{ group.label }}
                            </p>

                            <div
                                v-for="notification in group.items"
                                :key="notification.id"
                                class="group flex items-stretch border-b last:border-b-0"
                                :class="
                                    !notification.read
                                        ? 'bg-sky-500/5'
                                        : ''
                                "
                            >
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 gap-3 px-4 py-3 text-left transition hover:bg-muted/60"
                                    @click="markNotificationRead(notification)"
                                >
                                    <div
                                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                        :class="
                                            severityIconClass(
                                                notification.severity,
                                            )
                                        "
                                    >
                                        <CheckCircle2
                                            v-if="
                                                notification.severity ===
                                                'success'
                                            "
                                            class="h-4 w-4"
                                        />

                                        <TriangleAlert
                                            v-else-if="
                                                notification.severity ===
                                                'warning'
                                            "
                                            class="h-4 w-4"
                                        />

                                        <OctagonX
                                            v-else-if="
                                                notification.severity ===
                                                'error'
                                            "
                                            class="h-4 w-4"
                                        />

                                        <Info
                                            v-else
                                            class="h-4 w-4"
                                        />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <span
                                                class="text-xs font-semibold"
                                                :class="
                                                    !notification.read
                                                        ? 'text-foreground'
                                                        : 'text-muted-foreground'
                                                "
                                            >
                                                {{ notification.title }}
                                            </span>

                                            <span
                                                class="shrink-0 text-[10px] text-muted-foreground"
                                            >
                                                {{ notification.time }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 text-[11px] leading-relaxed text-muted-foreground"
                                        >
                                            {{ notification.message }}
                                        </p>

                                        <div
                                            v-if="
                                                notification.category ===
                                                    'import' &&
                                                notification.meta
                                            "
                                            class="mt-1.5 flex flex-wrap gap-1"
                                        >
                                            <span
                                                class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400"
                                            >
                                                {{
                                                    metaNumber(
                                                        notification,
                                                        'successful_rows',
                                                    )
                                                }}
                                                berhasil
                                            </span>

                                            <span
                                                class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{
                                                    metaNumber(
                                                        notification,
                                                        'skipped_rows',
                                                    )
                                                }}
                                                dilewati
                                            </span>

                                            <span
                                                class="rounded bg-red-50 px-1.5 py-0.5 text-[10px] font-medium text-red-600 dark:bg-red-950/40 dark:text-red-400"
                                            >
                                                {{
                                                    metaNumber(
                                                        notification,
                                                        'error_rows',
                                                    )
                                                }}
                                                error
                                            </span>
                                        </div>

                                        <span
                                            v-if="notification.href"
                                            class="mt-1.5 inline-flex text-[10px] font-medium text-sky-600"
                                        >
                                            {{
                                                notification.action ??
                                                'Lihat detail'
                                            }}
                                        </span>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    class="flex w-9 shrink-0 items-center justify-center text-muted-foreground opacity-0 transition hover:text-red-500 group-hover:opacity-100"
                                    title="Hapus notifikasi"
                                    @click.stop="
                                        deleteNotification(notification)
                                    "
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <button
                            v-if="notificationHasMore"
                            type="button"
                            class="flex w-full items-center justify-center gap-2 border-t px-4 py-2.5 text-xs font-medium text-sky-600 transition hover:bg-muted/60 disabled:opacity-60"
                            :disabled="notificationsLoadingMore"
                            @click="loadMoreNotifications()"
                        >
                            <Loader2
                                v-if="notificationsLoadingMore"
                                class="h-3.5 w-3.5 animate-spin"
                            />

                            {{
                                notificationsLoadingMore
                                    ? 'Memuat...'
                                    : 'Muat lebih banyak'
                            }}
                        </button>
                    </div>

                    <!-- EMPTY -->
                    <div v-else class="px-4 py-10 text-center">
                        <Inbox
                            class="mx-auto h-8 w-8 text-muted-foreground/40"
                        />

                        <p class="mt-2 text-xs text-muted-foreground">
                            {{
                                notificationFilter === 'unread'
                                    ? 'Tidak ada notifikasi yang belum dibaca.'
                                    : 'Belum ada notifikasi.'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- PROFILE -->
            <div ref="profileMenu" class="relative">
                <button
                    type="button"
                    @click="toggleProfile"
                    class="flex h-10 items-center gap-2 rounded-lg px-2 transition hover:bg-muted"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted"
                    >
                        <img
                            v-if="user?.profile_photo"
                            :src="`/storage/${user.profile_photo}`"
                            :alt="user?.name ?? 'User'"
                            class="h-full w-full object-cover"
                        />

                        <span
                            v-else
                            class="text-sm font-semibold text-muted-foreground"
                        >
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                        </span>
                    </div>

                    <div
                        class="hidden text-left lg:block"
                    >
                        <div
                            class="text-xs font-semibold"
                        >
                            {{ user?.name ?? 'User' }}
                        </div>

                        <div class="truncate text-xs text-muted-foreground">
                            {{ user?.business_name ?? 'Belum ada nama bisnis' }}
                        </div>
                    </div>

                    <ChevronDown
                        class="hidden h-4 w-4 text-muted-foreground lg:block"
                    />
                </button>

                <div
                    v-if="profileOpen"
                    class="absolute right-0 top-12 z-50 w-52 rounded-xl border bg-background p-2 shadow-xl"
                >
                    <button
                        type="button"
                        class="flex w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-muted"
                        @click="
                            router.visit(
                                '/settings',
                            )
                        "
                    >
                        Profile & Settings
                    </button>

                    <button
                        type="button"
                        class="flex w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-500/10"
                        @click="
                            router.post('/logout');
                            profileOpen = false;
                        "
                    >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>