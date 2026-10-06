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

const page = usePage();

const user = computed(() => page.props.auth.user);

const { isMobile, state } = useSidebar();

const hasProfilePhoto = computed(
    () => !!user.value.avatar && user.value.avatar.trim() !== '',
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
                        class="group relative overflow-hidden rounded-xl border border-transparent bg-transparent px-2.5 transition-all duration-200 hover:border-sidebar-border hover:bg-sidebar-accent/70 data-[state=open]:border-sidebar-border data-[state=open]:bg-sidebar-accent data-[state=open]:shadow-sm"
                        data-test="sidebar-menu-button"
                    >
                        <!-- Subtle active indicator -->
                        <span
                            class="absolute left-0 top-1/2 h-6 w-0.5 -translate-y-1/2 rounded-full bg-primary opacity-0 transition-all duration-200 group-data-[state=open]:opacity-100"
                        />

                        <!-- User -->
                        <div class="min-w-0 flex-1">
                            <UserInfo :user="user" />
                        </div>

                        <!-- Account icon / chevron -->
                        <div
                            class="ml-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sidebar-accent/70 text-sidebar-foreground/60 transition-all duration-200 group-hover:bg-sidebar-accent group-hover:text-sidebar-foreground group-data-[state=open]:bg-primary/10 group-data-[state=open]:text-primary"
                        >
                            <ChevronsUpDown
                                class="size-3.5 transition-transform duration-200 group-data-[state=open]:rotate-180"
                            />
                        </div>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <!-- User Dropdown -->
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-60 overflow-hidden rounded-xl border border-border/70 bg-popover p-1.5 shadow-xl"
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
                        class="mb-1 flex items-center gap-3 rounded-lg bg-muted/50 px-3 py-3"
                    >
                        <div
                            class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-border bg-muted shadow-sm"
                        >
                            <img
                                v-if="hasProfilePhoto"
                                :src="user.avatar"
                                :alt="user.name"
                                class="h-full w-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-primary/10 text-sm font-semibold text-primary"
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
                                class="truncate text-xs text-muted-foreground"
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