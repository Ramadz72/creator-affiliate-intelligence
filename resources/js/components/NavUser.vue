<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, CircleUserRound } from '@lucide/vue';
import { computed } from 'vue';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';

import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';

const page = usePage<{
    auth: {
        user: {
            name: string;
            email?: string | null;
            profile_photo?: string | null;
        };
    };
}>();

const user = computed(() => page.props.auth.user);

const { isMobile, state } = useSidebar();

const hasProfilePhoto = computed(
    () =>
        typeof user.value?.profile_photo === 'string' &&
        user.value.profile_photo.trim() !== '',
);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <!-- User Card -->
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="group hover:border-sidebar-border hover:bg-sidebar-accent/70 data-[state=open]:border-sidebar-border data-[state=open]:bg-sidebar-accent relative overflow-hidden rounded-xl border border-transparent bg-transparent px-2.5 transition-all duration-200 data-[state=open]:shadow-sm"
                        data-test="sidebar-menu-button"
                    >
                        <!-- Subtle active indicator -->
                        <span
                            class="bg-primary absolute top-1/2 left-0 h-6 w-0.5 -translate-y-1/2 rounded-full opacity-0 transition-all duration-200 group-data-[state=open]:opacity-100"
                        />

                        <!-- User -->
                        <div class="min-w-0 flex-1">
                            <UserInfo :user="user" />
                        </div>

                        <!-- Account icon / chevron -->
                        <div
                            class="bg-sidebar-accent/70 text-sidebar-foreground/60 group-hover:bg-sidebar-accent group-hover:text-sidebar-foreground group-data-[state=open]:bg-primary/10 group-data-[state=open]:text-primary ml-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all duration-200"
                        >
                            <ChevronsUpDown
                                class="size-3.5 transition-transform duration-200 group-data-[state=open]:rotate-180"
                            />
                        </div>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <!-- User Dropdown -->
                <DropdownMenuContent
                    class="border-border/70 bg-popover w-(--reka-dropdown-menu-trigger-width) min-w-60 overflow-hidden rounded-xl border p-1.5 shadow-xl"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="6"
                >
                    <!-- Account Header -->
                    <div
                        class="bg-muted/50 mb-1 flex items-center gap-3 rounded-lg px-3 py-3"
                    >
                        <div
                            class="border-border bg-muted h-10 w-10 shrink-0 overflow-hidden rounded-xl border shadow-sm"
                        >
                            <img
                                v-if="hasProfilePhoto"
                                :src="`/storage/${user.profile_photo}`"
                                :alt="user.name"
                                class="h-full w-full object-cover"
                            />

                            <div
                                v-else
                                class="bg-primary/10 text-primary flex h-full w-full items-center justify-center text-sm font-semibold"
                            >
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">
                                {{ user.name }}
                            </p>

                            <p
                                v-if="user.email"
                                class="text-muted-foreground truncate text-xs"
                            >
                                {{ user.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Existing menu -->
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
