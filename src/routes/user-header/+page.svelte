<script lang="ts">
  import { onMount } from 'svelte';
  
  let searchQuery = '';
  let notifications = [
    { id: 1, message: 'New message from John', time: '5 min ago', read: false },
    { id: 2, message: 'Meeting reminder', time: '1 hour ago', read: false },
    { id: 3, message: 'Task completed', time: '2 hours ago', read: true }
  ];
  
  let showNotifications = false;
  let showProfileMenu = false;
  let unreadCount = notifications.filter(n => !n.read).length;

  function handleSearch() {
    // Implement search functionality
    console.log('Searching for:', searchQuery);
  }

  function markAllAsRead() {
    notifications = notifications.map(n => ({ ...n, read: true }));
    unreadCount = 0;
  }

  function handleNotificationClick(id: number) {
    notifications = notifications.map(n => 
      n.id === id ? { ...n, read: true } : n
    );
    unreadCount = notifications.filter(n => !n.read).length;
  }

  // Close dropdowns when clicking outside
  onMount(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as HTMLElement;
      if (!target.closest('.notifications-dropdown') && !target.closest('.profile-dropdown')) {
        showNotifications = false;
        showProfileMenu = false;
      }
    };

    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  });
</script>

<header class="bg-white shadow-sm border-b border-gray-200">
  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Search Bar -->
      <div class="flex-1 flex items-center max-w-2xl">
        <div class="w-full">
          <label for="search" class="sr-only">Search</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              id="search"
              bind:value={searchQuery}
              on:keydown={(e) => e.key === 'Enter' && handleSearch()}
              class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
              placeholder="Search anything..."
              type="search"
            />
          </div>
        </div>
      </div>

      <!-- Right side buttons -->
      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <div class="relative notifications-dropdown">
          <button
            on:click={() => showNotifications = !showNotifications}
            class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
          >
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            {#if unreadCount > 0}
              <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
            {/if}
          </button>

          {#if showNotifications}
            <div class="origin-top-right absolute right-0 mt-2 w-96 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 transform transition-all duration-200 ease-out">
              <div class="py-1">
                <div class="px-4 py-3 flex justify-between items-center border-b border-gray-100">
                  <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                  {#if unreadCount > 0}
                    <button
                      on:click={markAllAsRead}
                      class="text-xs font-medium text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out"
                    >
                      Mark all as read
                    </button>
                  {/if}
                </div>
                <div class="max-h-96 overflow-y-auto">
                  {#each notifications as notification}
                    <button
                      on:click={() => handleNotificationClick(notification.id)}
                      class="w-full text-left px-4 py-3 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition duration-150 ease-in-out {notification.read ? 'bg-white' : 'bg-blue-50'}"
                    >
                      <p class="text-sm font-medium text-gray-900">{notification.message}</p>
                      <p class="text-xs text-gray-500 mt-1">{notification.time}</p>
                    </button>
                  {/each}
                </div>
              </div>
            </div>
          {/if}
        </div>

        <!-- Profile dropdown -->
        <div class="relative profile-dropdown">
          <button
            on:click={() => showProfileMenu = !showProfileMenu}
            class="flex items-center max-w-xs rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
          >
            <span class="sr-only">Open user menu</span>
            <img
              class="h-9 w-9 rounded-full ring-2 ring-gray-200"
              src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
              alt="User profile"
            />
          </button>

          {#if showProfileMenu}
            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 transform transition-all duration-200 ease-out">
              <div class="py-1">
                <a href="/profile" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                  <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Your Profile
                </a>
                <a href="/settings" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                  <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </a>
                <div class="border-t border-gray-100"></div>
                <a href="/logout" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-gray-100 transition duration-150 ease-in-out">
                  <svg class="mr-3 h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Sign out
                </a>
              </div>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</header>

<style>
  /* Add any custom styles here */
</style>
