<script lang="ts">
  // Sample data for demonstration
  let logs = [
    {
      fullName: "John Doe",
      timestamp: "2025-05-17 00:43",
      type: "Email",
      direction: "Incoming",
      fromTo: "user@mail",
      summary: "Blabla"
    },
    {
      fullName: "Jane Smith",
      timestamp: "2025-05-17 00:43",
      type: "Fax",
      direction: "Outgoing",
      fromTo: "user@mail",
      summary: "Blabla"
    },
    {
      fullName: "Alice Brown",
      timestamp: "2025-05-17 00:43",
      type: "Call",
      direction: "Outgoing",
      fromTo: "user@mail",
      summary: "Blabla"
    }
  ];

  let sortColumn: string = '';
  let sortDirection: 'asc' | 'desc' = 'asc';
  let selectedType: string = 'All Types';
  let selectedDirection: string = 'All Directions';
  let searchQuery: string = '';

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
      log.timestamp.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.type.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.direction.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.fromTo.toLowerCase().includes(searchQuery.toLowerCase()) ||
      log.summary.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesType && matchesDirection && matchesSearch;
  });
</script>

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
    </div>

    <!-- Table (Flowbite style) -->
    <div class="overflow-x-auto rounded-lg shadow">
      <table class="w-full text-sm text-left text-gray-700 bg-white">
        <thead class="text-xs uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('fullName')}>
              Full Name
              {#if sortColumn === 'fullName'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('timestamp')}>
              Timestamp
              {#if sortColumn === 'timestamp'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('type')}>
              Type
              {#if sortColumn === 'type'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('direction')}>
              Direction
              {#if sortColumn === 'direction'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('fromTo')}>
              From / To
              {#if sortColumn === 'fromTo'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3 cursor-pointer select-none" on:click={() => sortBy('summary')}>
              Summary
              {#if sortColumn === 'summary'}
                {sortDirection === 'asc' ? ' ▲' : ' ▼'}
              {/if}
            </th>
            <th scope="col" class="px-6 py-3">Actions</th>
            <th scope="col" class="px-6 py-3">Report</th>
          </tr>
        </thead>
        <tbody>
          {#each filteredLogs as log, i}
            <tr class="border-b hover:bg-blue-50 {i % 2 === 1 ? 'bg-gray-50' : ''}">
              <td class="px-6 py-4">{log.fullName}</td>
              <td class="px-6 py-4 font-mono">{log.timestamp}</td>
              <td class="px-6 py-4">{log.type}</td>
              <td class="px-6 py-4">{log.direction}</td>
              <td class="px-6 py-4">{log.fromTo}</td>
              <td class="px-6 py-4">{log.summary}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <a href="#" class="font-medium text-blue-600 hover:underline mr-3">View</a>
                <a href="#" class="font-medium text-blue-600 hover:underline">Export</a>
              </td>
              <td class="px-6 py-4">
                <button type="button" class="text-white bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 font-medium rounded-full text-xs px-5 py-1.5 text-center shadow transition">Generate</button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>

    <!-- Floating Add Button -->
    <button
      type="button"
      class="fixed bottom-8 right-8 z-50 flex items-center justify-center w-14 h-14 bg-gray-800 text-white rounded-full shadow-lg hover:bg-gray-700 transition text-3xl"
      aria-label="Add Log"
    >
      +
    </button>
  </div>
</div>
