<script lang="ts">
import { API_ENDPOINTS, apiRequest } from '$lib/api/config';
import { onMount } from 'svelte';

// Dashboard data
let settlements = 0;
let pending = 0;
let logScanEntries = 0;
let donutData = [0, 0, 0]; // Pending, Forward, Resolved
let donutColors = ["#3B82F6", "#F59E42", "#10B981"];
let recentActivities: any[] = [];

  // Define type for donut segments
  interface DonutSegment {
    color: string;
    dash: number;
    offset: number;
  }

let donutSegments: DonutSegment[] = [];

async function fetchDashboardData() {
  try {
    const response = await apiRequest(API_ENDPOINTS.LOGS + '?action=dashboard', {
      method: 'GET'
    });

    if (response.success) {
      const { data } = response;
      
      // Update total logs
      logScanEntries = data.total_logs;
      
      // Update direction stats
      const directionStats = data.by_direction;
      pending = directionStats.find((d: any) => d.direction === 'incoming')?.count || 0;
      settlements = directionStats.find((d: any) => d.direction === 'outgoing')?.count || 0;
      
      // Update donut chart data
      const typeStats = data.by_type;
      const total = typeStats.reduce((acc: number, curr: any) => acc + curr.count, 0);
      donutData = [
        (pending / total) * 100,
        ((total - pending) / 2 / total) * 100,
        ((total - pending) / 2 / total) * 100
      ];
      
      // Update recent activities
      recentActivities = data.recent_logs;
      
      // Update donut segments
      updateDonutSegments();
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  }
}

function updateDonutSegments() {
  const donutTotal = donutData.reduce((a, b) => a + b, 0);
  donutSegments = [];
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
}

onMount(() => {
  fetchDashboardData();
});
</script>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex flex-col">
  <div class="flex-1 flex flex-col overflow-hidden items-center justify-start">
      <div class="w-full mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between bg-white/80 p-4 rounded-2xl shadow-xl mb-6 border border-gray-200 w-full backdrop-blur-md">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-400 to-green-400 flex items-center justify-center text-white text-2xl font-bold shadow">
              {localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user') || '{}').firstName?.[0] ?? 'U' : 'U'}
            </div>
            <div>
              <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Hello, {localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user') || '{}').firstName : 'User'}!</h1>
              <div class="text-sm text-gray-500 font-medium">Welcome back! Ready to track your communications?</div>
            </div>
          </div>
          <div class="flex items-center text-gray-600 text-sm gap-2">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
              <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
              LogSystem
            </span>
            <span class="mx-2">/</span>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Dashboard</span>
          </div>
        </div>
        <!-- Summary Cards Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 w-full">
          <!-- Settlements Card -->
          <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in">
            <div class="flex items-center mb-2">
              <span class="bg-blue-100 p-2 rounded-full mr-2">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
              </span>
              <span class="text-3xl font-extrabold text-blue-700">{settlements.toLocaleString()}</span>
            </div>
            <div class="text-xs text-blue-700 font-semibold tracking-wide">OUTGOING</div>
            <div class="text-xs text-gray-400">Completed Communications</div>
          </div>
          <!-- Pending Card -->
          <div class="bg-gradient-to-tr from-blue-900 to-blue-700 p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in delay-100">
            <div class="flex items-center mb-2">
              <span class="bg-white p-2 rounded-full mr-2">
                <svg class="w-7 h-7 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17v-6a5 5 0 00-10 0v6"/><rect x="5" y="17" width="14" height="2" rx="1" fill="currentColor"/></svg>
              </span>
              <span class="text-3xl font-extrabold text-white">{pending}</span>
            </div>
            <div class="text-xs text-white font-semibold tracking-wide">INCOMING</div>
            <div class="text-xs text-blue-200">Received Communications</div>
          </div>
          <!-- Notifications Card -->
          <div class="bg-gray-100 p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in delay-200">
            <div class="flex items-center mb-2">
              <span class="bg-white p-2 rounded-full mr-2">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              </span>
              <span class="font-semibold text-gray-700">Total Logs</span>
            </div>
            <div class="text-lg text-gray-700 text-center font-bold">Log entries — <span class="text-blue-700">{logScanEntries.toLocaleString()}</span> total</div>
          </div>
          <!-- Donut Chart Card -->
          <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in delay-300">
            <svg width="80" height="80" viewBox="0 0 42 42" class="mb-2 animate-spin-slow">
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
              <div class="font-bold text-blue-700">{donutData[0].toFixed(1)}% Incoming</div>
              <div class="font-bold text-yellow-600">{donutData[1].toFixed(1)}% Processing</div>
              <div class="font-bold text-green-600">{donutData[2].toFixed(1)}% Completed</div>
            </div>
          </div>
        </div>
        <!-- Bottom Section: Recent Activities and Priority Tracking -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
          <!-- Recent Activities Section (table) -->
          <div class="bg-white p-6 rounded-2xl shadow md:col-span-2 border border-gray-100">
            <h3 class="text-lg font-semibold mb-2 text-gray-800">RECENT ACTIVITIES</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm rounded-xl overflow-hidden shadow-lg border-2 border-teal-200">
                <thead class="bg-gradient-to-r from-teal-700 to-blue-900 text-white font-bold tracking-wider border-b border-teal-300 sticky top-0 z-10">
                  <tr>
                    <th class="py-3 px-4 rounded-tl-xl text-left">TIMESTAMP</th>
                    <th class="py-3 px-4 text-left">FROM/ TO</th>
                    <th class="py-3 px-4 text-left">SUMMARY</th>
                    <th class="py-3 px-4 rounded-tr-xl text-left">ACTIONS</th>
                  </tr>
                </thead>
                <tbody>
                  {#each recentActivities as activity, i}
                    <tr class="border-b last:border-b-0 {i % 2 === 0 ? 'bg-white' : 'bg-teal-50'}">
                      <td class="py-3 px-4 whitespace-nowrap text-left">{activity.timestamp}</td>
                      <td class="py-3 px-4 whitespace-nowrap text-left">{activity.sender || activity.recipient || '--'}</td>
                      <td class="py-3 px-4 text-left">{activity.subject}</td>
                      <td class="py-3 px-4 text-left">
                        <a href="/log/{activity.id}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-teal-600 text-white font-semibold shadow hover:bg-teal-800 transition-colors duration-150">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                          View
                        </a>
                      </td>
                    </tr>
                  {:else}
                  <tr>
                      <td colspan="4" class="py-6 text-center text-gray-500 bg-white rounded-b-xl">No recent activities</td>
                  </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          </div>
          <!-- Priority Tracking -->
          <div class="bg-gradient-to-tr from-teal-50 to-blue-50 p-6 rounded-2xl shadow border-l-4 border-teal-600 flex flex-col gap-3 animate-fade-in">
            <h3 class="text-lg font-bold mb-2 text-teal-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Priority & Urgency Tracking
            </h3>
            <ul class="space-y-3">
              <li class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 font-bold animate-pulse">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414 1.414M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M6.343 6.343L4.929 4.929"/></svg>
                </span>
                <span>High-Priority Communications</span>
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">{pending} Incoming</span>
              </li>
              <li class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-teal-100 text-teal-600 font-bold">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
                </span>
                <span>Outgoing Communications</span>
                <span class="ml-auto bg-teal-600 text-white text-xs font-bold px-2 py-0.5 rounded">{settlements} Total</span>
              </li>
              <li class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <span>Total Log Entries</span>
                <span class="ml-auto bg-blue-700 text-white text-xs font-bold px-2 py-0.5 rounded">{logScanEntries}</span>
              </li>
            </ul>
            <div class="mt-4 text-xs text-teal-700 italic text-center">
              Stay on top of your priorities for a productive day!
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

