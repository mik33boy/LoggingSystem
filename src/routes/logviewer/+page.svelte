<!-- LogViewer.svelte -->
<script lang="ts">
    import { onMount } from 'svelte';
    import { browser } from '$app/environment';
    import { API } from '$lib/api';
    import type { Log } from '$lib/api';
    
    let logs: Log[] = [];
    let loading = true;
    let error: string | null = null;
    let deletingLogId: number | null = null;
    let confirmDelete = false;
    
    // Filter state
    let filters = {
      direction: '',
      type: '',
      fromDate: '',
      toDate: '',
      search: '',
      confidentiality: ''
    };
    
    onMount(async () => {
      try {
        logs = await API.getLogs();
        loading = false;
      } catch (err) {
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred';
        }
        loading = false;
      }
    });
    
    async function applyFilters() {
      try {
        loading = true;
        logs = await API.getLogs(filters);
        loading = false;
      } catch (err) {
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred';
        }
        loading = false;
      }
    }
    
    function clearFilters() {
      filters = {
        direction: '',
        type: '',
        fromDate: '',
        toDate: '',
        search: '',
        confidentiality: ''
      };
      applyFilters();
    }
    
    // Function to prepare log for deletion
    function prepareDelete(logId: number) {
      deletingLogId = logId;
      confirmDelete = true;
    }
    
    // Function to cancel deletion
    function cancelDelete() {
      deletingLogId = null;
      confirmDelete = false;
    }
    
    // Function to confirm and execute deletion
    async function confirmDeleteLog() {
      if (!deletingLogId) return;
      
      try {
        loading = true;
        await API.deleteLog(deletingLogId);
        
        // Remove the deleted log from the array
        logs = logs.filter(log => log.id !== deletingLogId);
        
        // Reset deletion state
        deletingLogId = null;
        confirmDelete = false;
      } catch (err) {
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred while deleting the log';
        }
      } finally {
        loading = false;
      }
    }
  </script>
  
  <div class="space-y-4">
    <div class="bg-white p-4 rounded-lg shadow">
      <h2 class="text-lg font-semibold mb-4">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Direction</label>
          <select bind:value={filters.direction} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">All</option>
            <option value="incoming">Incoming</option>
            <option value="outgoing">Outgoing</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Type</label>
          <select bind:value={filters.type} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">All</option>
            <option value="email">Email</option>
            <option value="memo">Memo</option>
            <option value="fax">Fax</option>
            <option value="letter">Letter</option>
            <option value="phone">Phone</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Confidentiality</label>
          <select bind:value={filters.confidentiality} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">All</option>
            <option value="public">Public</option>
            <option value="confidential">Confidential</option>
            <option value="secret">Secret</option>
          </select>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">From Date</label>
          <input type="date" bind:value={filters.fromDate} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">To Date</label>
          <input type="date" bind:value={filters.toDate} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Search</label>
          <input type="text" bind:value={filters.search} placeholder="Search subject/content" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
      </div>
      
      <div class="mt-4 flex space-x-2">
        <button on:click={applyFilters} class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Apply Filters
        </button>
        <button on:click={clearFilters} class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
          Clear Filters
        </button>
      </div>
    </div>
    
    {#if loading}
      <div class="text-center py-8">Loading...</div>
    {:else if error}
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {error}
      </div>
    {:else if logs.length === 0}
      <div class="text-center py-8 bg-white rounded-lg shadow">
        <p class="text-gray-500">No logs found matching your criteria.</p>
      </div>
    {:else}
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direction</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confidentiality</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            {#each logs as log (log.id)}
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{new Date(log.timestamp).toLocaleString()}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{log.direction}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{log.type}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{log.subject}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{log.confidentiality_level}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <a href="/logs/edit/{log.id}" class="text-blue-600 hover:text-blue-900 mr-3">View/Edit</a>
                  <button 
                    on:click={() => prepareDelete(log.id as number)} 
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    {/if}
    
    <!-- Delete Confirmation Modal -->
    {#if confirmDelete}
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Deletion</h3>
          <p class="text-gray-700 mb-6">Are you sure you want to delete this log? This action cannot be undone.</p>
          <div class="flex justify-end space-x-3">
            <button 
              on:click={cancelDelete}
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Cancel
            </button>
            <button 
              on:click={confirmDeleteLog}
              class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    {/if}
  </div>