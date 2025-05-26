<script lang="ts">
    import type { PageData } from './$types';

    // Placeholder for selected chat index
    let selectedChatIndex: number | null = 0; // Set to 0 for the first chat item to be selected initially
</script>

   <!-- Header Section -->
   <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-md mb-4 border border-gray-200 w-full">
    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Messenger</h1>
    <div class="flex items-center text-gray-600 text-sm">
      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
        <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
        LogSystem
      </span>
      <span class="mx-2">/</span>
      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Messenger</span>
    </div>
  </div>

<div class="flex h-screen bg-gray-100 text-gray-800">

    <!-- Left Sidebar (Chat List) -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
        <div class="w-full px-3 py-2">
            <!-- Header Section -->
            <div class="flex items-center justify-between bg-white p-2 rounded-xl shadow-sm mb-3 border border-gray-200 w-full">
                <h1 class="text-lg font-bold text-gray-900 tracking-tight">Chats</h1>
                <!-- Search bar -->
                <div class="relative flex-grow ml-4">
                    <input type="text" placeholder="Search Messenger" class="w-full pl-10 pr-3 py-1.5 rounded-full bg-gray-100 text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto p-3">
            <!-- Placeholder Chat Item -->
            <div class="flex items-center p-3 mb-2 rounded-lg cursor-pointer transition duration-200 ease-in-out {selectedChatIndex === 0 ? 'bg-blue-100 hover:bg-blue-200' : 'hover:bg-gray-100'}" on:click={() => selectedChatIndex = 0}>
                <img src="https://via.placeholder.com/40" alt="User Avatar" class="w-10 h-10 rounded-full mr-4 object-cover">
                <div>
                    <h2 class="text-gray-900 font-semibold">User Name</h2>
                    <p class="text-gray-600 text-sm">Last message...</p>
                </div>
            </div>
            <!-- Add more chat items here -->
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-gray-50">
        <!-- Chat Header (User/Group Name) -->
        <div class="bg-white p-4 border-b border-gray-200 flex items-center shadow-sm">
            <img src="https://via.placeholder.com/40" alt="Recipient Avatar" class="w-10 h-10 rounded-full mr-3 object-cover">
            <h2 class="text-gray-900 font-semibold text-lg">Recipient Name</h2>
            <!-- Add icons for call, search, etc. -->
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-4 flex items-center justify-center">
            <!-- Message list will go here -->
            <p class="text-center text-gray-400 italic text-sm">Start of the conversation</p>
        </div>
        <div class="bg-white p-4 border-t border-gray-200 shadow-sm">
            <!-- Message input will go here -->
            <input type="text" placeholder="Type a message..." class="w-full px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>

    <!-- Right Sidebar (Chat Details) -->
    <div class="w-64 bg-white border-l border-gray-200 flex flex-col shadow-sm">
        <div class="p-4">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Chat Details</h2>
            <!-- Chat details will go here -->
            <p class="text-gray-600">Details section</p>
        </div>
    </div>
</div>