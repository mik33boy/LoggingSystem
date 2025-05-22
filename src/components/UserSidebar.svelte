<script lang="ts">
  import { page } from '$app/stores';
</script>

<div class="w-64 h-screen bg-gray-800 text-white p-4">
  <!-- User Profile Section -->
  <div class="mb-8">
    <div class="flex items-center space-x-3 mb-4">
      <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center">
        <span class="text-lg font-semibold">C</span>
      </div>
      <div>
        <p class="font-semibold">Carmi</p>
        <p class="text-sm text-gray-400">User</p>
      </div>
    </div>
  </div>

  <!-- Navigation Links -->
  <nav>
    <ul class="space-y-2">
      <li>
        <a href="/user-dashboard" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-700 {$page.url.pathname === '/user-dashboard' ? 'bg-gray-700' : ''}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="/user-dashboard/transactions" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-700 {$page.url.pathname === '/user-dashboard/transactions' ? 'bg-gray-700' : ''}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          <span>Transactions</span>
        </a>
      </li>
      <li>
        <a href="/user-dashboard/reports" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-700 {$page.url.pathname === '/user-dashboard/reports' ? 'bg-gray-700' : ''}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <span>Reports</span>
        </a>
      </li>
      <li>
        <a href="/user-dashboard/settings" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-700 {$page.url.pathname === '/user-dashboard/settings' ? 'bg-gray-700' : ''}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Logout Button -->
  <div class="absolute bottom-4 left-4 right-4">
    <button class="w-full flex items-center space-x-2 p-2 rounded hover:bg-gray-700 text-red-400">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
      </svg>
      <span>Logout</span>
    </button>
  </div>
</div> 