<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  
  let isCollapsed = false;
  let isMobile = false;
  let currentTime = new Date().toLocaleTimeString();
  let currentDate = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

  const menuItems = [
    { 
      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 
      label: 'Dashboard', 
      href: '/user-dashboard',
      description: 'Overview and analytics'
    },
    { 
      icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 
      label: 'Logs', 
      href: '/user-logs',
      description: 'Activity and system logs'
    },
    { 
      icon: 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z', 
      label: 'Messages', 
      href: '/user-messages',
      description: 'Communication center'
    },
    {
      icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
      label: 'Settings',
      href: '/user-settings',
      description: 'Account preferences'
    }
  ];
    
  onMount(() => {
    const checkMobile = () => {
      isMobile = window.innerWidth < 768;
      if (isMobile) {
        isCollapsed = true;
      }
    };

    const updateDateTime = () => {
      currentTime = new Date().toLocaleTimeString();
      currentDate = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    };

    checkMobile();
    window.addEventListener('resize', checkMobile);
    setInterval(updateDateTime, 1000);

    return () => {
      window.removeEventListener('resize', checkMobile);
    };
  });

  function toggleSidebar() {
    isCollapsed = !isCollapsed;
  }
</script>

<div class="flex h-screen bg-gradient-to-br from-cyan-900 to-cyan-800">
  <!-- Sidebar -->
  <aside class={`
    ${isCollapsed ? 'w-20' : 'w-72'}
    bg-[#1f1f1f]
    shadow-2xl
    transition-all duration-300 ease-in-out
    py-6 px-2
    border-r border-cyan-800
    backdrop-blur-md bg-opacity-90
    rounded-tr-3xl
    rounded-br-3xl
    min-h-screen
    flex flex-col
  `} style="box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);">
    <!-- Toggle Button -->
    <div class="px-4 mb-6">
      <button
        on:click={toggleSidebar}
        class="w-full p-2 rounded-xl hover:bg-cyan-800 focus:outline-none border border-cyan-700 shadow-md transition-all duration-200 hover:scale-105 group relative flex items-center justify-center gap-2 bg-gradient-to-r from-cyan-800 to-cyan-900"
      >
        <svg
          class="w-5 h-5 text-cyan-300 transition-transform duration-300"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          style="transform: rotate({isCollapsed ? '0deg' : '180deg'})"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
          />
        </svg>
        {#if !isCollapsed}
          <span class="text-sm text-cyan-100 font-medium">Collapse Sidebar</span>
        {/if}
      </button>
    </div>

    <!-- User Profile Section -->
    {#if !isCollapsed}
      <div class="px-4 mb-8">
        <div class="bg-gradient-to-r from-cyan-800 to-cyan-900 rounded-2xl p-4 shadow-lg">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg">
                U
              </div>
              <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-cyan-900"></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-cyan-100 truncate">User Name</p>
              <p class="text-xs text-cyan-300 truncate">user@example.com</p>
            </div>
          </div>
          <div class="mt-4 text-xs text-cyan-300">
            <p>{currentTime}</p>
            <p>{currentDate}</p>
          </div>
        </div>
      </div>
    {/if}

    <!-- Navigation -->
    <nav class="px-2 space-y-4 flex-1">
      <ul class="space-y-2">
        {#each menuItems as item}
          <li>
            <a
              href={item.href}
              class="flex items-center gap-3 p-3 text-cyan-100 rounded-xl font-semibold tracking-wide transition-all duration-200
                hover:bg-cyan-800 hover:shadow-lg hover:text-cyan-300
                focus:outline-none focus:ring-2 focus:ring-cyan-400
                active:scale-95
                ${$page.url.pathname === item.href ? 'bg-gradient-to-r from-cyan-700 to-cyan-900 text-cyan-200 shadow-lg border-l-4 border-cyan-400' : ''}"
            >
              <div class="relative">
                <svg
                  class="w-6 h-6 text-gray-300 drop-shadow"
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
                {#if $page.url.pathname === item.href}
                  <div class="absolute -inset-1 bg-cyan-400/20 rounded-full blur-sm"></div>
                {/if}
              </div>
              {#if !isCollapsed}
                <div class="flex-1">
                  <span class="ml-1">{item.label}</span>
                  <p class="text-xs text-cyan-400 mt-0.5">{item.description}</p>
                </div>
              {/if}
            </a>
          </li>
        {/each}
      </ul>
    </nav>

    <!-- Footer -->
    {#if !isCollapsed}
      <div class="px-4 mt-auto">
        <div class="bg-gradient-to-r from-cyan-800 to-cyan-900 rounded-xl p-3 text-center">
          <p class="text-xs text-cyan-300">System Status: <span class="text-green-400">Online</span></p>
        </div>
      </div>
    {/if}
  </aside>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto bg-gradient-to-br from-cyan-50 to-cyan-100">
    <slot />
  </main>
</div>
