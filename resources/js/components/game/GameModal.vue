<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open: boolean;
        labelledby: string;
        describedby?: string;
        panelClass?: string;
        showClose?: boolean;
    }>(),
    {
        describedby: undefined,
        panelClass: '',
        showClose: true,
    },
);

const emit = defineEmits<{ close: [] }>();

const panel = ref<HTMLElement | null>(null);
let previouslyFocused: HTMLElement | null = null;

/**
 * Only render in the browser: lifecycle hooks don't run during SSR, so server and hydrating
 * client both render nothing and the DOM is only touched once mounted.
 */
const isMounted = ref(false);

const FOCUSABLE =
    'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

function focusables(): HTMLElement[] {
    return Array.from(
        panel.value?.querySelectorAll<HTMLElement>(FOCUSABLE) ?? [],
    );
}

/** Escape closes; Tab stays inside the dialog. */
function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        event.preventDefault();
        emit('close');

        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const items = focusables();

    if (items.length === 0) {
        return;
    }

    const first = items[0];
    const last = items[items.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

async function activate(): Promise<void> {
    previouslyFocused = document.activeElement as HTMLElement | null;
    document.addEventListener('keydown', onKeydown);
    await nextTick();
    (
        panel.value?.querySelector<HTMLElement>('[data-autofocus]') ??
        focusables()[0]
    )?.focus({ preventScroll: true });
}

function deactivate(): void {
    document.removeEventListener('keydown', onKeydown);
    previouslyFocused?.focus?.({ preventScroll: true });
    previouslyFocused = null;
}

onMounted(() => {
    isMounted.value = true;

    if (props.open) {
        void activate();
    }
});

watch(
    () => props.open,
    (isOpen) => (isOpen ? void activate() : deactivate()),
);

onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport v-if="isMounted" to="body">
        <Transition
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-100 flex items-center justify-center overflow-y-auto bg-black/50 p-4 font-sans text-white md:p-[3.5rem]"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="labelledby"
                :aria-describedby="describedby"
                @click.self="emit('close')"
            >
                <div
                    ref="panel"
                    :class="
                        cn(
                            'glass-panel relative my-auto flex w-full max-w-[45.25rem] flex-col items-center gap-5 px-10 py-5 shadow-[0_1.5rem_5rem_rgb(0_0_0/.45)]',
                            panelClass,
                        )
                    "
                >
                    <button
                        v-if="showClose"
                        type="button"
                        class="absolute top-3 right-3 z-10 size-[2.66rem] cursor-pointer rounded-full transition hover:scale-110 md:top-[-1.4375rem] md:right-auto md:left-full"
                        aria-label="Close"
                        @click="emit('close')"
                    >
                        <img
                            src="/images/game/icon-close.svg"
                            alt=""
                            width="42.535"
                            height="42.535"
                            class="size-full"
                        />
                    </button>
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
