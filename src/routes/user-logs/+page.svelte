<script lang="ts">
  import UserSidebar from '../user-sidebar/+page.svelte';
  import UserHeader from '../user-header/+page.svelte';
  import { API_ENDPOINTS, apiRequest } from '$lib/api/config';
  import { onMount } from 'svelte';

  // Logs data
  let logs: any[] = [];
  let sortColumn: string = '';
  let sortDirection: 'asc' | 'desc' = 'asc';
  let selectedType: string = 'All Types';
  let selectedDirection: string = 'All Directions';
  let searchQuery: string = '';
  let selectedDate: string = '';
  let selectedTime: string = '';
  
  // Modal state
  let showModal: boolean = false;
  let commType: string = '';
  let direction: string = '';
  let fromTo: string = '';
  let subject: string = '';
  let details: string = '';
  let dateTime: string = '';
  let confidential: boolean = false;
  let otherType: string = '';
  let emailAddress: string = '';
  let phoneNumber: string = '';

  // View modal state
  let showViewModal = false;
  let selectedLog: any = null;

  // Contacts for autocomplete
  const contacts = [
    "client@abc.com",
    "john.doe@company.com",
    "hr@company.com",
    "jane.smith@external.com",
    "sales@business.org"
  ];
  
  let filteredContacts: string[] = [];
  let showAutocomplete: boolean = false;
  
  // Store user info from localStorage
  let currentUser = {
    id: '',
    username: '',
    firstName: '',
    lastName: ''
  };
  
  function updateFilteredContacts(value: string) {
    if (!value) {
      filteredContacts = [];
      showAutocomplete = false;
      return;
    }
    const val = value.toLowerCase();
    filteredContacts = contacts.filter(c => c.toLowerCase().includes(val));
    showAutocomplete = filteredContacts.length > 0;
  }
  
  function selectContact(contact: string) {
    fromTo = contact;
    showAutocomplete = false;
  }
  
  function openModal() {
    showModal = true;
    commType = '';
    direction = '';
    fromTo = '';
    subject = '';
    details = '';
    // Set default date/time to now
    const now = new Date();
    dateTime = now.toISOString().slice(0, 16);
    confidential = false;
    otherType = '';
    emailAddress = '';
    phoneNumber = '';
    showAutocomplete = false;
  }
  
  function closeModal() {
    showModal = false;
  }
  
  function openViewModal(log: any) {
    selectedLog = log;
    showViewModal = true;
  }
  
  function closeViewModal() {
    showViewModal = false;
    selectedLog = null;
  }
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    
    try {
      // Get form data and process
      const actualCommType = commType === 'other' ? otherType : commType;
      
      // Fix direction case for sender/recipient
      const data = {
        direction,
        type: actualCommType,
        subject,
        content: details,
        sender: direction === 'Incoming' ? fromTo : null,
        recipient: direction === 'Outgoing' ? fromTo : null,
        confidential,
        // Optionally include user info for frontend display (not sent to backend)
        fullName: `${currentUser.firstName} ${currentUser.lastName}`.trim()
      };
      
      // Log token for debugging
      console.log('Token:', localStorage.getItem('token'));
      
      // Send data to backend
      const response = await apiRequest(API_ENDPOINTS.LOGS, {
        method: 'POST',
        body: JSON.stringify(data)
      });
      
      if (response.success) {
        // Add the new log to the logs array, ensuring fullName is set for immediate UI update
        logs = [{ ...response.data, fullName: `${currentUser.firstName} ${currentUser.lastName}`.trim() }, ...logs];
        closeModal();
      }
    } catch (error: any) {
      error = error.message;
    }
  }

  function sortBy(column: string) {
    if (sortColumn === column) {
      // Toggle direction
      sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn = column;
      sortDirection = 'asc';
    }
    logs = [...logs].sort((a, b) => {
      let aValue = a[column as keyof typeof a];
      let bValue = b[column as keyof typeof b];
      if (typeof aValue === 'string' && typeof bValue === 'string') {
        aValue = aValue.toLowerCase();
        bValue = bValue.toLowerCase();
      }
      if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });
  }

  $: filteredLogs = logs.filter(log => {
    const matchesType = selectedType === 'All Types' || log.type === selectedType;
    const matchesDirection = selectedDirection === 'All Directions' || log.direction === selectedDirection;
    const matchesSearch = searchQuery === '' ||
      (log.sender && log.sender.toLowerCase().includes(searchQuery.toLowerCase())) ||
      (log.recipient && log.recipient.toLowerCase().includes(searchQuery.toLowerCase())) ||
      log.timestamp.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.type.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.direction.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.subject.toLowerCase().includes(searchQuery.toLowerCase());

    // Date and time filtering
    let matchesDate = true;
    let matchesTime = true;
    if (selectedDate) {
      matchesDate = log.timestamp.startsWith(selectedDate);
    }
    if (selectedTime) {
      const logTime = log.timestamp.split(' ')[1].slice(0,5); // "00:43"
      matchesTime = logTime === selectedTime;
    }

    return matchesType && matchesDirection && matchesSearch && matchesDate && matchesTime;
  });

  async function fetchLogs() {
    try {
      const response = await apiRequest(API_ENDPOINTS.LOGS, {
        method: 'GET'
      });
      
      if (response.success) {
        logs = response.data;
      }
    } catch (error) {
      console.error('Error fetching logs:', error);
    }
  }
  
  onMount(() => {
    // Try to get user info from localStorage
    const userStr = localStorage.getItem('user');
    if (userStr) {
      try {
        const userObj = JSON.parse(userStr);
        currentUser = {
          id: userObj.id || '',
          username: userObj.username || '',
          firstName: userObj.firstName || '',
          lastName: userObj.lastName || ''
        };
      } catch {}
    }
    fetchLogs();
  });

  // Helper to format date/time for modal
  function formatDateTime(datetimeStr: string) {
    try {
      const dt = new Date(datetimeStr);
      if (isNaN(dt.getTime())) return datetimeStr;
      return dt.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
    } catch {
      return datetimeStr;
    }
  }
