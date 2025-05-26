<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  
  let isCollapsed = false;
  let isMobile = false;

  const menuItems = [
    { icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard', href: '/user-dashboard' },
    { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', label: 'Logs', href: '/user-logs' },
    { 
  icon: 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z', 
  label: 'Messages', 
  href: '/user-messages' 
}
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

<div class="flex h-screen bg-cyan-900">
  <!-- Sidebar -->
  <aside class={`
    ${isCollapsed ? 'w-20' : 'w-64'}
    bg-[#1f1f1f]
    shadow-2xl
    transition-all duration-300 ease-in-out
    py-6 px-2
    border-r border-cyan-800
    backdrop-blur-md bg-opacity-80
    rounded-tr-3xl
    rounded-br-3xl
    min-h-screen
  `} style="box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);">
    <!-- Navigation -->
    <nav class="px-2 space-y-4">
      <!-- Toggle Button -->
      <div class="flex items-center justify-end mb-8">
        <button
          on:click={toggleSidebar}
          class="p-2 rounded-xl hover:bg-cyan-800 focus:outline-none border border-cyan-700 shadow-md transition-transform duration-200 hover:scale-110 group relative"
        >
          <svg
            class="w-6 h-6 text-cyan-300"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d={isCollapsed ? 'M4 6h16M4 12h16M4 18h16' : 'M6 6l12 12M6 18l12-12'}
            />
          </svg>
          <span class="absolute left-full ml-2 top-1/2 -translate-y-1/2 bg-cyan-900 text-cyan-100 text-xs px-2 py-1 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
            {isCollapsed ? 'Expand' : 'Collapse'}
          </span>
        </button>
      </div>
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
              {#if !isCollapsed}
                <span class="ml-1">{item.label}</span>
              {/if}
            </a>
          </li>
        {/each}
      </ul>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto bg-cyan-50">
    <slot />
  </main>
</div>
