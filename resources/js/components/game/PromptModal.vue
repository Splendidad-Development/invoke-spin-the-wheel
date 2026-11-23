<script setup lang="ts">
import GameModal from '@/components/game/GameModal.vue';
import type { SpinResult } from '@/types/game';

defineProps<{ prompt: SpinResult | null }>();

const emit = defineEmits<{ close: [] }>();
</script>

<template>
    <GameModal
        :open="prompt !== null"
        labelledby="prompt-title"
        describedby="prompt-body"
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
                class="m-0 max-w-[40.25rem] text-center text-[1.4375rem] leading-normal font-medium text-balance"
            >
                {{ prompt.body }}
            </p>
            <button
                type="button"
                class="pill"
                data-autofocus
                @click="emit('close')"
            >
                Done
            </button>
        </template>
    </GameModal>
</template>
