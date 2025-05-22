<script>
    /**
     * Login Component
     * 
     * Handles user authentication through a login form
     * Validates inputs, sends API requests, and manages UI state
     */
    import { goto } from '$app/navigation';
    import { browser } from '$app/environment';
    import { API } from '$lib/api';
    
    // Form state variables
    let username = '';
    let password = '';
    let error = '';
    let loading = false;
    let showPassword = false;
    
    /**
     * Handles form submission for login
     * Validates inputs, calls API, and manages errors
     */
    async function handleLogin() {
      // Basic input validation
      if (!username || !password) {
        error = 'Please enter both username and password';
        return;
      }
      
      if (username.length < 3) {
        error = 'Username must be at least 3 characters';
        return;
      }
      
      if (password.length < 6) {
        error = 'Password must be at least 6 characters';
        return;
      }
      
      console.log('Attempting login with:', { username });
      
      try {
        // Set loading state and clear errors
        loading = true;
        error = '';
        
        // Call API to authenticate
        const response = await API.login(username, password);
        console.log('Login successful:', response);
        
        // Store authentication data in localStorage (only in browser)
        if (browser) {
          localStorage.setItem('token', response.token);
          localStorage.setItem('user', JSON.stringify(response.user));
        }
        
        // Redirect to dashboard page on success
        goto('/dashboard');
      } catch (err) {
        console.error('Login   error:', err);
        if (err instanceof Error) {
          error = err.message;
        } else {
          error = 'An unknown error occurred';
        }
      } finally {
        loading = false;
      }
    }
    
    /**
     * Toggles password visibility between plain text and masked
     */
    function togglePasswordVisibility() {
      showPassword = !showPassword;
    }
  </script>
  
  <!-- Login form container with styling -->
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-lg p-10 relative overflow-hidden">
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
      <form class="space-y-6" on:submit|preventDefault={handleLogin}>
        <div class="space-y-5">
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
                type={showPassword ? 'text' : 'password'}
                bind:value={password}
                required
                class="w-full pl-10 pr-10 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Password"
              />
              <!-- Toggle password visibility button -->
              <button 
                type="button" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400"
                on:click={togglePasswordVisibility}
              >
                {#if showPassword}
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                  </svg>
                {:else}
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                  </svg>
                {/if}
              </button>
            </div>
          </div>
        </div>
  
        <!-- Submit button with loading state -->
        <div>
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