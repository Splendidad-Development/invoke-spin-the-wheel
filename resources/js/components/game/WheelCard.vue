<script setup lang="ts">
import { computed, ref } from 'vue';
import SpinWheel from '@/components/game/SpinWheel.vue';
import type { WheelState } from '@/types/game';

const props = defineProps<{
    wheel: WheelState;
    /** This wheel is turning right now. */
    spinning: boolean;
    /** Spinning is blocked (another wheel is turning or a dialog is open). */
    disabled: boolean;
}>();

const emit = defineEmits<{ spin: [] }>();

const spinner = ref<InstanceType<typeof SpinWheel> | null>(null);

const noun = computed(() => `${props.wheel.label.toLowerCase()}s`);

/** e.g. "14 of 20 left" or "Round 2 of 2 · 7 left". */
const status = computed(() => {
    if (props.wheel.finished) {
        return `All ${noun.value} played. Restart to play again.`;
    }

    const total = props.wheel.segments.length;
    const left = props.wheel.segments.filter((segment) => !segment.used).length;
    const remaining = `${left} of ${total} left`;

    return props.wheel.rounds > 1
        ? `Round ${props.wheel.round} of ${props.wheel.rounds} · ${remaining}`
        : remaining;
});

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
        <div class="flex flex-col items-center gap-1 text-center">
            <h2
                :id="`wheel-${wheel.key}-title`"
                class="m-0 text-[1.4375rem] leading-normal font-normal"
            >
                Spin the wheel to get a
                <strong class="font-bold">{{ wheel.label }}</strong>
            </h2>
            <p
                class="m-0 text-[1.0625rem] leading-normal font-medium"
                :class="wheel.finished ? 'text-headline' : 'text-white/75'"
                aria-live="polite"
            >
                {{ status }}
            </p>
        </div>

        <SpinWheel
            ref="spinner"
            :segments="wheel.segments"
            :label="`${wheel.label} wheel`"
        />

        <button
            type="button"
            class="pill font-bold uppercase italic"
            :disabled="
                disabled || wheel.finished || wheel.segments.length === 0
            "
            :aria-label="`Spin the ${wheel.label.toLowerCase()} wheel`"
            @click="emit('spin')"
        >
            {{ spinning ? 'Spinning…' : 'Spin' }}
        </button>
    </section>
</template>
