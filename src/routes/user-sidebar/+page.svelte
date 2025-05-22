<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  
  let isCollapsed = false;
  let isMobile = false;

  const menuItems = [
    { icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard', href: '/user-dashboard' },
    { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', label: 'Logs', href: '/user-logs' }
  ];
    
  onMount(() => {
    const checkMobile = () => {
      isMobile = window.innerWidth < 768;
      if (isMobile) {
        isCollapsed = true;
      }
    };

    checkMobile();
    window.addEventListener('resize', checkMobile);

    return () => {
      window.removeEventListener('resize', checkMobile);
    };
  });

  function toggleSidebar() {
    isCollapsed = !isCollapsed;
  }
</script>

<div class="flex h-screen bg-gray-100">
  <!-- Sidebar -->
  <aside class={`${isCollapsed ? 'w-20' : 'w-64'} bg-white shadow-lg transition-all duration-300 ease-in-out py-4`}>
    <!-- Navigation -->
    <nav class="px-4 space-y-2">
      <!-- Dashboard and Toggle Button -->
      <div class="flex items-center justify-between mb-6">
        {#if !isCollapsed}
          <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
        {/if}
        <button
          on:click={toggleSidebar}
          class="p-2 rounded-lg hover:bg-gray-100 focus:outline-none"
        >
          <svg
            class="w-6 h-6 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d={isCollapsed ? 'M4 6h16M4 12h16M4 18h16' : 'M6 18L18 6M6 6l12 12'}
            />
          </svg>
        </button>
      </div>

      <ul class="space-y-2">
        {#each menuItems as item}
          <li>
            <a
              href={item.href}
              class="flex items-center p-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200 {$page.url.pathname === item.href ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : ''}"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d={item.icon}
                />
              </svg>
              {#if !isCollapsed}
                <span class="ml-3">{item.label}</span>
              {/if}
            </a>
          </li>
        {/each}
      </ul>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
    <slot />
  </main>
</div>

<style>
  /* Add any custom styles here */
</style>
