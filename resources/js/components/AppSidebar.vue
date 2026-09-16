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
| Intelligence
|--------------------------------------------------------------------------
*/

const intelligenceNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
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
];


/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">

        <!-- Logo -->
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>


        <!-- Navigation -->
        <SidebarContent>

            <NavMain
                label="INTELLIGENCE"
                :items="intelligenceNavItems"
            />

            <NavMain
                label="MANAGEMENT"
                :items="managementNavItems"
            />

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