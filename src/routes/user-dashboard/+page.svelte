<script lang="ts">

</script>

<div class="flex">
  <!-- Sidebar will go here  <UserSidebar />

  <!-- Main content area -->
  <div class="flex-1 p-6">
    <!-- Header will go here -->

    
    <!-- Page specific content will go here -->
    <h1 class="text-2xl font-bold mt-4">Hello, Carmi!</h1>

    <!-- Cards and Chart Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <!-- Settlements Card -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
          <!-- Icon placeholder -->
          <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
             <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 002-2V4a2 2 0 00-2-2H4zm2 9a2 2 0 012-2h6a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4z" clip-rule="evenodd"></path></svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">SETTLEMENTS</p>
            <p class="text-xl font-semibold text-gray-900">10,000</p>
            <p class="text-gray-500 text-xs">Completed Transactions</p>
          </div>
        </div>
      </div>

      <!-- Pending Card -->
      <div class="bg-white p-4 rounded-lg shadow">
         <div class="flex items-center">
          <!-- Icon placeholder -->
          <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l1.707 1.707A.996.996 0 006 14v1h4v-4H6L8 9l2-2V2z"></path></svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">PENDING</p>
            <p class="text-xl font-semibold text-gray-900">500</p>
            <p class="text-gray-500 text-xs">Ongoing Transactions</p>
          </div>
        </div>
      </div>

      <!-- Notifications Card -->
      <div class="bg-white p-4 rounded-lg shadow">
         <div class="flex items-center">
          <!-- Icon placeholder -->
          <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">NOTIFICATIONS</p>
            <p class="text-gray-900">Log scan completed — 12,340 entries reviewed.</p>
          </div>
        </div>
      </div>
    </div>

     <!-- Chart and Priority Tracking Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
       <!-- Chart -->
      <div class="bg-white p-4 rounded-lg shadow flex items-center justify-center">
         <p>Chart goes here</p>
      </div>

      <!-- Priority Tracking -->
      <div class="bg-white p-4 rounded-lg shadow">
         <h3 class="text-lg font-semibold mb-2">Priority & Urgency Tracking</h3>
         <ul class="list-disc list-inside">
            <li>High-Priority Communications (Flagged/Urgent)</li>
            <li>Missed/Overdue Responses (Needs follow-up)</li>
            <li>SLA Compliance (if applicable)</li>
         </ul>
      </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="bg-white p-4 rounded-lg shadow mt-6">
       <h3 class="text-lg font-semibold mb-2">RECENT ACTIVITIES</h3>
       <!-- Table goes here -->
       <p>Recent Activities Table goes here</p>
    </div>
  </div>
</div>
