<script lang="ts">
  // Sample data for demonstration
  let logs = [
    {
      timestamp: "2025-05-17 00:43",
      type: "Email",
      direction: "Incoming",
      fromTo: "user@mail",
      summary: "Blabla"
    },
    {
      timestamp: "2025-05-17 00:43",
      type: "Email",
      direction: "Incoming",
      fromTo: "user@mail",
      summary: "Blabla"
    },
    {
      timestamp: "2025-05-17 00:43",
      type: "Email",
      direction: "Incoming",
      fromTo: "user@mail",
      summary: "Blabla"
    }
  ];
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
      <select id="type" class="form-select block w-40 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
        <option>All Types</option>
        <!-- Add more options as needed -->
      </select>
      <select id="direction" class="form-select block w-48 px-3 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
        <option>All Directions</option>
        <!-- Add more options as needed -->
      </select>
      <div class="flex-1"></div>
      <div class="relative w-64">
        <input type="text" id="search" class="form-input block w-full px-4 py-2 text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500" placeholder="Search..." />
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
            <th scope="col" class="px-6 py-3">Timestamp</th>
            <th scope="col" class="px-6 py-3">Type</th>
            <th scope="col" class="px-6 py-3">Direction</th>
            <th scope="col" class="px-6 py-3">From / To</th>
            <th scope="col" class="px-6 py-3">Summary</th>
            <th scope="col" class="px-6 py-3">Actions</th>
            <th scope="col" class="px-6 py-3">Report</th>
          </tr>
        </thead>
        <tbody>
          {#each logs as log, i}
            <tr class="border-b hover:bg-blue-50 {i % 2 === 1 ? 'bg-gray-50' : ''}">
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
  </div>
</div>
