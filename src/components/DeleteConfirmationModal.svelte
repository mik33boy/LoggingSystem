<script lang="ts">
    import { createEventDispatcher } from 'svelte';

    export let show: boolean = false;
    export let message: string = 'Are you sure you want to delete this item?';

    const dispatch = createEventDispatcher<{
        confirm: void;
        cancel: void;
    }>();

    function confirm(): void {
        dispatch('confirm');
    }

    function cancel(): void {
        dispatch('cancel');
    }
</script>

{#if show}
    <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-40 animate-fadeIn">
        <div class="bg-white p-8 rounded-2xl w-full max-w-sm relative shadow-2xl border border-red-100 animate-slideUp">
            <h2 class="text-xl font-extrabold mb-4 text-red-700 flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                Confirm Deletion
            </h2>
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">{message}</p>

            <div class="flex justify-end gap-3">
                <button
                    class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition"
                    on:click={cancel}
                >
                    Cancel
                </button>
                <button
                    class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold shadow transition"
                    on:click={confirm}
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    .animate-slideUp {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style> 