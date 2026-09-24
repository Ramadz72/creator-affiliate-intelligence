<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as {
    name: string;
    email: string;
    business_name?: string | null;
    profile_photo?: string | null;
});

const previewPhoto = ref<string | null>(null);

const handlePhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (!input.files?.length) {
        previewPhoto.value = null;
        return;
    }

    const file = input.files[0];

    previewPhoto.value = URL.createObjectURL(file);
};

</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Kelola informasi profil akun kamu"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <!-- Foto Profil -->
            <div class="grid gap-3">
                <Label for="profile_photo">Foto Profil</Label>

                <div class="flex items-center gap-4">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted"
                    >
                        <img
                            v-if="previewPhoto || user.profile_photo"
                            :src="
                                previewPhoto ||
                                `/storage/${user.profile_photo}`
                            "
                            alt="Foto profil"
                            class="h-full w-full object-cover"
                        />

                        <span
                            v-else
                            class="text-2xl font-semibold text-muted-foreground"
                        >
                            {{ user.name?.charAt(0)?.toUpperCase() }}
                        </span>
                    </div>

                    <Input
                        id="profile_photo"
                        type="file"
                        name="profile_photo"
                        accept="image/jpeg,image/png,image/webp"
                        @change="handlePhotoChange"
                    />
                </div>

                <InputError
                    class="mt-1"
                    :message="errors.profile_photo"
                />
            </div>

            <!-- Nama Bisnis -->
            <div class="grid gap-2">
                <Label for="business_name">Nama Bisnis / Store</Label>

                <Input
                    id="business_name"
                    class="mt-1 block w-full"
                    name="business_name"
                    :default-value="user.business_name ?? ''"
                    autocomplete="organization"
                    placeholder="Contoh: Rama Store"
                />

                <InputError
                    class="mt-2"
                    :message="errors.business_name"
                />
            </div>

            <!-- Nama -->
            <div class="grid gap-2">
                <Label for="name">Nama</Label>

                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Nama lengkap"
                />

                <InputError
                    class="mt-2"
                    :message="errors.name"
                />
            </div>

            <!-- Email -->
            <div class="grid gap-2">
                <Label for="email">Alamat Email</Label>

                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Alamat email"
                />

                <InputError
                    class="mt-2"
                    :message="errors.email"
                />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing"
                    data-test="update-profile-button"
                >
                    {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </Button>
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>