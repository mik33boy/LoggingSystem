<script lang="ts">
  import { API_ENDPOINTS, apiRequest } from '$lib/api/config';

  let firstName = '';
  let lastName = '';
  let email = '';
  let password = '';
  let confirmPassword = '';
  let error = '';
  let loading = false;
  let username = '';
  
  async function handleRegister() {
    try {
      if (password !== confirmPassword) {
        error = 'Passwords do not match';
        return;
      }

      loading = true;
      error = '';
      
      const data = await apiRequest(API_ENDPOINTS.AUTH.REGISTER, {
        method: 'POST',
        body: JSON.stringify({ firstName, lastName, email, password, username })
      });

      // Store the token in localStorage
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));

      // Redirect to dashboard
      window.location.href = '/user-dashboard';
    } catch (err: any) {
      error = err.message;
    } finally {
      loading = false;
    }
  }
</script>
  
  <!-- Registration form container with styling -->
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-3xl p-10 relative overflow-hidden">
      <!-- Registration form header -->
      <div class="mb-12">
        <h2 class="text-center text-3xl font-bold text-gray-700">
          Create Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Please fill in your details below.
        </p>
      </div>
      
      <!-- Error message display -->
      {#if error}
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          {error}
        </div>
      {/if}
      
      <!-- Registration form -->
      <form class="space-y-4" on:submit|preventDefault={handleRegister}>
        <div class="space-y-4">
          <!-- First Name input -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="firstName"
                type="text"
                bind:value={firstName}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter First Name"
              />
            </div>
          </div>

          <!-- Last Name input -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="lastName"
                type="text"
                bind:value={lastName}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Last Name"
              />
            </div>
          </div>

          <!-- Email input -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
              </span>
              <input
                id="email"
                type="email"
                bind:value={email}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Email"
              />
            </div>
          </div>

          <!-- Username input -->
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
                placeholder="Enter Username"
              />
            </div>
          </div>
          
          <!-- Password input -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="password"
                type="password"
                bind:value={password}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Enter Password"
              />
            </div>
          </div>

          <!-- Confirm Password input -->
          <div>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                id="confirmPassword"
                type="password"
                bind:value={confirmPassword}
                required
                class="w-full pl-10 pr-3 py-4 border border-gray-200 rounded-md text-gray-500 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-transparent"
                placeholder="Confirm Password"
              />
            </div>
          </div>
        </div>
  
        <!-- Submit button -->
        <div class="flex justify-center">
          <button
            type="submit"
            disabled={loading}
            class="w-full py-4 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-gray-700 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 uppercase tracking-wider"
          >
            {loading ? 'Creating Account...' : 'REGISTER'}
          </button>
        </div>
        
        <!-- Login link -->
        <div class="text-center text-sm text-gray-500">
          Already have an account? <a href="/login" class="font-medium text-gray-700 hover:text-gray-900">Login here</a>
        </div>
      </form>
    </div>
  </div> 