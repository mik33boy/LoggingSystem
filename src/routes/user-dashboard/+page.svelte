<script lang="ts">
  // Dummy data for demonstration
  const settlements = 10000;
  const pending = 500;
  const logScanEntries = 12340;
  const donutData = [12.5, 25, 62.5]; // Pending, Forward, Resolved
  const donutColors = ["#3B82F6", "#F59E42", "#10B981"];

  // For donut chart
  const donutTotal = donutData.reduce((a, b) => a + b, 0);
  let donutSegments = [];
  let start = 0;
  for (let i = 0; i < donutData.length; i++) {
    const value = donutData[i];
    const dash = (value / donutTotal) * 100;
    donutSegments.push({
      color: donutColors[i],
      dash,
      offset: 25 - start
    });
    start += dash;
  }
</script>

<div class="flex">
  <div class="flex-1 p-6 bg-gray-100">
    <!-- Header Section -->
    <div class="flex items-center justify-between bg-white p-4 rounded-lg shadow mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Hello, Carmi!</h1>
      <div class="flex items-center text-gray-600 text-sm">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
          <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
          LogSystem
        </span>
        <span class="mx-1">/</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Dashboard</span>
      </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <!-- Settlements Card -->
      <div class="bg-white p-4 rounded-lg shadow flex flex-col items-center justify-center">
        <div class="flex items-center mb-2">
          <svg class="w-8 h-8 text-gray-500 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
          <span class="text-3xl font-bold">{settlements.toLocaleString()}</span>
        </div>
        <div class="text-xs text-gray-500 font-semibold">SETTLEMENTS</div>
        <div class="text-xs text-gray-400">Completed Transactions</div>
      </div>
      <!-- Pending Card -->
      <div class="bg-gray-900 p-4 rounded-lg shadow flex flex-col items-center justify-center">
        <div class="flex items-center mb-2">
          <svg class="w-8 h-8 text-white mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17v-6a5 5 0 00-10 0v6"/><rect x="5" y="17" width="14" height="2" rx="1" fill="currentColor"/></svg>
          <span class="text-3xl font-bold text-white">{pending}</span>
        </div>
        <div class="text-xs text-gray-300 font-semibold">PENDING</div>
        <div class="text-xs text-gray-400">Ongoing Transactions</div>
      </div>
      <!-- Notifications Card -->
      <div class="bg-gray-200 p-4 rounded-lg shadow flex flex-col items-center justify-center">
        <div class="flex items-center mb-2">
          <svg class="w-6 h-6 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="font-semibold text-gray-700">Notifications</span>
        </div>
        <div class="text-sm text-gray-700 text-center">Log scan completed — <span class="font-bold">{logScanEntries.toLocaleString()}</span> entries reviewed.</div>
      </div>
      <!-- Donut Chart Card -->
      <div class="bg-white p-4 rounded-lg shadow flex flex-col items-center justify-center">
        <svg width="80" height="80" viewBox="0 0 42 42" class="mb-2">
          {#each donutSegments as seg}
            <circle
              r="16"
              cx="21"
              cy="21"
              fill="transparent"
              stroke={seg.color}
              stroke-width="6"
              stroke-dasharray={`${seg.dash} ${100 - seg.dash}`}
              stroke-dashoffset={seg.offset}
              style="transform: rotate(-90deg); transform-origin: 50% 50%;"
            />
          {/each}
        </svg>
        <div class="text-xs text-gray-700 text-center">
          <div>12.5% Pending</div>
          <div>25% Forward</div>
          <div>62.5% Resolved</div>
        </div>
      </div>
    </div>

    <!-- Bottom Section: Recent Activities and Priority Tracking -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Recent Activities Section (table) -->
      <div class="bg-white p-4 rounded-lg shadow md:col-span-2">
        <h3 class="text-lg font-semibold mb-2 text-gray-800">RECENT ACTIVITIES</h3>
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2 px-2">TIMESTAMP</th>
              <th class="py-2 px-2">FROM/ TO</th>
              <th class="py-2 px-2">SUMMARY</th>
              <th class="py-2 px-2">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <!-- Example row, replace with dynamic data as needed -->
            <tr class="border-b">
              <td class="py-2 px-2">--</td>
              <td class="py-2 px-2">--</td>
              <td class="py-2 px-2">--</td>
              <td class="py-2 px-2">--</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Priority Tracking -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2 text-gray-800">Priority & Urgency Tracking</h3>
        <ul class="list-disc list-inside text-gray-700">
          <li>High-Priority Communications (Flagged/Urgent)</li>
          <li>Missed/Overdue Responses (Needs follow-up)</li>
          <li>SLA Compliance (If applicable)</li>
        </ul>
      </div>
    </div>
  </div>
</div>
