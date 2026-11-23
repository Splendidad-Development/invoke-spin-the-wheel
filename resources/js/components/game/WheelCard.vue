<script setup lang="ts">
import { ref } from 'vue';
import SpinWheel from '@/components/game/SpinWheel.vue';
import type { WheelState } from '@/types/game';

defineProps<{
    wheel: WheelState;
    /** This wheel is turning right now. */
    spinning: boolean;
    /** Spinning is blocked (another wheel is turning or a dialog is open). */
    disabled: boolean;
}>();

const emit = defineEmits<{ spin: [] }>();

const spinner = ref<InstanceType<typeof SpinWheel> | null>(null);

defineExpose({
    spinTo: (position: number): Promise<void> =>
        spinner.value?.spinTo(position) ?? Promise.resolve(),
});
</script>

<template>
    <section
        class="glass-panel flex flex-col items-center gap-5 px-5 py-5 md:px-10"
        :aria-labelledby="`wheel-${wheel.key}-title`"
    >
        <h2
            :id="`wheel-${wheel.key}-title`"
            class="m-0 text-center text-[1.4375rem] leading-normal font-normal"
        >
            Spin the wheel to get a
            <strong class="font-bold">{{ wheel.label }}</strong>
        </h2>

        <SpinWheel
            ref="spinner"
            :segments="wheel.segments"
            :label="`${wheel.label} wheel`"
        />

        <button
            type="button"
            class="pill font-bold uppercase italic"
            :disabled="disabled || wheel.segments.length === 0"
            :aria-label="`Spin the ${wheel.label.toLowerCase()} wheel`"
            @click="emit('spin')"
        >
            {{ spinning ? 'Spinning…' : 'Spin' }}
        </button>
    </section>
</template>
