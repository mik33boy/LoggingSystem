<script>
  import { themeStore } from '$lib/stores/theme';

  export let user;

  // Subscribe to theme store to get current theme
  $: currentTheme = $themeStore;

  function toggleTheme() {
    themeStore.toggle();
  }
</script>

<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md transition-colors duration-200">
  <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">Settings</h2>

  <!-- Theme Toggle Section -->
  <div class="mb-8">
    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Appearance</h3>

    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
          {#if currentTheme === 'dark'}
            <!-- Moon icon for dark mode -->
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          {:else}
            <!-- Sun icon for light mode -->
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          {/if}
        </div>
        <div>
          <p class="font-medium text-gray-900 dark:text-white">Theme</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Switch between light and dark mode
          </p>
        </div>
      </div>

      <!-- Toggle Switch -->
      <button
        on:click={toggleTheme}
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 {currentTheme === 'dark' ? 'bg-blue-600' : 'bg-gray-200'}"
        role="switch"
        aria-checked={currentTheme === 'dark'}
        aria-label="Toggle theme"
      >
        <span
          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {currentTheme === 'dark' ? 'translate-x-6' : 'translate-x-1'}"
        ></span>
      </button>
    </div>

    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
      Current theme: <span class="font-medium capitalize">{currentTheme}</span>
    </p>
  </div>

  <!-- User Info Section -->
  <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">User Information</h3>
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
      <p class="text-gray-900 dark:text-white">
        Welcome, <span class="font-medium">{user.firstname}</span>!
      </p>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        Manage your account settings and preferences here.
      </p>
    </div>
  </div>
</div>