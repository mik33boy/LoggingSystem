<script lang="ts">
  import { onMount, tick } from 'svelte';
  import { fade, scale } from 'svelte/transition';
  import Settings from '../settings/Settings.svelte';
  import { cubicOut } from 'svelte/easing';

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
  let notificationSearch = '';
  let filteredNotifications = notifications;

  function markAllAsRead() {
    notifications = notifications.map(n => ({ ...n, read: true }));
    unreadCount = 0;
    filterNotifications();
  }

  function handleNotificationClick(id: number) {
    notifications = notifications.map(n => 
      n.id === id ? { ...n, read: true } : n
    );
    unreadCount = notifications.filter(n => !n.read).length;
    filterNotifications();
  }

  function filterNotifications() {
    filteredNotifications = notifications.filter(n =>
      n.message.toLowerCase().includes(notificationSearch.toLowerCase())
    );
  }

  // User info
  let user = {
    firstname: '',
    lastname: '',
    email: '',
    avatar: ''
  };
  let userStatus = 'online'; // Could be dynamic

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

  // ESC key closes modals
  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      showProfile = false;
      showSettings = false;
    }
  }
  onMount(() => {
    window.addEventListener('keydown', handleKeydown);
    return () => window.removeEventListener('keydown', handleKeydown);
  });

  function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
  }

  $: filterNotifications();

  function hideLogoOnError(e: Event) {
    const t = e.target as HTMLImageElement | null;
    if (t) t.style.display = 'none';
  }
</script>

<header class="bg-white shadow-sm border-b border-gray-200">
  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <!-- Left: Logo/App Name -->
      <div class="flex items-center space-x-3">
        <span class="text-xl font-bold tracking-tight text-blue-700 select-none">LogiTrack</span>
      </div>
      <!-- Right side buttons -->
      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <div class="relative notifications-dropdown">
          <button
            on:click={() => showNotifications = !showNotifications}
            class="p-2 rounded-lg text-gray-500 hover:text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out relative"
            aria-label="View notifications"
          >
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6 bell-icon {unreadCount > 0 ? 'animate-bounce' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            {#if unreadCount > 0}
              <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white animate-pulse"></span>
            {/if}
          </button>

          {#if showNotifications}
            <div class="origin-top-right absolute right-0 mt-2 w-96 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" transition:scale={{ duration: 180, easing: cubicOut }}>
              <div class="px-4 py-2 border-b border-gray-100">
                <div class="relative z-0">
                  <input
                    type="text"
                    placeholder="Search..."
                    bind:value={notificationSearch}
                    on:input={filterNotifications}
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
                  <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/></svg>
                    Notifications
                  </h3>
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
                  {#if filteredNotifications.length === 0}
                    <div class="px-4 py-6 text-center text-gray-400 text-sm">No notifications found.</div>
                  {/if}
                  {#each filteredNotifications as notification}
                    <button
                      on:click={() => handleNotificationClick(notification.id)}
                      class="w-full text-left px-4 py-3 hover:bg-blue-50 focus:outline-none focus:bg-blue-50 transition duration-150 ease-in-out flex items-start gap-3 {notification.read ? 'bg-white' : 'bg-blue-50'}"
                    >
                      <svg class="w-5 h-5 mt-1 {notification.read ? 'text-gray-300' : 'text-blue-500'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                      <div>
                        <p class="text-sm font-medium text-gray-900">{notification.message}</p>
                        <p class="text-xs text-gray-500 mt-1">{notification.time}</p>
                      </div>
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
            class="flex items-center max-w-xs rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out space-x-2 relative"
            aria-label="Open profile menu"
          >
            <span class="relative">
              <img
                class="h-9 w-9 rounded-full ring-2 ring-blue-200"
                src={user.avatar}
                alt="User avatar"
              />
              <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white {userStatus === 'online' ? 'bg-green-400' : 'bg-gray-300'}"></span>
            </span>
            <span class="hidden sm:block text-sm font-medium text-gray-700">{user.firstname} {user.lastname}</span>
          </button>

          {#if showProfileMenu}
            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" transition:scale={{ duration: 180, easing: cubicOut }}>
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 14c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"/></svg>
                  {user.firstname} {user.lastname}
                </p>
                <p class="text-xs text-gray-500">{user.email}</p>
              </div>
              <div class="py-1">
                <a
                  on:click={() => showProfile = true}
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer flex items-center gap-2"
                >
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  Your Profile
                </a>
                <a
                  on:click={() => showSettings = true}
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer flex items-center gap-2"
                >
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-1.14 1.603-1.14 1.902 0a1.724 1.724 0 002.573 1.01c1.01-.588 2.12.422 1.532 1.432a1.724 1.724 0 001.01 2.573c1.14.3 1.14 1.603 0 1.902a1.724 1.724 0 00-1.01 2.573c.588 1.01-.422 2.12-1.432 1.532a1.724 1.724 0 00-2.573 1.01c-.3 1.14-1.603 1.14-1.902 0a1.724 1.724 0 00-2.573-1.01c-1.01.588-2.12-.422-1.532-1.432a1.724 1.724 0 00-1.01-2.573c-1.14-.3-1.14-1.603 0-1.902a1.724 1.724 0 001.01-2.573c-.588-1.01.422-2.12 1.432-1.532a1.724 1.724 0 002.573-1.01z"/></svg>
                  Settings
                </a>
                <div class="border-t border-gray-100"></div>
                <a
                  on:click={logout}
                  class="block px-4 py-2 text-sm text-red-600 hover:bg-blue-50 cursor-pointer flex items-center gap-2"
                >
                  <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
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

{#if showProfile}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" transition:fade>
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full relative" transition:scale={{ duration: 180, easing: cubicOut }}>
      <button class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl" on:click={() => showProfile = false}>&times;</button>
      <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Profile Information
      </h2>
      <p class="mb-1"><strong>Name:</strong> {user.firstname} {user.lastname}</p>
      <p class="mb-1"><strong>Email:</strong> {user.email}</p>
    </div>
  </div>
{/if}

{#if showSettings}
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" transition:fade>
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full relative" transition:scale={{ duration: 180, easing: cubicOut }}>
      <button class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl" on:click={() => showSettings = false}>&times;</button>
      <Settings {user} />
    </div>
  </div>
{/if}

<style>
  .bell-icon.animate-bounce {
    animation: bounce 1s infinite alternate;
  }
  @keyframes bounce {
    0% { transform: translateY(0); }
    100% { transform: translateY(-4px); }
  }
  .backdrop-blur-sm {
    backdrop-filter: blur(4px);
  }
</style>
