<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import GameModal from '@/components/game/GameModal.vue';
import { update } from '@/routes/players';
import type { PlayerRow } from '@/types/game';

const props = defineProps<{
    open: boolean;
    gameId: number;
    players: PlayerRow[];
}>();

const emit = defineEmits<{ close: [] }>();

/** Rows shown even when the sheet is empty, as in the design. */
const MIN_ROWS = 7;
const MAX_ROWS = 50;

type EditableRow = { name: string; score: number | string | null };

function rowsFrom(players: PlayerRow[]): EditableRow[] {
    const rows: EditableRow[] = players.map((player) => ({ ...player }));

    while (rows.length < MIN_ROWS) {
        rows.push({ name: '', score: null });
    }

    return rows;
}

const form = useForm<{ players: EditableRow[] }>({
    players: rowsFrom(props.players),
});

const list = ref<HTMLElement | null>(null);

// Start from what's saved every time the sheet opens.
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.defaults({ players: rowsFrom(props.players) });
            form.reset();
            form.clearErrors();
        }
    },
);

const canAddRow = computed(() => form.players.length < MAX_ROWS);

async function addRow(): Promise<void> {
    form.players.push({ name: '', score: null });
    await nextTick();
    list.value?.querySelector<HTMLInputElement>('li:last-child input')?.focus();
}

function errorFor(index: number, field: 'name' | 'score'): string | undefined {
    return (form.errors as Record<string, string | undefined>)[
        `players.${index}.${field}`
    ];
}

/** Every way out of the sheet saves it, so scores are never lost to a stray Escape. */
function saveAndClose(): void {
    if (form.processing) {
        return;
    }

    if (!form.isDirty) {
        emit('close');

        return;
    }

    form.put(update.url(props.gameId), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => emit('close'),
        // The list scrolls, so bring the first problem into view.
        onError: async () => {
            await nextTick();
            const invalid = list.value?.querySelector<HTMLInputElement>(
                '[aria-invalid="true"]',
            );
            invalid?.focus({ preventScroll: true });
            invalid
                ?.closest('li')
                ?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        },
    });
}
</script>

<template>
    <GameModal
        :open="open"
        labelledby="score-sheet-title"
        panel-class="max-w-[88rem] justify-between"
        @close="saveAndClose"
    >
        <h2
            id="score-sheet-title"
            class="m-0 text-[2.25rem] leading-normal font-bold"
        >
            Score sheet
        </h2>

        <form
            id="score-sheet-form"
            class="flex w-full flex-col gap-4"
            novalidate
            @submit.prevent="saveAndClose"
        >
            <div
                class="grid grid-cols-[minmax(0,1fr)_minmax(0,0.6fr)] gap-x-4 text-center text-[1.4375rem] leading-normal font-medium md:grid-cols-2 md:gap-x-10"
                aria-hidden="true"
            >
                <span>Player name</span>
                <span>Score</span>
            </div>

            <ol
                ref="list"
                class="m-0 flex max-h-[min(30.625rem,55svh)] list-none flex-col gap-5 overflow-y-auto p-1"
            >
                <li
                    v-for="(row, index) in form.players"
                    :key="index"
                    class="grid grid-cols-[minmax(0,1fr)_minmax(0,0.6fr)] gap-x-4 gap-y-1 md:grid-cols-2 md:gap-x-10"
                >
                    <div>
                        <label :for="`player-${index}-name`" class="sr-only">
                            Player {{ index + 1 }} name
                        </label>
                        <input
                            :id="`player-${index}-name`"
                            v-model="row.name"
                            :data-autofocus="index === 0 ? '' : undefined"
                            class="field"
                            type="text"
                            maxlength="40"
                            autocomplete="off"
                            placeholder="Name"
                            :aria-invalid="!!errorFor(index, 'name')"
                            :aria-describedby="
                                errorFor(index, 'name')
                                    ? `player-${index}-name-error`
                                    : undefined
                            "
                        />
                        <p
                            v-if="errorFor(index, 'name')"
                            :id="`player-${index}-name-error`"
                            class="mt-1 mb-0 text-sm font-semibold text-danger"
                        >
                            {{ errorFor(index, 'name') }}
                        </p>
                    </div>
                    <div>
                        <label :for="`player-${index}-score`" class="sr-only">
                            Player {{ index + 1 }} score
                        </label>
                        <input
                            :id="`player-${index}-score`"
                            v-model="row.score"
                            class="field"
                            type="number"
                            inputmode="numeric"
                            step="1"
                            placeholder="Score"
                            :aria-invalid="!!errorFor(index, 'score')"
                            :aria-describedby="
                                errorFor(index, 'score')
                                    ? `player-${index}-score-error`
                                    : undefined
                            "
                        />
                        <p
                            v-if="errorFor(index, 'score')"
                            :id="`player-${index}-score-error`"
                            class="mt-1 mb-0 text-sm font-semibold text-danger"
                        >
                            {{ errorFor(index, 'score') }}
                        </p>
                    </div>
                </li>
            </ol>

            <div
                class="flex flex-wrap items-center justify-between gap-3 text-base text-white/80"
            >
                <p class="m-0">Leave a name blank to remove that row.</p>
                <button
                    v-if="canAddRow"
                    type="button"
                    class="cursor-pointer rounded-full px-4 py-2 font-semibold text-white underline-offset-4 hover:underline"
                    @click="addRow"
                >
                    + Add player
                </button>
            </div>

            <p
                v-if="form.errors.players"
                class="m-0 text-center font-semibold text-danger"
            >
                {{ form.errors.players }}
            </p>
        </form>

        <button
            type="submit"
            form="score-sheet-form"
            class="pill"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving…' : 'Done' }}
        </button>
    </GameModal>
</template>
