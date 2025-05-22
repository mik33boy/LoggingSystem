<script lang="ts">

</script>

<div class="flex">
  <div class="flex-1 p-6 bg-gray-100">
    <!-- Page specific content will go here -->
    <!-- Header Section -->
    <div class="flex items-center justify-between bg-white p-4 rounded-lg shadow mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Hello, Carmi!</h1>
      <div class="flex items-center text-gray-600 text-sm">
        <!-- LogSystem with Icon -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
          <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
          LogSystem
        </span>
        <span class="mx-1">/</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Dashboard</span>
      </div>
    </div>

    <!-- Top Section: Notes and Notifications -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Notes Card -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2 text-gray-800">Notes</h3>
        <p class="text-gray-700">notes for the user he can put notes for himself</p>
      </div>

      <!-- Notifications Card -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-2 text-gray-800">Notifications and Alerts</h3>
        <p class="text-gray-700">Highlight important notifications (e.g., unread messages, follow-up reminders).</p>
      </div>
    </div>

    <!-- Bottom Section: Recent Activities and Priority Tracking -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
       <!-- Recent Activities Section (takes more space) -->
      <div class="bg-white p-4 rounded-lg shadow md:col-span-2">
       <h3 class="text-lg font-semibold mb-2 text-gray-800">Recent Activity Log</h3>
       <ul class="list-disc list-inside text-gray-700">
            <li>Last 10-20 Communications (With timestamps & brief details)</li>
            <li>Quick Actions (Mark as resolved, forward, etc.)</li>
         </ul>
       <!-- Table goes here -->
       <p class="text-gray-700">Recent Activities Table goes here</p>
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
