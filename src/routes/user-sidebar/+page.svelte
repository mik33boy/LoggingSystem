<script lang="ts">
  import { onMount } from 'svelte';
  
  let isCollapsed = false;
  let isMobile = false;

  const menuItems = [
    { icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', label: 'Dashboard', href: '/dashboard' },
    { icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', label: 'Profile', href: '/profile' },
    { icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', label: 'Settings', href: '/settings' },
    { icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', label: 'Calendar', href: '/calendar' }
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
              class="flex items-center p-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200"
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
