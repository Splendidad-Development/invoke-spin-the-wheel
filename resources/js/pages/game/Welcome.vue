<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import GameLayout from '@/layouts/GameLayout.vue';
import { start } from '@/routes/game';
</script>

<template>
    <Head title="Spin the wheel" />
    <GameLayout variant="intro">
        <section
            class="flex flex-1 flex-col items-center justify-end gap-7 px-6 pt-10 pb-10 text-center md:pb-[4.75rem]"
            aria-labelledby="welcome-title"
        >
            <div
                class="flex max-w-[34.2rem] flex-col items-center text-headline"
            >
                <h1
                    id="welcome-title"
                    class="m-0 text-[3rem] leading-normal font-bold"
                >
                    Spin the wheel
                </h1>
                <p
                    class="m-0 text-[1.5rem] leading-normal font-semibold text-balance"
                >
                    Two wheels, one choice. Spin to answer a question or take on
                    a challenge. Are you ready?
                </p>
            </div>

            <Form v-bind="start.form()" #default="{ processing }">
                <button type="submit" class="pill" :disabled="processing">
                    Get started
                </button>
            </Form>
        </section>
    </GameLayout>
</template>
