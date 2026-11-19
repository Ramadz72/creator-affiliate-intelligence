<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { useSidebar } from '@/components/ui/sidebar';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: true,
});

const { getInitials } = useInitials();
const { state } = useSidebar();

const hasProfilePhoto = computed(
    () => !!props.user.profile_photo && props.user.profile_photo.trim() !== '',
);

console.log('Profile photo:', props.user.profile_photo);
console.log('Has profile photo:', hasProfilePhoto.value);

const isCollapsed = computed(() => state.value === 'collapsed');
</script>

<template>
    <div
        class="flex min-w-0 items-center gap-2.5"
        :class="{
            'justify-center': isCollapsed,
        }"
    >
        <!-- Profile Photo -->
        <div class="relative shrink-0">
            <Avatar
                class="border-sidebar-border h-9 w-9 overflow-hidden rounded-xl border shadow-sm transition-all duration-200 group-hover:scale-105 group-hover:shadow-md"
            >
                <!-- Foto profil asli -->
                <AvatarImage
                    v-if="hasProfilePhoto"
                    :src="`/storage/${props.user.profile_photo}`"
                    :alt="props.user.name"
                    class="h-full w-full object-cover"
                />

                <!-- Fallback kalau belum punya foto -->
                <AvatarFallback
                    v-else
                    class="bg-primary/10 text-primary rounded-xl text-xs font-semibold"
                >
                    {{ getInitials(user.name) }}
                </AvatarFallback>
            </Avatar>

            <!-- Active indicator -->
            <span
                class="border-sidebar absolute -right-0.5 -bottom-0.5 h-2.5 w-2.5 rounded-full border-2 bg-green-500"
            />
        </div>

        <!-- User Information -->
        <div
            v-if="!isCollapsed"
            class="grid min-w-0 flex-1 text-left leading-tight"
        >
            <span class="truncate text-sm font-semibold tracking-tight">
                {{ user.name }}
            </span>

            <span
                v-if="showEmail && user.email"
                class="text-muted-foreground mt-0.5 truncate text-[11px]"
            >
                {{ user.email }}
            </span>
        </div>
    </div>
</template>
