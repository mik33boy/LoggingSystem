<script lang="ts">
  import { createEventDispatcher } from 'svelte';

  export let show: boolean = false;
  export let message: string = '';
  export let type: 'success' | 'error' | 'info' = 'info';

  const dispatch = createEventDispatcher();

  function close() {
    show = false;
    dispatch('close');
  }

  let iconColorClass = 'text-blue-600';
  let iconBgClass = 'bg-blue-100';
  let buttonColorClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';

  $: {
    switch (type) {
      case 'success':
        iconColorClass = 'text-green-600';
        iconBgClass = 'bg-green-100';
        buttonColorClass = 'bg-green-600 hover:bg-green-700 focus:ring-green-500';
        break;
      case 'error':
        iconColorClass = 'text-red-600';
        iconBgClass = 'bg-red-100';
        buttonColorClass = 'bg-red-600 hover:bg-red-700 focus:ring-red-500';
        break;
      case 'info':
      default:
        iconColorClass = 'text-blue-600';
        iconBgClass = 'bg-blue-100';
        buttonColorClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';
        break;
    }
  }
</script>

{#if show}
  <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-60 animate-fadeIn px-4 py-6 sm:px-0">
    <div class="modal-content bg-white rounded-xl p-6 w-full max-w-sm relative shadow-2xl transform transition-all sm:align-middle border border-gray-200">
      <div class="flex items-start">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {iconBgClass} sm:mx-0 sm:h-10 sm:w-10">
          {#if type === 'success'}
            <svg class="h-7 w-7 {iconColorClass}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          {:else if type === 'error'}
            <svg class="h-7 w-7 {iconColorClass}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          {:else}
             <svg class="h-7 w-7 {iconColorClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          {/if}
        </div>
        <div class="mt-1 ml-4 text-left">
          <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
            Notification
          </h3>
          <div class="mt-2">
            <p class="text-sm text-gray-600">
              {message}
            </p>
          </div>
        </div>
      </div>
      <div class="mt-5 sm:mt-6 flex justify-end">
        <button type="button" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {buttonColorClass} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm" on:click={close}>
          OK
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  .animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
  }
</style> 