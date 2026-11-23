<script setup lang="ts">
import { computed } from 'vue';
import GameModal from '@/components/game/GameModal.vue';
import type { ResultRow } from '@/types/game';

const props = defineProps<{
    open: boolean;
    results: ResultRow[];
}>();

const emit = defineEmits<{ close: []; openScoreSheet: [] }>();

/** Ranks 1-3 sit in their own highlighted block, as in the design. */
const podium = computed(() => props.results.filter((row) => row.rank <= 3));
const rest = computed(() => props.results.filter((row) => row.rank > 3));

const rowClass =
    'grid grid-cols-[3rem_minmax(0,1fr)_6rem] items-center gap-4 [&>span:nth-child(2)]:truncate [&>span:nth-child(2)]:text-center [&>span:nth-child(3)]:text-right [&>span]:tabular-nums';

function rowLabel(row: ResultRow): string {
    return `Rank ${row.rank}: ${row.name}, ${row.score} points`;
}
</script>

<template>
    <GameModal
        :open="open"
        labelledby="results-title"
        panel-class="max-w-[51.5rem] justify-between"
        @close="emit('close')"
    >
        <h2
            id="results-title"
            class="m-0 text-[2.25rem] leading-normal font-bold"
        >
            Results
        </h2>

        <div
            v-if="results.length"
            class="flex w-full flex-col gap-[1.875rem] text-[1.4375rem] leading-normal font-medium"
        >
            <ol
                v-if="podium.length"
                class="m-0 flex list-none flex-col gap-5 rounded-[1.25rem] bg-glass px-10 py-5"
                aria-label="Top three"
            >
                <li
                    v-for="row in podium"
                    :key="`${row.rank}-${row.name}`"
                    :class="rowClass"
                    :aria-label="rowLabel(row)"
                >
                    <span>{{ row.rank }}</span>
                    <span>{{ row.name }}</span>
                    <span>{{ row.score }}</span>
                </li>
            </ol>

            <ol
                v-if="rest.length"
                class="m-0 flex list-none flex-col gap-[1.3rem] px-10"
                aria-label="Everyone else in the top ten"
            >
                <li
                    v-for="row in rest"
                    :key="`${row.rank}-${row.name}`"
                    :class="rowClass"
                    :aria-label="rowLabel(row)"
                >
                    <span>{{ row.rank }}</span>
                    <span>{{ row.name }}</span>
                    <span>{{ row.score }}</span>
                </li>
            </ol>
        </div>

        <div v-else class="flex flex-col items-center gap-3 py-8 text-center">
            <p class="m-0 text-[1.4375rem] font-medium">No scores yet.</p>
            <p class="m-0 text-lg text-white/80">
                Add players and their points on the score sheet to see who's
                leading.
            </p>
            <button
                type="button"
                class="cursor-pointer rounded-full px-4 py-2 text-lg font-semibold underline-offset-4 hover:underline"
                @click="emit('openScoreSheet')"
            >
                Open score sheet
            </button>
        </div>

        <button
            type="button"
            class="pill"
            data-autofocus
            @click="emit('close')"
        >
            Done
        </button>
    </GameModal>
</template>
