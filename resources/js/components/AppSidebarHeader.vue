<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage  } from '@inertiajs/vue3';

import {
    Bell,
    ChevronDown,
    LayoutGrid,
    Moon,
    PanelLeft,
    Plus,
    Search,
    Sun,
    UserCircle,
    X,
} from '@lucide/vue';

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

const page = usePage();

const user = computed(() => page.props.auth?.user as {
    name?: string;
    email?: string;
    business_name?: string | null;
    profile_photo?: string | null;
});

const notifications = ref([
    {
        id: 1,
        title: 'Import Data Selesai',
        message: 'Import data affiliate berhasil diproses.',
        time: 'Baru saja',
        href: '/history',
        read: false,
    },
    {
        id: 2,
        title: 'Creator Siap Dianalisis',
        message: 'Ada creator yang memiliki data terbaru.',
        time: '10 menit lalu',
        href: '/creators',
        read: false,
    },
    {
        id: 3,
        title: 'Campaign Baru',
        message: 'Campaign baru berhasil dibuat.',
        time: '1 jam lalu',
        href: '/campaigns',
        read: true,
    },
]);

const unreadNotifications = computed(() => {
    return notifications.value.filter(
        (notification) => !notification.read,
    );
});

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

const openNotification = (notification: any) => {
    notification.read = true;

    notificationOpen.value = false;

    if (notification.href) {
        updateActiveTab(
            notification.title,
            notification.href,
        );

        router.visit(notification.href);
    }
};

const markAllNotificationsAsRead = () => {
    notifications.value.forEach((notification) => {
        notification.read = true;
    });
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

onMounted(() => {
    initializeTabs();

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
    window.removeEventListener(
        'app:open-tab',
        openAppTab,
    );

    removeNavigateListener?.();
});
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-13 shrink-0 border-b app-textured-bg bg-background"
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
                    class="group relative flex h-11 min-w-[130px] max-w-[200px] items-center gap-2 rounded-t-xl border px-3 text-sm transition-all duration-200"
                    :class="
                        activeTabId === tab.id
                            ? 'border-border border-b-background bg-background text-foreground shadow-sm'
                            : 'border-transparent bg-muted/40 text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                >
                    <!-- ACTIVE INDICATOR -->
                    <span
                        v-if="
                            activeTabId ===
                            tab.id
                        "
                        class="absolute inset-x-3 -bottom-px h-[2px] rounded-full bg-sky-500"
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
            <div class="relative">
                <button
                    type="button"
                    @click="
                        notificationOpen =
                            !notificationOpen;
                        profileOpen = false;
                    "
                    class="relative flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    title="Notifications"
                >
                    <Bell class="h-4 w-4" />

                    <!-- BADGE -->
                    <span
                        v-if="unreadNotifications.length"
                        class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white"
                    >
                        {{
                            unreadNotifications.length > 9
                                ? '9+'
                                : unreadNotifications.length
                        }}
                    </span>
                </button>

                <!-- DROPDOWN -->
                <div
                    v-if="notificationOpen"
                    class="absolute right-0 top-11 z-50 w-80 overflow-hidden rounded-xl border bg-background shadow-xl"
                >
                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between border-b px-4 py-3"
                    >
                        <div>
                            <h3
                                class="text-sm font-semibold"
                            >
                                Notifications
                            </h3>

                            <p
                                class="mt-0.5 text-[11px] text-muted-foreground"
                            >
                                {{
                                    unreadNotifications.length
                                }}
                                belum dibaca
                            </p>
                        </div>

                        <button
                            v-if="
                                unreadNotifications.length
                            "
                            type="button"
                            class="text-[11px] font-medium text-sky-600 transition hover:text-sky-700"
                            @click="
                                markAllNotificationsAsRead()
                            "
                        >
                            Tandai dibaca
                        </button>
                    </div>

                    <!-- LIST -->
                    <div
                        v-if="notifications.length"
                        class="max-h-80 overflow-y-auto"
                    >
                        <button
                            v-for="notification in notifications"
                            :key="notification.id"
                            type="button"
                            class="flex w-full gap-3 border-b px-4 py-3 text-left transition last:border-b-0 hover:bg-muted/60"
                            :class="
                                !notification.read
                                    ? 'bg-sky-500/5'
                                    : ''
                            "
                            @click="
                                openNotification(
                                    notification,
                                )
                            "
                        >
                            <!-- INDICATOR -->
                            <div
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                :class="
                                    !notification.read
                                        ? 'bg-sky-500'
                                        : 'bg-transparent'
                                "
                            />

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
                                        {{
                                            notification.title
                                        }}
                                    </span>

                                    <span
                                        class="shrink-0 text-[10px] text-muted-foreground"
                                    >
                                        {{
                                            notification.time
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="mt-1 text-[11px] leading-relaxed text-muted-foreground"
                                >
                                    {{
                                        notification.message
                                    }}
                                </p>
                            </div>
                        </button>
                    </div>

                    <!-- EMPTY -->
                    <div
                        v-else
                        class="px-4 py-8 text-center"
                    >
                        <Bell
                            class="mx-auto h-6 w-6 text-muted-foreground/40"
                        />

                        <p
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            Belum ada notifikasi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- PROFILE -->
            <div class="relative">
                <button
                    type="button"
                    @click="
                        profileOpen =
                            !profileOpen
                    "
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