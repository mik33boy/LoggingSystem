<script lang="ts">
    import Sidebar from '../../components/sidebar.svelte';
    import { onMount } from 'svelte';
    import { API, type User } from '$lib/api';
    
    // User data state
    let userData: User | null = null;
    let loading = true;
    let error = '';
    
    // Format display name based on username
    function getDisplayName(username: string): string {
      return username ? username.charAt(0).toUpperCase() + username.slice(1) : 'User';
    }
    
    // Get greeting based on time of day
    function getGreeting(): string {
      const hour = new Date().getHours();
      if (hour < 12) return 'Good Morning';
      if (hour < 18) return 'Good Afternoon';
      return 'Good Evening';
    }
    
    // Format current date
    function getCurrentDate(): string {
      return new Date().toLocaleDateString('en-US', { 
        weekday: 'long',
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    }
    
    // Format current time
    function getCurrentTime(): string {
      return new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    }
    
    let currentDate = getCurrentDate();
    let currentTime = getCurrentTime();
    
    // Update time every second
    let timeInterval: ReturnType<typeof setInterval>;
    
    // Fetch user data when component mounts
    onMount(() => {
      // Fetch user data
      const fetchUserData = async () => {
        try {
          // Get user data from API
          const response = await API.getCurrentUser();
          userData = response.user;
        } catch (err) {
          console.error('Error fetching user data:', err);
          error = err instanceof Error ? err.message : 'Failed to load user data';
        } finally {
          loading = false;
        }
      };
      
      // Call fetch function
      fetchUserData();
      
      // Start time interval
      timeInterval = setInterval(() => {
        currentTime = getCurrentTime();
      }, 1000);
      
      // Clean up interval on component destroy
      return () => {
        clearInterval(timeInterval);
      };
    });
  </script>
  
  <div class="flex">
    <!-- Sidebar -->
    <Sidebar active="grid" role={userData?.role || 'user'} />
  
    <!-- Main Content -->
    <div class="flex-1 p-6 bg-gray-50 min-h-screen">
      <!-- Top bar -->
      <div class="flex justify-between items-center mb-6">
        <div class="bg-white rounded-full px-6 py-2 text-center shadow w-1/3">{currentDate}</div>
        <div class="bg-white rounded-full px-6 py-2 text-center shadow w-1/3">{currentTime}</div>
        <div class="flex items-center space-x-3">
          <div class="bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
          </div>
          <div class="text-right">
            {#if loading}
              <div class="font-bold">Loading...</div>
            {:else if error}
              <div class="text-red-500">Error loading data</div>
            {:else}
              <div class="font-bold">{getDisplayName(userData?.username || '')}</div>
              <div class="text-sm text-gray-500">@{userData?.role || 'User'}</div>
            {/if}
          </div>
        </div>
      </div>
  
      <!-- Greeting and Breadcrumb -->
      <div class="bg-white px-6 py-4 rounded shadow mb-4">
        <div class="flex justify-between items-center">
          {#if loading}
            <h1 class="text-xl font-semibold">Loading...</h1>
          {:else if error}
            <h1 class="text-xl font-semibold text-red-500">Error loading user data</h1>
          {:else}
            <h1 class="text-xl font-semibold">{getGreeting()}, {getDisplayName(userData?.username || '')}!</h1>
          {/if}
          <div class="flex items-center">
            <span class="text-sm text-gray-500">
              <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg> LogSystem
            </span>
            <span class="mx-2">/</span>
            <span class="font-medium">Dashboard</span>
          </div>
        </div>
      </div>
  
      <!-- Dashboard widgets -->
      <div class="grid grid-cols-3 gap-4 mb-4">
        <!-- Settlements Widget -->
        <div class="bg-white p-6 rounded shadow flex flex-col items-center">
          <div class="bg-gray-100 rounded-full p-4 mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <div class="text-center">
            <div class="text-sm text-gray-500 mb-1">SETTLEMENTS</div>
            <div class="text-2xl font-bold">10,000</div>
            <div class="text-xs text-gray-400">Completed Transactions</div>
          </div>
        </div>
        
        <!-- Pending Widget -->
        <div class="bg-gray-800 p-6 rounded shadow flex flex-col items-center">
          <div class="bg-gray-700 rounded-full p-4 mb-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3h5m0 0v5m0-5l-6 6M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"></path>
            </svg>
          </div>
          <div class="text-center text-white">
            <div class="text-sm mb-1">PENDING</div>
            <div class="text-2xl font-bold">500</div>
            <div class="text-xs text-gray-300">Ongoing Transactions</div>
          </div>
        </div>
        
        <!-- Notifications Widget -->
        <div class="bg-white p-6 rounded shadow">
          <div class="flex justify-between items-center mb-4">
            <div class="text-sm text-gray-500">NOTIFICATIONS</div>
            <div class="bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
            </div>
          </div>
          <div>
            <div class="text-gray-700">
              Log scan completed — <span class="font-bold">12,340</span>
            </div>
            <div class="text-gray-500">entries reviewed.</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <!-- Recent Activities -->
        <div class="bg-white p-6 rounded shadow">
          <h2 class="text-lg font-medium text-gray-700 mb-4">RECENT ACTIVITIES</h2>
          
          <table class="w-full text-sm">
            <thead>
              <tr class="text-gray-500">
                <th class="pb-3 text-left">TIMESTAMP</th>
                <th class="pb-3 text-left">FROM/ TO</th>
                <th class="pb-3 text-left">SUMMARY</th>
                <th class="pb-3 text-left">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <!-- Empty table body ready for data -->
            </tbody>
          </table>
        </div>

        <div class="grid grid-rows-2 gap-4">
          <!-- Donut Chart Widget -->
          <div class="bg-white p-6 rounded shadow">
            <div class="flex items-center justify-center">
              <div class="w-32 h-32 relative">
                <!-- This would be replaced with an actual chart library -->
                <svg viewBox="0 0 36 36" class="w-full h-full">
                  <circle cx="18" cy="18" r="15.9155" class="stroke-none fill-blue-500" stroke-dasharray="62.5, 100" stroke-dashoffset="25"></circle>
                  <circle cx="18" cy="18" r="15.9155" class="stroke-none fill-blue-300" stroke-dasharray="25, 100" stroke-dashoffset="-37.5"></circle>
                  <circle cx="18" cy="18" r="15.9155" class="stroke-none fill-blue-100" stroke-dasharray="12.5, 100" stroke-dashoffset="-62.5"></circle>
                  <circle cx="18" cy="18" r="10" class="stroke-none fill-white"></circle>
                </svg>
              </div>
              <div class="ml-4 flex flex-col text-sm space-y-1">
                <div>12.5% Pending</div>
                <div>25% Forward</div>
                <div>62.5% Resolved</div>
              </div>
            </div>
          </div>
          
          <!-- Priority & Urgency Tracking -->
          <div class="bg-white p-6 rounded shadow">
            <h3 class="font-medium mb-3">Priority & Urgency Tracking</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>High-Priority Communications (Flagged/Urgent)</li>
              <li>Missed/Overdue Responses (Needs follow-up)</li>
              <li>SLA Compliance (If applicable)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  