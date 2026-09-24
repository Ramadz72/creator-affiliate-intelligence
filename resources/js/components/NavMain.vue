<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';
import { ref, } from 'vue';

withDefaults(
    defineProps<{
        items: NavItem[];
        label?: string;
    }>(),
    {
        label: 'Platform',
    },
);

const { isCurrentUrl } = useCurrentUrl();

const hoveredItem = ref<string | null>(null);

const page = usePage();

const currentUrl = computed(() => {
    return page.url.split('?')[0];
});

const getChildPages = (item: NavItem) => {
    const url = currentUrl.value;

    // =========================
    // CREATOR
    // =========================
        if (item.href === '/creators') {
        // Saat sedang Add Creator, jangan tampilkan child pages
        if (url === '/creators/create') {
            return [];
        }

        const match = url.match(
            /^\/creators\/([^/]+)(?:\/.*)?$/,
        );

        if (!match) {
            return [];
        }

        const creatorId = match[1];

        return [
            {
                title: 'Detail Creator',
                href: `/creators/${creatorId}`,
            },
            {
                title: 'Edit Creator',
                href: `/creators/${creatorId}/edit`,
            },
            {
                title: 'Analyze Creator',
                href: `/creators/${creatorId}/analysis`,
            },
            {
                title: 'Tambah Rate Card',
                href: `/creators/${creatorId}/rate-cards/create`,
            },
            {
                title: 'Tambah Content',
                href: `/creators/${creatorId}/contents/create`,
            },
            {
                title: 'Analysis History',
                href: `/creators/${creatorId}/analysis/history`,
            },
        ];
    }

    // =========================
    // AFFILIATE
    // =========================
    if (item.href === '/affiliates') {
        const match = url.match(
            /^\/affiliates\/([^/]+)$/,
        );

        if (!match) {
            return [];
        }

        const affiliateId = match[1];

        return [
            {
                title: 'Detail Affiliate',
                href: `/affiliates/${affiliateId}`,
            },
        ];
    }

    // =========================
    // CAMPAIGN
    // =========================
    if (item.href === '/campaigns') {
    // Saat Add Campaign, jangan tampilkan child pages
    if (url === '/campaigns/create') {
        return [];
    }

    const match = url.match(
        /^\/campaigns\/([^/]+)(?:\/.*)?$/,
    );

    if (!match) {
        return [];
    }

    const campaignId = match[1];

        const performanceEditMatch = url.match(
            /^\/campaigns\/([^/]+)\/performance\/([^/]+)\/edit$/,
        );

        if (performanceEditMatch) {
            const performanceId =
                performanceEditMatch[2];

            return [
                {
                    title: 'Detail Campaign',
                    href: `/campaigns/${campaignId}`,
                },
                {
                    title: 'Edit Campaign',
                    href: `/campaigns/${campaignId}/edit`,
                },
                {
                    title: 'Tambah Performance',
                    href: `/campaigns/${campaignId}/performance/create`,
                },
                {
                    title: 'Edit Performance',
                    href: `/campaigns/${campaignId}/performance/${performanceId}/edit`,
                },
            ];
        }

        return [
            {
                title: 'Detail Campaign',
                href: `/campaigns/${campaignId}`,
            },
            {
                title: 'Edit Campaign',
                href: `/campaigns/${campaignId}/edit`,
            },
            {
                title: 'Tambah Performance',
                href: `/campaigns/${campaignId}/performance/create`,
            },
        ];
    }

    // =========================
    // HISTORY
    // =========================
    if (item.href === '/history') {
        const match = url.match(
            /^\/history\/([^/]+)$/,
        );

        if (!match) {
            return [];
        }

        const batchId = match[1];

        return [
            {
                title: 'Detail History',
                href: `/history/${batchId}`,
            },
        ];
    }

    return [];
};

