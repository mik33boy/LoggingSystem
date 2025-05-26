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
    console.log('Add Log button clicked');
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

  let showReportModal = false;
  let reportLog: any = null;
  let reportFormat: 'pdf' | 'excel' = 'pdf';
  let reportRange: 'yearly' | 'monthly' | 'weekly' = 'monthly';

  function openReportModal(log: any) {
    reportLog = log;
    showReportModal = true;
  }

  function closeReportModal() {
    showReportModal = false;
    reportLog = null;
  }

  async function generateReport(log: any, format: string = 'pdf', range: string = 'monthly') {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch(`${API_ENDPOINTS.LOGS}?action=report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ logId: log.id, format, range })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to generate report');
        }

        // Get the blob from the response
        const blob = await response.blob();
        
        // Create a URL for the blob
        const url = window.URL.createObjectURL(blob);
        
        // Create a temporary link element
        const a = document.createElement('a');
        a.href = url;
        a.download = `log-report-${log.id}.${format === 'excel' ? 'csv' : 'pdf'}`;
        
        // Append to body, click, and remove
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        // Clean up the URL
        window.URL.revokeObjectURL(url);
        
        // Close the modal
        closeReportModal();
    } catch (err: any) {
        alert('Error generating report: ' + (err?.message || 'Unknown error occurred'));
    }
  }

  async function deleteLog(logId: number) {
    if (!confirm('Are you sure you want to delete this log?')) return;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_ENDPOINTS.LOGS}?id=${logId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      if (!response.ok) throw new Error('Failed to delete log');
      logs = logs.filter(log => log.id !== logId);
    } catch (err) {
      alert('Error deleting log: ' + err.message);
    }
  }

  // Add a new variable for summary report modal
  let showSummaryReportModal = false;
  let summaryReportFormat: 'pdf' | 'excel' = 'pdf';
  let summaryReportRange: 'weekly' | 'monthly' | 'yearly' = 'monthly';

  // Add a new function for summary report generation
  async function generateSummaryReport(format: string = 'pdf', range: string = 'monthly') {
    try {
      const token = localStorage.getItem('token');
      if (!token) throw new Error('No authentication token found');
      const response = await fetch(`${API_ENDPOINTS.LOGS}?action=report-summary`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ format, range })
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to generate summary report');
      }
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `log-summary-report.${format === 'excel' ? 'csv' : 'pdf'}`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (err: any) {
      alert('Error generating summary report: ' + (err?.message || 'Unknown error occurred'));
    }
  }
</script>
<div class="flex h-screen bg-gray-50">
  <div class="flex-1 flex flex-col overflow-hidden items-center justify-start">
    <div class="w-full px-2 py-8">
      <!-- Header Section -->
      <div class="flex items-center justify-between bg-white p-6 rounded-2xl shadow-lg mb-8 border border-gray-200 w-full">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Log Management</h1>
        <div class="flex items-center text-gray-600 text-base">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-base font-medium bg-gray-200 text-gray-700">
            <svg class="w-5 h-5 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
            LogSystem
          </span>
          <span class="mx-2">/</span>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold bg-gray-100 text-gray-800">Log Management</span>
        </div>
      </div>
      <!-- Filters and Search -->
      <div class="flex flex-wrap items-center gap-4 mb-8 bg-white p-4 rounded-xl shadow border border-gray-100 w-full">
        <select id="type" bind:value={selectedType} class="form-select w-40 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
          <option>All Types</option>
          <option>Email</option>
          <option>Fax</option>
          <option>Call</option>
        </select>
        <select id="direction" bind:value={selectedDirection} class="form-select w-48 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
          <option>All Directions</option>
          <option>Incoming</option>
          <option>Outgoing</option>
        </select>
        <input type="date" bind:value={selectedDate} class="form-input w-40 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500" />
        <input type="time" bind:value={selectedTime} class="form-input w-32 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500" />
        <div class="flex-1 min-w-[180px]"></div>
        <div class="relative w-64">
          <input type="text" id="search" bind:value={searchQuery} class="form-input w-full px-4 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500" placeholder="Search..." />
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <button type="button" class="ml-2 px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white rounded-lg shadow text-sm font-semibold transition" on:click={() => showSummaryReportModal = true}>
          Generate Summary
        </button>
      </div>
      <!-- Table -->
      <div class="overflow-x-auto rounded-2xl shadow-lg border border-gray-200 bg-white w-full">
        <table class="min-w-full text-sm text-left text-gray-700 align-middle">
          <thead class="text-xs uppercase bg-gradient-to-r from-blue-100 to-blue-200 text-gray-800 font-bold tracking-wider border-b border-gray-200">
            <tr>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('fullName')}>Full Name {#if sortColumn === 'fullName'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('timestamp')}>Timestamp {#if sortColumn === 'timestamp'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('type')}>Type {#if sortColumn === 'type'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('direction')}>Direction {#if sortColumn === 'direction'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('fromTo')}>From / To {#if sortColumn === 'fromTo'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 cursor-pointer select-none whitespace-nowrap" on:click={() => sortBy('summary')}>Summary {#if sortColumn === 'summary'}{sortDirection === 'asc' ? ' ▲' : ' ▼'}{/if}</th>
              <th scope="col" class="px-6 py-4 whitespace-nowrap">Actions</th>
              <th scope="col" class="px-6 py-4 whitespace-nowrap">Report</th>
            </tr>
          </thead>
          <tbody>
            {#each filteredLogs as log, i}
              <tr class="border-b border-gray-100 transition-colors duration-150 {i % 2 === 1 ? 'bg-blue-50' : 'bg-white'} hover:bg-blue-100/60">
                <td class="px-6 py-4 align-middle whitespace-nowrap">{log.fullName || '--'}</td>
                <td class="px-6 py-4 font-mono align-middle whitespace-nowrap">{log.timestamp}</td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">{log.type}</td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">{log.direction}</td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">{log.sender || log.recipient || '--'}</td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">{log.subject}</td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">
                  <a href="#" class="inline-block font-medium text-white bg-blue-500 hover:bg-blue-600 rounded px-3 py-1 mr-2 transition shadow-sm" on:click|preventDefault={() => openViewModal(log)}>View</a>
                  <a href="#" class="inline-block font-medium text-blue-600 bg-blue-100 hover:bg-blue-200 rounded px-3 py-1 transition shadow-sm" on:click|preventDefault={() => deleteLog(log.id)}>Delete</a>
                </td>
                <td class="px-6 py-4 align-middle whitespace-nowrap">
                  <button type="button" class="text-white bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 font-semibold rounded-full text-xs px-5 py-1.5 text-center shadow transition" on:click={() => openReportModal(log)}>Generate</button>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
      <!-- Floating Add Button -->
      <div class="fixed bottom-8 right-8 z-[9999] group pointer-events-auto">
        <button type="button" class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white rounded-full shadow-2xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300 border-4 border-white pointer-events-auto" aria-label="Add Log" on:click={openModal}>
          <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
        </button>
        <span class="absolute right-0 bottom-16 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs rounded py-1 px-3 pointer-events-none transition-opacity duration-200 shadow-lg">
          Add Log
        </span>
      </div>



      <!-- Add Log Modal -->
      {#if showModal}
        <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-40">
          <div class="modal-content bg-white p-8 rounded-2xl w-full max-w-lg relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl font-bold" on:click={closeModal}>&times;</button>
            <h2 class="text-xl font-bold mb-4">Add Log Entry</h2>
            <form on:submit|preventDefault={handleSubmit}>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Type</label>
                <select bind:value={commType} class="form-select w-full px-3 py-2 border rounded">
                  <option value="">Select Type</option>
                  <option value="Email">Email</option>
                  <option value="Fax">Fax</option>
                  <option value="Call">Call</option>
                  <option value="other">Other</option>
                </select>
                {#if commType === 'other'}
                  <input type="text" bind:value={otherType} class="form-input w-full mt-2 px-3 py-2 border rounded" placeholder="Specify other type" />
                {/if}
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Direction</label>
                <select bind:value={direction} class="form-select w-full px-3 py-2 border rounded">
                  <option value="">Select Direction</option>
                  <option value="Incoming">Incoming</option>
                  <option value="Outgoing">Outgoing</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">From / To</label>
                <input type="text" bind:value={fromTo} class="form-input w-full px-3 py-2 border rounded" placeholder="Email or phone" on:input={(e) => updateFilteredContacts(e.target.value)} />
                {#if showAutocomplete}
                  <ul class="absolute bg-white border rounded shadow mt-1 w-full z-10">
                    {#each filteredContacts as contact}
                      <li class="px-3 py-1 hover:bg-blue-100 cursor-pointer" on:click={() => selectContact(contact)}>{contact}</li>
                    {/each}
                  </ul>
                {/if}
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Subject</label>
                <input type="text" bind:value={subject} class="form-input w-full px-3 py-2 border rounded" />
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Details</label>
                <textarea bind:value={details} class="form-input w-full px-3 py-2 border rounded" rows="3"></textarea>
              </div>
              <div class="mb-3 flex items-center gap-4">
                <label class="block text-sm font-medium">Confidential</label>
                <input type="checkbox" bind:checked={confidential} />
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Date & Time</label>
                <input type="datetime-local" bind:value={dateTime} class="form-input w-full px-3 py-2 border rounded" />
              </div>
              <div class="flex justify-end mt-6">
                <button type="button" class="mr-3 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700" on:click={closeModal}>Cancel</button>
                <button type="submit" class="px-5 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold">Add Log</button>
              </div>
            </form>
          </div>
        </div>
      {/if}
    </div>
  </div>
</div>

