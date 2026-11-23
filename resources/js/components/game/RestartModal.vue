<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import GameModal from '@/components/game/GameModal.vue';
import { restart } from '@/routes/game';

defineProps<{ open: boolean; gameId: number }>();

const emit = defineEmits<{ close: [] }>();
</script>

<template>
    <GameModal
        :open="open"
        labelledby="restart-title"
        describedby="restart-body"
        @close="emit('close')"
    >
        <h2
            id="restart-title"
            class="m-0 text-[1.4375rem] leading-normal font-bold"
        >
            Restart the game?
        </h2>
        <p
            id="restart-body"
            class="m-0 max-w-[40.25rem] text-center text-[1.4375rem] leading-normal font-medium text-balance"
        >
            Both wheels reset and the score sheet starts empty. This game's
            scores are kept on record.
        </p>
        <Form
            v-bind="restart.form(gameId)"
            class="flex flex-wrap justify-center gap-3"
            #default="{ processing }"
            @success="emit('close')"
        >
            <button
                type="button"
                class="pill"
                data-autofocus
                @click="emit('close')"
            >
                Keep playing
            </button>
            <button type="submit" class="pill" :disabled="processing">
                {{ processing ? 'Restarting…' : 'Restart' }}
            </button>
        </Form>
    </GameModal>
</template>
