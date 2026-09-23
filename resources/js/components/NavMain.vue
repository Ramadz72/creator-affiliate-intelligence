<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

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
            >
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="data-[active=true]:bg-transparent data-[active=true]:text-sky-600 hover:data-[active=true]:bg-transparent hover:data-[active=true]:text-sky-600"
                >
                    <Link
                        :href="item.href"
                        class="transition-transform duration-200 ease-out hover:scale-[1.03]"
                    >
                        <component :is="item.icon" />
                        <span class="text-[15px] font-medium">
                            {{ item.title }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>