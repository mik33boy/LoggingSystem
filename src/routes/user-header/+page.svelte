<script lang="ts">
  import { onMount } from 'svelte';
  import Settings from '../settings/Settings.svelte';
  
  let notifications = [
    { id: 1, message: 'New message from John', time: '5 min ago', read: false },
    { id: 2, message: 'Meeting reminder', time: '1 hour ago', read: false },
    { id: 3, message: 'Task completed', time: '2 hours ago', read: true }
  ];
  
  let showNotifications = false;
  let showProfileMenu = false;
  let unreadCount = notifications.filter(n => !n.read).length;
  let showProfile = false;
  let showSettings = false;

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

  // User info
  let user = {
    firstname: '',
    lastname: '',
    email: '',
    avatar: ''
  };

  import { onMount as onMountSvelte } from 'svelte';
  onMountSvelte(async () => {
    try {
      // Try both 'authToken' and 'token' for compatibility
      const token = localStorage.getItem('authToken') || localStorage.getItem('token');
      if (!token) return;

      // Try to get user info from localStorage first
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          const userObj = JSON.parse(userStr);
          user = {
            firstname: userObj.firstName || userObj.firstname || '',
            lastname: userObj.lastName || userObj.lastname || '',
            email: userObj.email || '',
            avatar: userObj.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent((userObj.firstName || userObj.firstname || '') + ' ' + (userObj.lastName || userObj.lastname || ''))}`
          };
        } catch {}
      }

      // Optionally, fetch from API if you have a /api/user endpoint
      // const response = await fetch('/api/user', {
      //   headers: {
      //     'Authorization': `Bearer ${token}`
      //   }
      // });
      // if (response.ok) {
      //   const data = await response.json();
      //   user = {
      //     ...data,
      //     avatar: data.avatar || `https://ui-avatars.com/api/?name=${data.firstname}+${data.lastname}`
      //   };
      // }
    } catch (err) {
      console.error('Error loading user:', err);
    }
  });

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
    <div class="flex justify-end items-center h-16">
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
            <div class="origin-top-right absolute right-0 mt-2 w-96 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
              <div class="px-4 py-2 border-b border-gray-100">
                <div class="relative z-0">
                  <input
                    type="text"
                    placeholder="Search..."
                    class="w-full pl-4 pr-10 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                </div>
              </div>
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
            class="flex items-center max-w-xs rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out space-x-2"
          >
            <img
              class="h-9 w-9 rounded-full ring-2 ring-gray-200"
              src={user.avatar}
              alt="User avatar"
            />
            <span class="hidden sm:block text-sm font-medium text-gray-700">{user.firstname} {user.lastname}</span>
          </button>

          {#if showProfileMenu}
            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5">
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{user.firstname} {user.lastname}</p>
                <p class="text-xs text-gray-500">{user.email}</p>
              </div>
              <div class="py-1">
                <a
                  on:click={() => showProfile = true}
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                >
                  Your Profile
                </a>
                <a
                  on:click={() => showSettings = true}
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                >
                  Settings
                </a>
                <div class="border-t border-gray-100"></div>
                <a href="/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Sign out</a>
              </div>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</header>

{#if showProfile}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full relative">
      <button class="absolute top-2 right-3 text-gray-400 hover:text-gray-600" on:click={() => showProfile = false}>&times;</button>
      <h2 class="text-xl font-semibold mb-2">Profile Information</h2>
      <p class="mb-1"><strong>Name:</strong> {user.firstname} {user.lastname}</p>
      <p class="mb-1"><strong>Email:</strong> {user.email}</p>
    </div>
  </div>
{/if}

{#if showSettings}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full relative">
      <button class="absolute top-2 right-3 text-gray-400 hover:text-gray-600" on:click={() => showSettings = false}>&times;</button>
      <Settings {user} />
    </div>
  </div>
{/if}

<style>
  /* Add any custom styles here */
</style>