const openAppTab = (href: any, title: string) => {
    window.dispatchEvent(
        new CustomEvent('app:open-tab', {
            detail: {
                href,
                title,
            },
        }),
    );
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>
            {{ label }}
        </SidebarGroupLabel>

        <SidebarMenu>
            <SidebarMenuItem
                v-for="item in items"
                :key="item.title"    
                    @mouseenter="hoveredItem = item.title"
                    @mouseleave="hoveredItem = null"
                >
                <SidebarMenuButton
                    as-child
                    :is-active="
                        isCurrentUrl(item.href) ||
                        (
                            item.href === '/creators' &&
                            currentUrl === '/creators/create'
                        ) ||
                        (
                            item.href === '/campaigns' &&
                            currentUrl === '/campaigns/create'
                        )
                    "
                    :tooltip="item.title"
                    class="data-[active=true]:bg-transparent data-[active=true]:text-sky-600 hover:data-[active=true]:bg-transparent hover:data-[active=true]:text-sky-600"
                >
                    <Link
                        :href="item.href"
                        class="transition-transform duration-200 ease-out hover:scale-[1.03]"
                        @click="
                            openAppTab(
                                item.href,
                                item.title,
                            )
                        "
                    >
                        <component :is="item.icon" />

                        <span class="text-[15px] font-medium">
                            {{ item.title }}
                        </span>
                    </Link>
                </SidebarMenuButton>

                <!-- QUICK ACTION CREATOR -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="max-h-0 opacity-0 -translate-y-1"
                    enter-to-class="max-h-10 opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="max-h-10 opacity-100 translate-y-0"
                    leave-to-class="max-h-0 opacity-0 -translate-y-1"
                >
                    <div
                        v-if="
                            hoveredItem === item.title &&
                            (
                                item.href === '/creators' ||
                                item.href === '/affiliates' ||
                                item.href === '/campaigns'
                            )
                        "
                        class="ml-5 overflow-hidden border-l border-border/60 pl-3"
                    >
                        <!-- ADD CREATOR -->
                        <Link
                            v-if="item.href === '/creators'"
                            href="/creators/create"
                            class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-all duration-200 hover:scale-[1.02] hover:bg-muted hover:text-sky-600"
                            @click="
                                openAppTab(
                                    '/creators/create',
                                    'Tambah Creator',
                                )
                            "
                        >
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-md text-xs font-medium transition-all duration-200 group-hover:bg-sky-500/10"
                            >
                                +
                            </span>

                            <span class="font-medium">
                                Tambah Creator
                            </span>
                        </Link>

                        <!-- IMPORT DATA -->
                        <Link
                            v-if="item.href === '/affiliates'"
                            href="/imports"
                            class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-all duration-200 hover:scale-[1.02] hover:bg-muted hover:text-sky-600"
                            @click="
                                openAppTab(
                                    '/imports',
                                    'Import Data',
                                )
                            "
                        >
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-md text-xs font-medium transition-all duration-200 group-hover:bg-sky-500/10"
                            >
                                +
                            </span>

                            <span class="font-medium">
                                Import Data
                            </span>
                        </Link>

                        <!-- ADD CAMPAIGN -->
                        <Link
                            v-if="item.href === '/campaigns'"
                            href="/campaigns/create"
                            class="group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-all duration-200 hover:scale-[1.02] hover:bg-muted hover:text-sky-600"
                            @click="
                                openAppTab(
                                    '/campaigns/create',
                                    'Tambah Campaign',
                                )
                            "
                        >
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-md text-xs font-medium transition-all duration-200 group-hover:bg-sky-500/10"
                            >
                                +
                            </span>

                            <span class="font-medium">
                                Tambah Campaign
                            </span>
                        </Link>
                    </div>
                </Transition>

                <!-- CHILD PAGE -->
                <div
                    v-if="getChildPages(item).length"
                    class="ml-5 border-l border-border/60 pl-3"
                >
                    <Link
                        v-for="child in getChildPages(item)"
                        :key="child.href"
                        :href="child.href"
                        class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition-all duration-200 hover:bg-muted hover:text-foreground"
                        :class="
                            isCurrentUrl(child.href)
                                ? 'font-medium text-sky-600'
                                : 'text-muted-foreground'
                        "
                        @click="
                            openAppTab(
                                child.href,
                                child.title,
                            )
                        "
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-current"
                        />

                        <span class="truncate">
                            {{ child.title }}
                        </span>
                    </Link>
                </div>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>