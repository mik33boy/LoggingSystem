<script lang="ts">
  let username = '';
  let error = '';
  let password = '';
  let showPassword = false;
  let loading = false;
  
  function handleLogin() {
    // Implement your login logic here
  }

  // Reactive statement to determine the input type
  $: inputType = showPassword ? 'text' : 'password';
</script>
  
  <!-- Login form container with styling -->
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-3xl p-10 relative overflow-hidden">
      <!-- Login form header -->
      <div class="mb-12">
        <h2 class="text-center text-3xl font-bold text-gray-700">
          Personnel Login
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Please input your credentials below.
        </p>
      </div>
      
      <!-- Error message display (conditionally rendered) -->
      {#if error}
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          {error}
        </div>
      {/if}
      
      <!-- Login form with validation -->
      <form class="space-y-4" on:submit|preventDefault={handleLogin}>
        <div class="space-y-4">
          <!-- Username input with icon -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="username"
                type="text"
                bind:value={username}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Email"
              />
            </div>
          </div>
          
          <!-- Password input with icon and toggle visibility button -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="password"
                bind:value={password}
                required
                class="w-full pl-10 pr-10 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Password"
                type="password"
              />
            </div>
          </div>
        </div>
  
        <!-- Submit button with loading state -->
        <div class="flex justify-center">
          <button
            type="submit"
            disabled={loading}
            class="w-full py-4 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-gray-700 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 uppercase tracking-wider"
          >
            {loading ? 'Logging in...' : 'LOGIN'}
          </button>
        </div>
        
        <!-- Registration link -->
        <div class="text-center text-sm text-gray-500">
          Don't have an account? <a href="/register" class="font-medium text-gray-700 hover:text-gray-900">Contact your administrator</a>
        </div>
      </form>
    </div>
  </div> 