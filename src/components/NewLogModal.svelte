<script lang="ts">
    import { createEventDispatcher, onMount } from 'svelte';
  
    const dispatch = createEventDispatcher();
    export let showModal = false;
  
    // For backward compatibility
    export function openModal() {
      showModal = true;
    }
  
    function closeModal() {
      showModal = false;
      dispatch('cancel');
    }
  
    let now = '';
  
    onMount(() => {
      const date = new Date();
      now = date.toISOString().slice(0, 16);
    });
  
    function submitForm(e: Event) {
      e.preventDefault();
      const form = e.target as HTMLFormElement;
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
  
      const formData = new FormData(form);
      const data = Object.fromEntries(formData);
      dispatch('submit', data);
      closeModal();
    }
  </script>
  
  {#if showModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" on:click={(e) => e.target === e.currentTarget && closeModal()}>
      <div class="modal-content bg-white p-6 rounded-xl w-full max-w-lg shadow-lg">
        <h2 class="text-xl font-semibold text-center text-blue-900 mb-4">New Log Entry</h2>
  
        <form class="space-y-4" on:submit={submitForm}>
          <div>
            <label for="comm-type" class="block text-sm font-medium text-gray-700 mb-1">Communication Type</label>
            <select id="comm-type" name="comm-type" required class="w-full border rounded-md px-3 py-2">
              <option value="" disabled selected>Select type...</option>
              <option value="memo">Memo</option>
              <option value="fax">Fax</option>
              <option value="email">Email</option>
              <option value="letter">Letter</option>
              <option value="call">Call</option>
              <option value="meeting">Meeting</option>
              <option value="text-message">Text Message</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label for="direction" class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
            <select id="direction" name="direction" required class="w-full border rounded-md px-3 py-2">
              <option value="" disabled selected>Select direction...</option>
              <option value="incoming">Incoming</option>
              <option value="outgoing">Outgoing</option>
            </select>
          </div>
  
          <div>
            <label for="fromto" class="block text-sm font-medium text-gray-700 mb-1">From / To</label>
            <input type="text" id="fromto" name="fromto" required class="w-full border rounded-md px-3 py-2" />
          </div>
          
          <div>
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject / Summary</label>
            <input type="text" id="subject" name="subject" required class="w-full border rounded-md px-3 py-2" />
          </div>
          
          <div>
            <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Details / Notes</label>
            <textarea id="details" name="details" required class="w-full border rounded-md px-3 py-2 min-h-[80px]"></textarea>
          </div>
  
          <div>
            <label for="datetime" class="block text-sm font-medium text-gray-700 mb-1">Date/Time</label>
            <input type="datetime-local" id="datetime" name="datetime" bind:value={now} required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Attachments</label>
            <input type="file" id="attachments" name="attachments" multiple class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="inline-flex items-center">
              <input type="checkbox" id="confidential" name="confidential" class="mr-2">
              Confidential
            </label>
          </div>
  
          <div class="flex justify-end gap-2 pt-4">
            <button type="button" on:click={closeModal} class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
          </div>
        </form>
      </div>
    </div>
  {/if}
  