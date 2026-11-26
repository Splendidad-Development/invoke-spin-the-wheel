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

/** Only shown once the wheel has played all its rounds, to explain the disabled Spin button. */
const finishedMessage = computed(
    () =>
        `All ${props.wheel.label.toLowerCase()}s played. Restart to play again.`,
);

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
                v-if="wheel.finished"
                class="m-0 text-[1.0625rem] leading-normal font-medium text-headline"
                role="status"
            >
                {{ finishedMessage }}
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
