<script lang="ts">
    import { API } from '$lib/api';
    import { goto } from '$app/navigation';
    import type { Log } from '$lib/api';
    
    export let id: number | null = null;
    
    let log: Log = {
      direction: 'incoming',
      type: 'email',
      subject: '',
      content: '',
      sender: '',
      recipient: '',
      timestamp: new Date().toISOString().slice(0, 16),
      confidentiality_level: 'public'
    };
    
    let error = '';
    let loading = false;
    
    async function loadLog() {
      if (!id) return;
      
      try {
        loading = true;
        const data = await API.getLog(id);
        log = {
          direction: data.direction,
          type: data.type,
          subject: data.subject,
          content: data.content || '',
          sender: data.sender || '',
          recipient: data.recipient || '',
          timestamp: data.timestamp.slice(0, 16), // Convert to datetime-local format
          confidentiality_level: data.confidentiality_level || 'public',
          id: data.id
        };
      } catch (err) {
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred';
        }
      } finally {
        loading = false;
      }
    }
    
    async function saveLog() {
      try {
        loading = true;
        error = '';
        
        if (id) {
          await API.updateLog(id, log);
        } else {
          await API.createLog(log);
        }
        
        goto('/logs');
      } catch (err) {
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred';
        }
      } finally {
        loading = false;
      }
    }
    
    $: if (id) loadLog();
  </script>
  
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{id ? 'Edit' : 'Create New'} Communication Log</h1>
    
    {#if error}
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {error}
      </div>
    {/if}
    
    <form on:submit|preventDefault={saveLog} class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
          <select 
            bind:value={log.direction}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="incoming">Incoming</option>
            <option value="outgoing">Outgoing</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select 
            bind:value={log.type}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="email">Email</option>
            <option value="memo">Memo</option>
            <option value="fax">Fax</option>
            <option value="letter">Letter</option>
            <option value="phone">Phone</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
        <input 
          type="text" 
          bind:value={log.subject}
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
        <textarea 
          bind:value={log.content}
          rows="5"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        ></textarea>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {log.direction === 'incoming' ? 'Sender' : 'Recipient'}
          </label>
          <input 
            type="text" 
            value={log.direction === 'incoming' ? log.sender : log.recipient}
            on:input={(e) => {
              if (log.direction === 'incoming') {
                log.sender = e.currentTarget.value;
              } else {
                log.recipient = e.currentTarget.value;
              }
            }}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date/Time</label>
          <input 
            type="datetime-local" 
            bind:value={log.timestamp}
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confidentiality Level</label>
        <select 
          bind:value={log.confidentiality_level}
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="public">Public</option>
          <option value="confidential">Confidential</option>
          <option value="secret">Secret</option>
        </select>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          on:click={() => goto('/logs')}
          class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={loading}
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {loading ? 'Saving...' : 'Save'}
        </button>
      </div>
    </form>
  </div>