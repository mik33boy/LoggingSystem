<script context="module">
  // Module context exports that can be imported via named exports
  export const openModal = (logEntry) => {
    if (modalInstance) {
      modalInstance.openModal(logEntry);
    } else {
      console.error('Modal instance not initialized yet');
    }
  };

  // Track the modal instance
  let modalInstance;
</script>

<script lang="ts">
    import { createEventDispatcher, onMount, onDestroy } from 'svelte';
  
    const dispatch = createEventDispatcher();
    
    // Interface for log entry
    interface LogEntry {
      commType: string;
      direction: string;
      fromto: string;
      subject: string;
      details: string;
      datetime: string;
      attachments?: string[];
      confidential?: boolean;
    }
    
    // Store for the currently viewed log entry
    let currentLog: LogEntry | null = null;
    let show = false;
    
    // Function to open the modal with a log entry
    function openModal(logEntry: LogEntry): void {
      currentLog = logEntry;
      show = true;
    }
    
    // Close the modal
    function closeModal(): void {
      show = false;
      dispatch('close');
    }
    
    // Format date for display
    function formatDate(dateString: string): string {
      if (!dateString) return '';
      return new Date(dateString).toLocaleString();
    }
    
    // Register this component instance for external access
    onMount(() => {
      modalInstance = { openModal };
      return () => {
        modalInstance = undefined;
      };
    });
    
    // Cleanup on destroy
    onDestroy(() => {
      modalInstance = undefined;
    });
  </script>
  
  {#if show && currentLog}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
         on:click={(e) => e.target === e.currentTarget && closeModal()}>
      <div class="bg-white p-6 rounded-xl w-full max-w-2xl shadow-lg">
        <div class="flex justify-between items-start mb-4">
          <h2 class="text-xl font-semibold">{currentLog.subject || 'Log Details'}</h2>
          <button 
            class="text-gray-500 hover:text-gray-700" 
            on:click={closeModal}
            aria-label="Close"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Communication Type</p>
              <p class="font-medium">{currentLog.commType || 'N/A'}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Direction</p>
              <p class="font-medium">{currentLog.direction || 'N/A'}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">From/To</p>
              <p class="font-medium">{currentLog.fromto || 'N/A'}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Date/Time</p>
              <p class="font-medium">{formatDate(currentLog.datetime)}</p>
            </div>
          </div>
        </div>
        
        <div class="mb-4">
          <h3 class="font-medium mb-2">Details</h3>
          <div class="border rounded-lg p-4 whitespace-pre-wrap">
            {currentLog.details || 'No details provided.'}
          </div>
        </div>
        
        {#if currentLog.attachments && currentLog.attachments.length > 0}
          <div>
            <h3 class="font-medium mb-2">Attachments</h3>
            <ul class="border rounded-lg p-4 space-y-2">
              {#each currentLog.attachments as attachment}
                <li class="flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <span>{attachment}</span>
                </li>
              {/each}
            </ul>
          </div>
        {/if}
        
        <div class="mt-6 flex justify-end">
          {#if currentLog.confidential}
            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded mr-auto">Confidential</span>
          {/if}
          <button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" on:click={closeModal}>Close</button>
        </div>
      </div>
    </div>
  {/if} 