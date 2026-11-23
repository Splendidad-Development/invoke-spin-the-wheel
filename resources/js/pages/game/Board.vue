<script setup lang="ts">
import { Head, useHttp } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import PromptModal from '@/components/game/PromptModal.vue';
import RestartModal from '@/components/game/RestartModal.vue';
import ResultsModal from '@/components/game/ResultsModal.vue';
import ScoreSheetModal from '@/components/game/ScoreSheetModal.vue';
import WheelCard from '@/components/game/WheelCard.vue';
import GameLayout from '@/layouts/GameLayout.vue';
import { store as storeSpin } from '@/routes/spins';
import type {
    PlayerRow,
    ResultRow,
    SpinResponse,
    SpinResult,
    WheelKey,
    WheelState,
} from '@/types/game';

const props = defineProps<{
    game: { id: number };
    wheels: WheelState[];
    players: PlayerRow[];
    results: ResultRow[];
}>();

type Dialog = 'restart' | 'results' | 'scoreSheet' | null;

/** Local copy so a wheel's "used" segments can update after a spin without a page reload. */
const wheels = ref<WheelState[]>(props.wheels);
watch(
    () => props.wheels,
    (next) => {
        wheels.value = next;
    },
);

const dialog = ref<Dialog>(null);
const spinningWheel = ref<WheelKey | null>(null);
const prompt = ref<SpinResult | null>(null);
const spinError = ref<string | null>(null);

/** Applied when the prompt closes, so the landed segment greys out after it has been read. */
let pendingWheel: WheelState | null = null;

const cards = ref<Partial<Record<WheelKey, InstanceType<typeof WheelCard>>>>(
    {},
);

const http = useHttp<{ wheel: WheelKey | '' }, SpinResponse>({ wheel: '' });

const isDialogOpen = computed(
    () => prompt.value !== null || dialog.value !== null,
);

const isBusy = computed(
    () => spinningWheel.value !== null || isDialogOpen.value,
);

/** Let the room see where the wheel stopped before the prompt covers it. */
const LANDING_PAUSE_MS = 700;

function pause(ms: number): Promise<void> {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return Promise.resolve();
    }

    return new Promise((resolve) => setTimeout(resolve, ms));
}

function setCard(key: WheelKey, card: unknown): void {
    if (card) {
        cards.value[key] = card as InstanceType<typeof WheelCard>;
    }
}

async function spin(key: WheelKey): Promise<void> {
    if (isBusy.value) {
        return;
    }

    spinningWheel.value = key;
    spinError.value = null;
    http.wheel = key;

    try {
        const response = await http.post(storeSpin.url(props.game.id));

        await cards.value[key]?.spinTo(response.spin.position);
        await pause(LANDING_PAUSE_MS);

        pendingWheel = response.wheel;
        prompt.value = response.spin;
    } catch {
        spinError.value =
            http.errors.wheel ??
            "The wheel didn't spin. Check the connection and try again.";
    } finally {
        spinningWheel.value = null;
    }
}

function closePrompt(): void {
    prompt.value = null;

    if (pendingWheel) {
        const updated = pendingWheel;
        wheels.value = wheels.value.map((wheel) =>
            wheel.key === updated.key ? updated : wheel,
        );
        pendingWheel = null;
    }
}

function open(next: Exclude<Dialog, null>): void {
    if (spinningWheel.value === null) {
        dialog.value = next;
    }
}
</script>

<template>
    <Head title="Spin the wheel" />
    <GameLayout variant="board">
        <template #nav>
            <nav
                class="flex flex-wrap justify-center gap-3"
                aria-label="Game controls"
            >
                <button
                    type="button"
                    class="pill pill-compact"
                    :disabled="spinningWheel !== null"
                    @click="open('restart')"
                >
                    <img
                        src="/images/game/icon-restart.svg"
                        alt=""
                        width="29.78"
                        height="29.78"
                        class="size-[1.86rem]"
                    />
                    Restart
                </button>
                <button
                    type="button"
                    class="pill"
                    :disabled="spinningWheel !== null"
                    @click="open('results')"
                >
                    Results
                </button>
                <button
                    type="button"
                    class="pill pill-compact"
                    :disabled="spinningWheel !== null"
                    @click="open('scoreSheet')"
                >
                    <img
                        src="/images/game/icon-score.svg"
                        alt=""
                        width="29.777"
                        height="29.745"
                        class="h-[1.859rem] w-[1.861rem]"
                    />
                    Score sheet
                </button>
            </nav>
        </template>

        <div
            class="flex flex-1 flex-col items-center justify-center gap-6 px-4 py-8 md:pb-[9rem]"
        >
            <h1 class="sr-only">Spin the wheel</h1>

            <!-- The design hides the wheels behind dialogs so the frosted panel reads cleanly. -->
            <div
                class="flex flex-col items-center gap-8 transition-opacity duration-200 md:flex-row md:gap-[8.6875rem]"
                :class="isDialogOpen && 'opacity-0'"
            >
                <WheelCard
                    v-for="wheel in wheels"
                    :key="wheel.key"
                    :ref="(card) => setCard(wheel.key, card)"
                    :wheel="wheel"
                    :spinning="spinningWheel === wheel.key"
                    :disabled="isBusy"
                    @spin="spin(wheel.key)"
                />
            </div>

            <p
                class="m-0 min-h-[1.5em] text-center text-lg font-semibold text-danger"
                role="status"
                aria-live="polite"
            >
                {{ spinError }}
            </p>
        </div>

        <PromptModal :prompt="prompt" @close="closePrompt" />

        <RestartModal
            :open="dialog === 'restart'"
            :game-id="game.id"
            @close="dialog = null"
        />

        <ResultsModal
            :open="dialog === 'results'"
            :results="results"
            @close="dialog = null"
            @open-score-sheet="dialog = 'scoreSheet'"
        />

        <ScoreSheetModal
            :open="dialog === 'scoreSheet'"
            :game-id="game.id"
            :players="players"
            @close="dialog = null"
        />
    </GameLayout>
</template>
