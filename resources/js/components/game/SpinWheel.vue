<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import {
    labelPlacement,
    rotationFor,
    segmentColour,
    segmentPath,
    SPIN_DURATION_MS,
    SPIN_EASING,
} from '@/lib/wheel';
import type { WheelSegment } from '@/types/game';

const props = defineProps<{
    segments: WheelSegment[];
    /** Accessible name, e.g. "Question wheel". */
    label: string;
}>();

/** viewBox units; the rendered size comes from CSS. */
const RADIUS = 250;
const LABEL_RADIUS = RADIUS * 0.86;

const rotation = ref(0);
const isSpinning = ref(false);
const wheel = ref<SVGSVGElement | null>(null);

let settle: (() => void) | null = null;
let fallbackTimer: ReturnType<typeof setTimeout> | undefined;

const slices = computed(() =>
    props.segments.map((segment, index) => {
        const count = props.segments.length;
        const label = labelPlacement(
            index,
            count,
            LABEL_RADIUS,
            RADIUS,
            RADIUS,
        );

        return {
            ...segment,
            path: segmentPath(index, count, RADIUS),
            colour: segmentColour(index, count),
            label,
        };
    }),
);

const usedCount = computed(
    () => props.segments.filter((segment) => segment.used).length,
);

const description = computed(() => {
    const total = props.segments.length;

    return usedCount.value === 0
        ? `${props.label}, ${total} segments`
        : `${props.label}, ${total} segments, ${usedCount.value} already played this round`;
});

function prefersReducedMotion(): boolean {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function finish(): void {
    clearTimeout(fallbackTimer);
    isSpinning.value = false;
    settle?.();
    settle = null;
}

/**
 * Spin until the segment numbered `position` is under the pointer.
 * Resolves when the wheel has stopped.
 */
function spinTo(position: number): Promise<void> {
    const index = props.segments.findIndex(
        (segment) => segment.position === position,
    );

    if (index === -1) {
        return Promise.resolve();
    }

    finish();

    const reduceMotion = prefersReducedMotion();
    const jitter = Math.random() - 0.5;

    rotation.value = rotationFor(
        index,
        props.segments.length,
        rotation.value,
        jitter,
    );

    if (reduceMotion) {
        return Promise.resolve();
    }

    isSpinning.value = true;

    return new Promise((resolve) => {
        settle = resolve;
        // transitionend can be skipped (hidden tab, interrupted transition), so never wait forever.
        fallbackTimer = setTimeout(finish, SPIN_DURATION_MS + 250);
    });
}

function onTransitionEnd(event: TransitionEvent): void {
    if (event.target === wheel.value && event.propertyName === 'transform') {
        finish();
    }
}

onBeforeUnmount(() => clearTimeout(fallbackTimer));

defineExpose({ spinTo });
</script>

<template>
    <div class="relative flex items-center gap-[0.074rem]">
        <svg
            ref="wheel"
            :viewBox="`0 0 ${RADIUS * 2} ${RADIUS * 2}`"
            class="size-[min(30.875rem,calc(100vw-10rem))] shrink-0 rounded-full drop-shadow-[0_0.5rem_1.5rem_rgb(0_0_0/.35)] will-change-transform"
            :style="{
                transform: `rotate(${rotation}deg)`,
                transition: isSpinning
                    ? `transform ${SPIN_DURATION_MS}ms ${SPIN_EASING}`
                    : 'none',
            }"
            role="img"
            :aria-label="description"
            @transitionend="onTransitionEnd"
        >
            <circle
                v-if="slices.length === 1"
                :cx="RADIUS"
                :cy="RADIUS"
                :r="RADIUS"
                :fill="slices[0].colour"
            />
            <g v-for="slice in slices" :key="slice.position">
                <path
                    v-if="slices.length > 1"
                    :d="slice.path"
                    :fill="slice.colour"
                />
                <path
                    v-if="slice.used && slices.length > 1"
                    :d="slice.path"
                    fill="rgb(0 0 0 / 0.55)"
                />
                <text
                    :x="slice.label.x"
                    :y="slice.label.y"
                    :transform="`rotate(${slice.label.angle} ${slice.label.x} ${slice.label.y})`"
                    text-anchor="middle"
                    dominant-baseline="central"
                    class="fill-white font-wheel text-[21.7px] font-bold"
                    :class="slice.used && 'opacity-45'"
                >
                    {{ slice.position }}
                </text>
            </g>
        </svg>

        <img
            src="/images/game/pointer.svg"
            alt=""
            width="34.831"
            height="23.22"
            class="h-[1.451rem] w-[2.177rem] shrink-0 drop-shadow-[0_0.125rem_0.25rem_rgb(0_0_0/.4)]"
        />
    </div>
</template>
