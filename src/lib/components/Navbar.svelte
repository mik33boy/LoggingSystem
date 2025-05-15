<script>
  import { browser } from '$app/environment';
  import { page } from '$app/stores';
  
  // Check if user is logged in
  const isLoggedIn = () => {
    if (!browser) return false;
    return localStorage.getItem('token') !== null;
  };
  
  // Get user data
  const getUser = () => {
    if (!browser) return null;
    try {
      return JSON.parse(localStorage.getItem('user') || 'null');
    } catch {
      return null;
    }
  };
  
  // Handle logout
  function logout() {
    if (browser) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/';
    }
  }
</script>

<nav class="bg-blue-800 text-white shadow-md">
  <div class="container mx-auto px-4 py-2">
    <div class="flex justify-between items-center">
      <!-- Logo -->
      <a href="/" class="text-xl font-bold">Communication Logger</a>
      
      <!-- Menu -->
      <div class="flex items-center space-x-4">
        {#if isLoggedIn()}
          <a 
            href="/dashboard" 
            class="px-3 py-2 rounded hover:bg-blue-700 transition-colors {$page.url.pathname === '/dashboard' ? 'bg-blue-700' : ''}"
          >
            Dashboard
          </a>
          <a 
            href="/logviewer" 
            class="px-3 py-2 rounded hover:bg-blue-700 transition-colors {$page.url.pathname.includes('/logviewer') ? 'bg-blue-700' : ''}"
          >
            View Logs
          </a>
          <a 
            href="/logeditor" 
            class="px-3 py-2 rounded hover:bg-blue-700 transition-colors {$page.url.pathname.includes('/logeditor') ? 'bg-blue-700' : ''}"
          >
            New Log
          </a>
          
          <!-- User Menu -->
          <div class="relative group">
            <button class="px-3 py-2 rounded hover:bg-blue-700 transition-colors flex items-center">
              <span>{getUser()?.username || 'User'}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg py-1 invisible group-hover:visible z-10">
              {#if getUser()?.role === 'admin'}
                <a href="/admin" class="block px-4 py-2 hover:bg-gray-100">Admin Panel</a>
                <div class="border-t border-gray-100"></div>
              {/if}
              <button on:click={logout} class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                Logout
              </button>
            </div>
          </div>
        {:else if $page.url.pathname !== '/login'}
          <a href="/login" class="px-4 py-2 bg-white text-blue-800 rounded-lg font-medium hover:bg-gray-100 transition-colors">
            Login
          </a>
        {/if}
      </div>
    </div>
  </div>
</nav> 