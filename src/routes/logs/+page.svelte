<script lang="ts">
  import Sidebar from '../../components/sidebar.svelte';
  import NewLogModal from '../../components/NewLogModal.svelte';
  import ViewLogModal, { openModal as openViewModal } from '../../components/ViewLogModal.svelte';

  interface LogEntry {
    timestamp: string;
    type: string;
    direction: string;
    contact: string;
    summary: string;
  }

  let search = '';
  let selectedType = 'All Types';
  let selectedDirection = 'All Directions';
  let showModal = false;

  let logs: LogEntry[] = [
    { timestamp: '2025-05-17 00:43', type: 'Email', direction: 'Incoming', contact: 'user@mail', summary: 'Blabla' },
    { timestamp: '2025-05-17 00:43', type: 'Email', direction: 'Incoming', contact: 'user@mail', summary: 'Blabla' },
    { timestamp: '2025-05-17 00:43', type: 'Email', direction: 'Incoming', contact: 'user@mail', summary: 'Blabla' }
  ];

  const filteredLogs = () => logs.filter(log =>
    (selectedType === 'All Types' || log.type === selectedType) &&
    (selectedDirection === 'All Directions' || log.direction === selectedDirection) &&
    (search === '' || log.contact.toLowerCase().includes(search.toLowerCase()))
  );

  function openLogEntryModal() {
    showModal = true;
  }

  function handleSubmit(event: CustomEvent<any>) {
    const data = event.detail;
    console.log('Form submitted:', data);
    
    // Add the new log to our local array
    logs = [...logs, {
      timestamp: new Date(data.datetime).toLocaleString(),
      type: data['comm-type'] || 'Other',
      direction: data.direction || 'Outgoing',
      contact: data.fromto || 'N/A',
      summary: data.subject
    }];
  }

  function openSampleEntry() {
    const sample = {
      commType: 'Memo',
      direction: 'Outgoing',
      fromto: 'John Doe (HR)',
      subject: 'Policy Changes',
      details: 'Confidential memo outlining employee policy changes.',
      datetime: '2023-10-25T10:15:00',
      attachments: ['policy.pdf']
    };
    openViewModal(sample);
  }
</script>


<div class="flex">
  <Sidebar active="grid" role="admin" />

  <div class="flex-1 p-6 bg-gray-50 min-h-screen relative">
    <!-- Top bar -->
    <div class="flex justify-between items-center mb-6">
      <div class="bg-white rounded-full px-6 py-2 text-center shadow w-1/3">DATE</div>
      <div class="bg-white rounded-full px-6 py-2 text-center shadow w-1/3">00 : 00 : 00</div>
      <div class="flex items-center space-x-3">
        <div class="bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center">🔔</div>
        <div class="text-right">
          <div class="font-bold">User Personnel</div>
          <div class="text-sm text-gray-500">@User</div>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="bg-white px-4 py-2 rounded shadow mb-4 flex justify-between items-center">
      <span class="text-xl font-semibold">Log Management</span>
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-500">LogSystem /</span>
        <button class="bg-blue-100 text-blue-600 px-4 py-1 rounded">Manage Logs</button>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <div class="flex justify-between items-center mb-4">
        <div class="flex space-x-4">
          <select bind:value={selectedType} class="px-4 py-2 border rounded">
            <option>All Types</option>
            <option>Email</option>
            <option>Call</option>
          </select>
          <select bind:value={selectedDirection} class="px-4 py-2 border rounded">
            <option>All Directions</option>
            <option>Incoming</option>
            <option>Outgoing</option>
          </select>
        </div>
        <input type="text" placeholder="Search..." bind:value={search}
               class="px-4 py-2 border rounded w-1/4" />
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded border">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-100 text-gray-700 font-semibold">
            <tr>
              <th class="px-4 py-2">TIMESTAMP</th>
              <th class="px-4 py-2">TYPE</th>
              <th class="px-4 py-2">DIRECTION</th>
              <th class="px-4 py-2">FROM/TO</th>
              <th class="px-4 py-2">SUMMARY</th>
              <th class="px-4 py-2">ACTIONS</th>
              <th class="px-4 py-2">REPORT</th>
            </tr>
          </thead>
          <tbody>
            {#each filteredLogs() as log, index}
              <tr class={index === 2 ? 'bg-gray-200' : 'hover:bg-gray-100'}>
                <td class="px-4 py-2">{log.timestamp}</td>
                <td class="px-4 py-2">{log.type}</td>
                <td class="px-4 py-2">{log.direction}</td>
                <td class="px-4 py-2">{log.contact}</td>
                <td class="px-4 py-2">{log.summary}</td>
                <td class="px-4 py-2 text-blue-500 space-x-2">
                  <button class="hover:underline" on:click={openSampleEntry}>View</button>
                  <ViewLogModal />
                  <button class="hover:underline">Export</button>
                </td>
                <td class="px-4 py-2">
                  <button class="bg-gray-900 text-white px-3 py-1 rounded text-sm">Generate</button>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>

    <!-- Floating Add Button -->
    <button 
      on:click={openLogEntryModal} 
      class="absolute bottom-4 right-12 bg-gray-900 text-white w-20 h-20 rounded-full text-4xl flex items-center justify-center shadow-lg hover:bg-gray-800 transition"
    >
      +
    </button>
    
    <!-- Modal Component -->
    <NewLogModal bind:showModal on:submit={handleSubmit} on:cancel={() => console.log('Modal closed')} />
  </div>
</div>
