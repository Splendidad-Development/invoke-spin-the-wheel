<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import GameModal from '@/components/game/GameModal.vue';
import type { SpinResult } from '@/types/game';

const props = defineProps<{ prompt: SpinResult | null }>();

const emit = defineEmits<{ close: [] }>();

/** The host reveals the answer once the room has had its say. */
const isRevealed = ref(false);
const doneButton = ref<HTMLButtonElement | null>(null);

watch(
    () => props.prompt?.id,
    () => {
        isRevealed.value = false;
    },
);

const canReveal = computed(
    () => !!props.prompt?.answer && (props.prompt?.options.length ?? 0) > 0,
);

/** Key message split into a lead paragraph and bullet points ("• ..." lines). */
const keyMessage = computed(() => {
    const lines = (props.prompt?.keyMessage ?? '')
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);

    return {
        lead: lines.filter((line) => !line.startsWith('•')),
        points: lines
            .filter((line) => line.startsWith('•'))
            .map((line) => line.replace(/^•\s*/, '')),
    };
});

async function reveal(): Promise<void> {
    isRevealed.value = true;
    await nextTick();
    doneButton.value?.focus({ preventScroll: true });
}

function optionState(letter: string): 'correct' | 'other' | 'idle' {
    if (!isRevealed.value) {
        return 'idle';
    }

    return letter === props.prompt?.answer ? 'correct' : 'other';
}
</script>

<template>
    <GameModal
        :open="prompt !== null"
        labelledby="prompt-title"
        describedby="prompt-body"
        :panel-class="prompt?.options.length ? 'max-w-[60rem]' : ''"
        @close="emit('close')"
    >
        <template v-if="prompt">
            <h2
                id="prompt-title"
                class="m-0 text-[1.4375rem] leading-normal font-bold"
            >
                {{ prompt.title }}
            </h2>
            <p
                id="prompt-body"
                class="m-0 max-w-[52rem] text-center text-[1.4375rem] leading-normal font-medium text-balance"
            >
                {{ prompt.body }}
            </p>

            <ol
                v-if="prompt.options.length"
                class="m-0 grid w-full list-none gap-3 p-0"
                aria-label="Options"
            >
                <li
                    v-for="option in prompt.options"
                    :key="option.letter"
                    class="flex items-start gap-4 rounded-[0.75rem] px-5 py-3 text-left text-[1.25rem] leading-snug transition"
                    :class="{
                        'bg-field': optionState(option.letter) === 'idle',
                        'bg-headline/25 outline-3 outline-headline':
                            optionState(option.letter) === 'correct',
                        'bg-field opacity-45':
                            optionState(option.letter) === 'other',
                    }"
                >
                    <span class="shrink-0 font-bold">{{ option.letter }}.</span>
                    <span class="flex-1">{{ option.text }}</span>
                    <span
                        v-if="optionState(option.letter) === 'correct'"
                        class="shrink-0 font-bold text-headline"
                    >
                        Correct
                    </span>
                </li>
            </ol>

            <div
                v-if="isRevealed && prompt.keyMessage"
                class="w-full rounded-[1.25rem] bg-glass px-6 py-4 text-left text-[1.125rem] leading-normal"
                role="region"
                aria-label="Key message"
            >
                <p class="m-0 mb-1 font-bold">Key message</p>
                <p
                    v-for="(line, index) in keyMessage.lead"
                    :key="`lead-${index}`"
                    class="m-0"
                >
                    {{ line }}
                </p>
                <ul
                    v-if="keyMessage.points.length"
                    class="m-0 mt-1 list-disc pl-6"
                >
                    <li
                        v-for="(point, index) in keyMessage.points"
                        :key="index"
                    >
                        {{ point }}
                    </li>
                </ul>
            </div>

            <p class="sr-only" aria-live="polite">
                {{ isRevealed ? `Correct answer: ${prompt.answer}` : '' }}
            </p>

            <div class="flex flex-wrap justify-center gap-3">
                <button
                    v-if="canReveal && !isRevealed"
                    type="button"
                    class="pill"
                    data-autofocus
                    @click="reveal"
                >
                    Show answer
                </button>
                <button
                    ref="doneButton"
                    type="button"
                    class="pill"
                    :data-autofocus="canReveal ? undefined : ''"
                    @click="emit('close')"
                >
                    Done
                </button>
            </div>
        </template>
    </GameModal>
</template>
