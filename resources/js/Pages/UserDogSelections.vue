<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="User Dog Selections" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Dog Selections</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-md rounded-lg">
                    <ul>
                        <li
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-gray-300 py-4"
                        >
                            <h3 class="font-semibold text-lg">{{ user.name }}</h3>
                            <div class="flex space-x-4 mt-2">
                                <div
                                    v-for="dog in user.dogs"
                                    :key="dog"
                                    class="p-2 border border-gray-300 rounded-lg"
                                >
                                    <p class="text-sm capitalize">{{ dog }}</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
