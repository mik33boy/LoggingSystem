<!-- App.svelte -->
<script>
  import { page } from '$app/stores';
  import { browser } from '$app/environment';
  
  // Check if the user is logged in
  const isLoggedIn = () => {
    if (!browser) return false;
    return localStorage.getItem('token') !== null;
  };
</script>

<div class="min-h-screen bg-gray-50">
  <main class="container mx-auto p-4 py-8">
    {#if !isLoggedIn() && $page.url.pathname !== '/login'}
      <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-blue-800 mb-4">Communication Logger System</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
          A centralized, secure, and user-friendly platform for logging, managing, and retrieving communications.
        </p>
        <div class="mt-8">
          <a href="/login" data-sveltekit-preload-data class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-md transition-all transform hover:scale-105">
            Get Started
          </a>
        </div>
      </div>
      
      <!-- Hero Banner -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl overflow-hidden mb-12">
        <div class="p-8 md:p-12 text-white">
          <h2 class="text-3xl font-bold mb-4">General Purpose Computerized Logging and Retrieval System</h2>
          <p class="text-xl opacity-90">For Incoming and Outgoing Communications</p>
        </div>
      </div>
      
      <!-- Software Purpose Section -->
      <section class="mb-12 bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">Software Purpose</h2>
        <p class="text-gray-700 leading-relaxed">
          The purpose of the General Purpose Computerized Logging and Retrieval System for Incoming and Outgoing Communications is to provide a centralized, secure, and user-friendly platform for logging, managing, and retrieving email communications within an organization. This system ensures that all email correspondence both internal and external is accurately recorded, categorized, and easily accessible to authorized users.
        </p>
        <p class="text-gray-700 leading-relaxed mt-4">
          By streamlining the logging and retrieval of emails, the system enhances organizational efficiency, ensures accountability, and supports proper documentation of communication activities for operational, compliance, and auditing purposes.
        </p>
      </section>
      
      <!-- System Overview Section -->
      <section class="mb-12 bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">System Overview</h2>
        <p class="text-gray-700 leading-relaxed">
          The General Purpose Computerized Logging and Retrieval System for Incoming and Outgoing Communications is a secure platform designed to manage email communication logs within an organization. The system allows users to log incoming and outgoing emails manually, categorizing them for easy distinction. Each log entry is timestamped with the precise date and time of the communication event, ensuring accurate tracking.
        </p>
        <p class="text-gray-700 leading-relaxed mt-4">
          The system offers robust search and filter capabilities, allowing users to quickly retrieve logs based on criteria such as date, communication type, and user. This feature enhances the user experience, ensuring efficient access to relevant communication records.
        </p>
        <p class="text-gray-700 leading-relaxed mt-4">
          Access control features ensure that only authorized users can view or edit sensitive data, maintaining data security and privacy. Role-based permissions restrict access based on user roles, addressing concerns about unauthorized access.
        </p>
      </section>
      
      <!-- Product Functions Section -->
      <section class="mb-12">
        <h2 class="text-2xl font-bold text-blue-800 mb-6">Product Functions</h2>
        
        <div class="grid md:grid-cols-3 gap-6">
          <!-- Function 1 -->
          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 text-blue-700 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">User Management & Security</h3>
            <p class="text-gray-600">
              Ensures secure access by allowing only authorized users to log in and manage email logs, protecting the integrity of the data.
            </p>
          </div>
          
          <!-- Function 2 -->
          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 text-blue-700 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Email Log Management</h3>
            <p class="text-gray-600">
              Allows users to add, update, and delete email records, including essential details such as sender, recipient, subject, and date.
            </p>
          </div>
          
          <!-- Function 3 -->
          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 text-blue-700 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Search & Retrieval</h3>
            <p class="text-gray-600">
              Provides users with powerful search and filtering options to quickly find specific email logs based on various criteria.
            </p>
          </div>
        </div>
      </section>
      
      <!-- Constraints Section -->
      <section class="mb-12 bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-blue-800 mb-4">Constraints</h2>
        
        <div class="space-y-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Platform Compatibility</h3>
            <p class="text-gray-700 mt-2">
              The system is developed as a desktop application and is designed to operate smoothly only on desktop operating systems such as Windows.
            </p>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Scalability</h3>
            <p class="text-gray-700 mt-2">
              In its initial deployment phase, the system is configured to support up to 10 users to maintain system performance and responsiveness.
            </p>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Data Input Limitation</h3>
            <p class="text-gray-700 mt-2">
              Since all communication logs are manually entered, the system relies on consistent user input following organizational standards.
            </p>
          </div>
        </div>
      </section>
      
      <!-- Call to Action -->
      <div class="text-center mt-8 mb-12">
        <a href="/login" data-sveltekit-preload-data class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-md transition-all transform hover:scale-105">
          Sign In Now
        </a>
      </div>
      
    {:else if $page.url.pathname === '/login'}
      <h1 class="text-2xl font-bold mb-4">Welcome to Communication Logger</h1>
      <p class="mb-4">Select an option from the menu to get started.</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="/logs" class="p-4 bg-white rounded shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">View Logs</h2>
          <p>Browse and search communication logs</p>
        </a>
        
        <a href="/logs/new" class="p-4 bg-white rounded shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">New Log</h2>
          <p>Create a new communication log entry</p>
        </a>
        
        <a href="/users" class="p-4 bg-white rounded shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">User Management</h2>
          <p>Manage system users (Admin only)</p>
        </a>
      </div>
    {/if}
  </main>
</div>