</script>
<div class="flex h-screen">
	<UserSidebar />
	<div class="flex-1 flex flex-col overflow-hidden">
		<UserHeader />

<div class="flex">
  <div class="flex-1 p-6 bg-gray-100">
    <!-- Page specific content will go here -->
    <!-- Header Section -->
    <div class="flex items-center justify-between bg-white p-4 rounded-lg shadow mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Log Management</h1>
      <div class="flex items-center text-gray-600 text-sm">
        <!-- LogSystem with Icon -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
          <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
          LogSystem
        </span>
        <span class="mx-1">/</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Log Management</span>
      </div>
    </div>
  </div>
</div>

    <!-- Filters and Search (Flowbite style) -->
    <div class="flex items-center mb-6 gap-3">
      <select id="type" bind:value={selectedType} class="form-select block w-40 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
        <option>All Types</option>
        <option>Email</option>
        <option>Fax</option>
        <option>Call</option>
      </select>
      <select id="direction" bind:value={selectedDirection} class="form-select block w-48 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
        <option>All Directions</option>
        <option>Incoming</option>
        <option>Outgoing</option>
      </select>
      <div class="flex-1"></div>
      <div class="relative w-64 -z-1">
        <input type="text" id="search" bind:value={searchQuery} class="form-input block w-full px-4 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500" placeholder="Search..." />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <input
        type="date"
        bind:value={selectedDate}
        class="form-input block w-40 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
      />
      <input
        type="time"
        bind:value={selectedTime}
        class="form-input block w-32 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500"
      />
    </div>

    <!-- Table (Flowbite style) -->
    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 bg-white">
      <table class="min-w-full text-sm text-left text-gray-700 align-middle">
        <thead class="text-xs uppercase bg-gradient-to-r from-blue-50 to-blue-100 text-gray-700 font-semibold tracking-wider border-b border-gray-200">
          <tr>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('fullName')}>
              Full Name
              {#if sortColumn === 'fullName'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('timestamp')}>
              Timestamp
              {#if sortColumn === 'timestamp'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('type')}>
              Type
              {#if sortColumn === 'type'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('direction')}>
              Direction
              {#if sortColumn === 'direction'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('fromTo')}>
              From / To
              {#if sortColumn === 'fromTo'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('summary')}>
              Summary
              {#if sortColumn === 'summary'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-4 whitespace-nowrap">Actions</th>
            <th scope="col" class="px-6 py-4 whitespace-nowrap">Report</th>
          </tr>
        </thead>
        <tbody>
          {#each filteredLogs as log, i}
            <tr class="border-b border-gray-100 transition-colors duration-150 {i % 2 === 1 ? 'bg-blue-50' : 'bg-white'} hover:bg-blue-100">
              <td class="px-6 py-4 align-middle whitespace-nowrap">{log.fullName || '--'}</td>
              <td class="px-6 py-4 font-mono align-middle whitespace-nowrap">{log.timestamp}</td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">{log.type}</td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">{log.direction}</td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">{log.sender || log.recipient || '--'}</td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">{log.subject}</td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">
                <a href="#" class="inline-block font-medium text-white bg-blue-500 hover:bg-blue-600 rounded px-3 py-1 mr-2 transition shadow-sm" on:click|preventDefault={() => openViewModal(log)}>View</a>
                <a href="#" class="inline-block font-medium text-blue-600 bg-blue-100 hover:bg-blue-200 rounded px-3 py-1 transition shadow-sm">Delete</a>
              </td>
              <td class="px-6 py-4 align-middle whitespace-nowrap">
                <button type="button" class="text-white bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 font-semibold rounded-full text-xs px-5 py-1.5 text-center shadow transition">Generate</button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>

    <!-- Floating Add Button -->
    <div class="fixed bottom-8 right-8 z-50 group">
      <button
        type="button"
        class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white rounded-full shadow-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300"
        aria-label="Add Log"
        on:click={openModal}
      >
        <!-- Heroicon: Plus -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
      </button>
      <span class="absolute right-0 bottom-14 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs rounded py-1 px-3 pointer-events-none transition-opacity duration-200 shadow-lg">
        Add Log
      </span>
    </div>
    
    {#if showModal}
    <div 
      class="fixed inset-0 z-[110] flex items-center justify-center overflow-y-auto p-4 backdrop-blur-sm" 
      on:click|self={closeModal}
      role="dialog" 
      aria-modal="true" 
      aria-labelledby="modal-new-log-title" 
      aria-describedby="modal-new-log-desc"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-8 max-h-[90vh] overflow-y-auto">
        <h2 id="modal-new-log-title" class="text-xl font-bold text-center text-blue-800 mb-6">New Log Entry</h2>
        
        <form id="new-log-form" on:submit={handleSubmit} class="space-y-4">
          <div>
            <label for="comm-type-select" class="block font-semibold text-gray-700">Communication Type</label>
            <select 
              id="comm-type-select" 
              name="comm-type" 
              required 
              bind:value={commType}
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            >
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
          
          {#if commType === 'other'}
          <div>
            <label for="other-type-input" class="block font-semibold text-gray-700">Please specify the other type</label>
            <input 
              type="text" 
              id="other-type-input" 
              name="other-type" 
              bind:value={otherType}
              placeholder="Type of communication" 
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
          {/if}
          
          <div>
            <label for="direction-select" class="block font-semibold text-gray-700">Direction</label>
            <select 
              id="direction-select" 
              name="direction" 
              bind:value={direction}
              required
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="" disabled selected>Select direction...</option>
              <option value="Incoming">Incoming</option>
              <option value="Outgoing">Outgoing</option>
            </select>
          </div>
          
          {#if commType === 'email'}
          <div>
            <label for="email-input" class="block font-semibold text-gray-700">Email Address</label>
            <input 
              type="email" 
              id="email-input" 
              name="email-address" 
              bind:value={emailAddress}
              placeholder="example@example.com" 
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
          {/if}
          
          {#if commType === 'call' || commType === 'text-message'}
          <div>
            <label for="phone-input" class="block font-semibold text-gray-700">Phone Number</label>
            <input 
              type="tel" 
              id="phone-input" 
              name="phone-number" 
              bind:value={phoneNumber}
              placeholder="+1 (555) 123-4567" 
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
          {/if}
          
          <div class="relative">
            <label for="fromto-input" class="block font-semibold text-gray-700">From / To</label>
            <input 
              type="text" 
              id="fromto-input" 
              name="fromto" 
              bind:value={fromTo}
              on:input={(e) => updateFilteredContacts(e.currentTarget.value)}
              autocomplete="off" 
              aria-autocomplete="list" 
              aria-haspopup="listbox" 
              aria-controls="fromto-autocomplete"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required 
            />
            
            {#if showAutocomplete}
              <div 
                id="fromto-autocomplete" 
                class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg max-h-40 overflow-y-auto border border-gray-300" 
                role="listbox"
              >
                {#each filteredContacts as contact}
                  <div 
                    class="px-3 py-2 cursor-pointer hover:bg-blue-100" 
                    role="option" 
                    on:click={() => selectContact(contact)}
                  >
                    {contact}
                  </div>
                {/each}
              </div>
            {/if}
          </div>
          
          <div>
            <label for="subject-input" class="block font-semibold text-gray-700">Subject / Summary</label>
            <input 
              type="text" 
              id="subject-input" 
              name="subject" 
              bind:value={subject}
              placeholder="e.g. Policy Changes" 
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required 
            />
          </div>
          
          <div>
            <label for="details-input" class="block font-semibold text-gray-700">Details / Notes</label>
            <textarea 
              id="details-input" 
              name="details" 
              bind:value={details}
              placeholder="Add more information about the communication" 
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 min-h-[80px] resize-y"
              required
            ></textarea>
          </div>
          
          <div>
            <label for="datetime-input" class="block font-semibold text-gray-700">Date / Time</label>
            <input 
              type="datetime-local" 
              id="datetime-input" 
              name="datetime" 
              bind:value={dateTime}
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              required 
            />
          </div>
          
          <div>
            <label for="attachments-input" class="block font-semibold text-gray-700">Attachments</label>
            <input 
              type="file" 
              id="attachments-input" 
              name="attachments" 
              multiple
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
            />
          </div>
          
          <div class="flex items-center">
            <input 
              type="checkbox" 
              id="confidential-input" 
              name="confidential" 
              bind:checked={confidential}
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
            />
            <label for="confidential-input" class="ml-2 block text-gray-700">Confidential</label>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button" 
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
              on:click={closeModal}
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
    {/if}

    {#if showViewModal && selectedLog}
      <div class="modal active" style="position:fixed;z-index:120;left:0;top:0;width:100vw;height:100vh;background-color:rgba(33,33,33,0.6);display:flex;align-items:center;justify-content:center;">
        <div class="modal-content" style="background:white;border-radius:8px;padding:2.5rem 3rem;box-shadow:0 6px 20px rgba(0,0,0,0.15);position:relative;width:500px;max-height:90vh;overflow-y:auto;">
          <h2 style="text-align:center;color:#0d47a1;font-size:1.8rem;">View Log Entry</h2>
          <div>
            <div class="field-label">Communication Type</div>
            <input class="field-value" type="text" value={selectedLog.type} readonly style="width:100%;margin-bottom:10px;" />
          </div>
          <div>
            <div class="field-label">Direction</div>
            <input class="field-value" type="text" value={selectedLog.direction} readonly style="width:100%;margin-bottom:10px;" />
          </div>
          <div>
            <div class="field-label">From / To</div>
            <input class="field-value" type="text" value={selectedLog.sender || selectedLog.recipient || '--'} readonly style="width:100%;margin-bottom:10px;" />
          </div>
          <div>
            <div class="field-label">Subject / Summary</div>
            <input class="field-value" type="text" value={selectedLog.subject} readonly style="width:100%;margin-bottom:10px;" />
          </div>
<div>
  <div class="field-label">Details / Notes</div>
  <textarea class="field-value" readonly style="width:100%;margin-bottom:10px;" rows="4">
    {selectedLog.content}
  </textarea>
</div>

          <div>
            <div class="field-label">Date / Time</div>
            <input class="field-value" type="text" value={formatDateTime(selectedLog.timestamp)} readonly style="width:100%;margin-bottom:10px;" />
          </div>
          <div>
            <div class="field-label">Attachments</div>
            <ul class="attachments-list">
              <li>No attachments</li>
            </ul>
          </div>
          <button class="close-btn" style="margin-top:2.5rem;background-color:#1976d2;color:white;border:none;padding:12px 28px;border-radius:6px;font-weight:700;font-size:1rem;cursor:pointer;transition:background-color 0.3s;display:block;margin-left:auto;" on:click={closeViewModal}>Close</button>
        </div>
      </div>
    {/if}
  </div>
</div>

<style>
  /* Add specific modal styles here if needed */
  :global(body.modal-open) {
    overflow: hidden;
  }
</style>
