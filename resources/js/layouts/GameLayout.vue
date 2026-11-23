<script setup lang="ts">
withDefaults(
    defineProps<{
        /** "intro": full-strength stadium. "board": stadium dimmed to 30% so the wheels stand out. */
        variant?: 'intro' | 'board';
    }>(),
    { variant: 'board' },
);
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col overflow-x-hidden bg-black font-sans text-white antialiased"
    >
        <div class="pointer-events-none fixed inset-0" aria-hidden="true">
            <img
                src="/images/game/background.png"
                alt=""
                class="size-full object-cover"
                :class="variant === 'board' && 'opacity-30'"
            />
            <template v-if="variant === 'intro'">
                <div
                    class="absolute inset-0 bg-linear-to-b from-black/0 to-black/80"
                />
                <div
                    class="absolute inset-0 bg-[linear-gradient(-36.9deg,rgb(0_0_0/0)_64%,rgb(0_0_0)_101.5%)]"
                />
            </template>
        </div>

        <header
            class="relative z-10 flex flex-wrap items-start justify-between gap-x-6 gap-y-4 px-6 pt-6 md:grid md:grid-cols-[1fr_auto_1fr] md:px-[3.6rem] md:pt-[3.25rem] md:pl-[4.9375rem]"
        >
            <img
                src="/images/game/abbvie.svg"
                alt="AbbVie"
                width="257"
                height="46"
                class="mt-[0.5rem] h-[2.875rem] w-auto"
            />

            <div
                class="order-last flex w-full justify-center md:order-none md:w-auto"
            >
                <slot name="nav" />
            </div>

            <img
                src="/images/game/one-rheum.svg"
                alt="One Rheum: win the race for control"
                width="353"
                height="78"
                class="mt-[1.3rem] h-[3.5rem] w-auto justify-self-end md:h-[4.875rem]"
            />
        </header>

        <main class="relative z-10 flex flex-1 flex-col">
            <slot />
        </main>

        <footer
            class="relative z-10 flex justify-end px-6 pb-6 md:absolute md:right-[3.8rem] md:bottom-[3.25rem] md:p-0"
        >
            <img
                src="/images/game/rinvoq.png"
                alt="Rinvoq (upadacitinib)"
                width="268"
                height="98"
                class="h-[6.125rem] w-auto"
            />
        </footer>
    </div>
</template>
