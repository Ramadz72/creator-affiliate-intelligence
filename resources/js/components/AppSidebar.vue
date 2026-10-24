<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    FileSpreadsheet,
    History,
    LayoutGrid,
    Settings,
    Users,
    UserRoundSearch,
    Sparkles,
    HelpCircle,
} from '@lucide/vue';

import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

const dashboardNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

/*
|--------------------------------------------------------------------------
| Intelligence
|--------------------------------------------------------------------------
*/

const intelligenceNavItems: NavItem[] = [
    {
        title: 'Creator / KOL',
        href: '/creators',
        icon: UserRoundSearch,
    },
    {
        title: 'Affiliate',
        href: '/affiliates',
        icon: Users,
    },
    {
        title: 'Insights',
        href: '/insights',
        icon: Sparkles,
    },
];


/*
|--------------------------------------------------------------------------
| Management
|--------------------------------------------------------------------------
*/

const managementNavItems: NavItem[] = [
    {
        title: 'Campaign',
        href: '/campaigns',
        icon: BarChart3,
    },
    {
        title: 'Import Data',
        href: '/imports',
        icon: FileSpreadsheet,
    },
    {
        title: 'History',
        href: '/history',
        icon: History,
    },
];


/*
|--------------------------------------------------------------------------
| System
|--------------------------------------------------------------------------
*/

const systemNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
    },
    {
        title: 'FAQ',
        href: '/faq',
        icon: HelpCircle,
    },
];


/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-sidebar-border/80 bg-sidebar">

       <!-- Logo -->
        <SidebarHeader class="overflow-visible border-b border-sidebar-border/70 px-3 py-3">
            <SidebarMenu class="overflow-visible">
                <SidebarMenuItem class="overflow-visible">
                    <SidebarMenuButton
                        as-child
                        class="h-10 w-full justify-center bg-transparent p-0 hover:bg-transparent focus:bg-transparent group-data-[collapsible=icon]:h-9"
                    >
                        <Link
                            :href="dashboard()"
                            class="flex h-7 w-full items-center justify-center overflow-visible transition-opacity hover:opacity-90 group-data-[collapsible=icon]:justify-center"
                        >
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>


        <!-- Navigation -->
        <SidebarContent class="gap-1 px-2 py-2">

            <!-- Dashboard -->
            <NavMain
                label=""
                :items="dashboardNavItems"
            />

            <!-- Intelligence -->
            <NavMain
                label="INTELLIGENCE"
                :items="intelligenceNavItems"
            />

            <!-- Management -->
            <NavMain
                label="MANAGEMENT"
                :items="managementNavItems"
            />

            <!-- System -->
            <NavMain
                label="SYSTEM"
                :items="systemNavItems"
            />

        </SidebarContent>



        <!-- Footer -->
        <SidebarFooter>

            <NavFooter :items="footerNavItems" />

            <NavUser />

        </SidebarFooter>

    </Sidebar>
</template>