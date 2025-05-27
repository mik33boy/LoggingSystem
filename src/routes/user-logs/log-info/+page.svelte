<script lang="ts">
    import { API_ENDPOINTS, apiRequest } from '$lib/api/config';
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';

    let log: any = null;
    let loading = true;
    let error: string | null = null;
    let selectedDate: string = '';
    let activeOptionsId: string | null = null;
    let replies: any[] = []; // Variable to store replies
    let loadingReplies = false; // Loading state for replies
    let repliesError: string | null = null; // Error state for replies

    onMount(async () => {
        loading = true;
        try {
            const logId = $page.url.searchParams.get('id');
            if (!logId) {
                error = 'No log ID provided';
                loading = false;
                return;
            }
            
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?id=${logId}`, {
                method: 'GET'
            });
            
            if (response.success) {
                log = response.data[0]; // Assuming the backend returns an array with one log

                // Fetch replies after fetching the main log if log is successful
                if (log && log.client_name) {
                    loadingReplies = true;
                    try {
                        // Use client_name from the fetched log instead of local storage
                        const clientName = log.client_name;
                        console.log('Fetching replies for client:', clientName);

                        if (clientName) {
                            const repliesResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getReplies&client_name=${encodeURIComponent(clientName)}`, {
                                method: 'GET'
                            });

                            if (repliesResponse.success) {
                                replies = repliesResponse.data;
                            } else {
                                repliesError = 'Failed to load replies';
                            }
                        } else {
                            repliesError = 'Client name not available for fetching replies.';
                        }
                    } catch (err) {
                        repliesError = 'Error loading replies';
                        console.error(err);
                    } finally {
                        loadingReplies = false;
                    }
                }

            } else {
                error = 'Failed to load log details';
            }
        } catch (err) {
            error = 'Error loading log details';
            console.error(err);
        } finally {
            loading = false;
        }
    });

    function toggleOptions(e: Event, id: string) {
        e.stopPropagation();
        if (activeOptionsId === id) {
            activeOptionsId = null; // Hide the menu if it's already open for this log
        } else {
            activeOptionsId = id; // Show the menu for this log
        }
    }

    function handleReply(id: string) {
        // Implement the logic to reply to the message
        console.log(`Reply to message with id: ${id}`);
    }

    function handleConfidential(id: string) {
        // Implement the logic to mark the message as confidential
        console.log(`Mark message with id: ${id} as confidential`);
    }

    async function handleDelete(id: string) {
        if (!confirm('Are you sure you want to delete this log?')) {
            return;
        }
        
        try {
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?id=${id}`, {
                method: 'DELETE'
            });
            
            if (response.success) {
                // Redirect back to logs list after successful deletion
                goto('/user-logs');
            } else {
                alert('Failed to delete log');
            }
        } catch (err) {
            console.error('Error deleting log:', err);
            alert('Error deleting log');
        }
    }

    async function handleGenerateReport(id: string) {
        try {
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}/report?id=${id}`, {
                method: 'GET'
            });
            
            if (response.success) {
                // Handle the report data - you might want to download it or show it in a modal
                const reportData = response.data;
                // For now, we'll just log it
                console.log('Report generated:', reportData);
                alert('Report generated successfully');
            } else {
                alert('Failed to generate report');
            }
        } catch (err) {
            console.error('Error generating report:', err);
            alert('Error generating report');
        }
    }

    // Helper function to format date and time
    function formatDateTime(dateTimeString: string): { date: string, time: string } {
        const date = new Date(dateTimeString);
        const optionsDate: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const optionsTime: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
        return {
            date: date.toLocaleDateString(undefined, optionsDate),
            time: date.toLocaleTimeString(undefined, optionsTime)
        };
    }

</script>

<div class="flex h-screen bg-gray-50">
    <div class="flex-1 flex flex-col overflow-hidden items-center justify-start">
        <div class="w-full px-2 py-1">
            <!-- Header Section -->
            <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-md mb-4 border border-gray-200 w-full">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Log Details</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center text-gray-600 text-sm">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
                            <svg class="w-4 h-4 mr-1 text-ggray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            LogSystem
                        </span>
                        <span class="mx-2">/</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm bg-gray-100 text-gray-800">Log Management</span>
                        <span class="mx-2">/</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Log Details</span>
                    </div>
                </div>
            </div>

            {#if !loading && !error && log}
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-md font-semibold text-gray-800">Client Information</h2>
                    </div>
                    <p class="text-sm text-gray-700">Client Name: {log.client_name || "N/A"}</p>
                    <p class="text-sm text-gray-700">Client Email: {log.sender || "N/A"}</p>
                </div>

                <!-- Messenger Interface -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-md font-semibold text-gray-800">Conversation History</h2>
                        <div class="flex items-center gap-4">
                            <!-- Date Filter -->
                            <div class="flex items-center gap-2">
                                <input 
                                    type="date" 
                                    bind:value={selectedDate}
                                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button 
                                    class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm flex items-center gap-1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Search
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <button class="p-2 hover:bg-gray-100 rounded-full" on:click|stopPropagation={(e) => toggleOptions(e, 'conversation-options')}>
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    {#if activeOptionsId === 'conversation-options'}
                                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                        <ul class="py-1 text-sm text-gray-700">
                                            <li>
                                                <button 
                                                    class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    on:click={() => goto('/user-logs/add-log')}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Add Log Entry
                                                </button>
                                            </li>
                                            <li>
                                                <button 
                                                    class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    on:click={() => handleDelete(log.id)}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </li>
                                            <li>
                                                <button 
                                                    class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    on:click={() => handleGenerateReport(log.id)}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Generate Report
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="space-y-6 h-[420px] overflow-y-auto p-4 bg-gray-50 rounded-xl">
                        <!--  Messages -->
                        <div class="flex flex-col space-y-6">
                            <!--  Received Message -->
                            <div class="flex items-start group">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                        {log.client_name?.[0] || 'C'}
                                    </div>
                                </div>
                                <div class="ml-4 w-full">
                                    <div class="bg-white rounded-2xl py-4 px-5 max-w-[75%] shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                                        <!-- Subject -->
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-semibold text-gray-900">{log.subject || "No Subject"}</h3>
                                            <!-- Options Menu -->
                                            <div class="relative">
                                                <button class="p-1 rounded-full hover:bg-gray-100 focus:outline-none" on:click|stopPropagation={(e) => toggleOptions(e, log.id)}>
                                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/>
                                                    </svg>
                                                </button>
                                                {#if activeOptionsId === log.id}
                                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                                    <ul class="py-1 text-sm text-gray-700">
                                                        <li>
                                                            <button class="block w-full text-left px-4 py-2 hover:bg-gray-100" on:click={() => handleReply(log.id)}>Reply</button>
                                                        </li>
                                                        <li>
                                                            <button class="block w-full text-left px-4 py-2 hover:bg-gray-100" on:click={() => handleConfidential(log.id)}>Mark as Confidential</button>
                                                        </li>
                                                    </ul>
                                                </div>
                                                {/if}
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        <p class="text-sm text-gray-700 leading-relaxed mb-3">{log.content || "No Content"}</p>
                                        
                                        <!-- File Attachment -->
                                        {#if log.attachment}
                                        <div class="flex items-center gap-2 mt-3 p-2.5 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors duration-200 cursor-pointer">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span class="text-xs font-medium text-gray-700">{log.attachment}</span>
                                            <!-- Placeholder for file size if available -->
                                            <!-- <span class="text-xs text-gray-400">(2.4 MB)</span> -->
                                            <button class="ml-auto p-1 hover:bg-gray-200 rounded-full transition-colors duration-200">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </button>
                                        </div>
                                        {/if}

                                        <!-- Logged By -->
                                        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100">
                                            <span class="text-xs text-gray-500">Logged by:</span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-medium text-gray-700">{log.log_by || "N/A"}</span>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500">Support User</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Timestamp -->
                                    <div class="flex items-center gap-2 mt-2 ml-1">
                                        <span class="text-xs text-gray-500">{new Date(log.created_at).toLocaleDateString()}</span>
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-500">{new Date(log.created_at).toLocaleTimeString()}</span>
                                    </div>
                                </div>
                            </div>

                            {#if loadingReplies}
                                <p>Loading replies...</p>
                            {:else if repliesError}
                                <p class="text-red-500">{repliesError}</p>
                            {:else}
                                {#each replies as reply (reply.logs_rep)}
                                    <!-- Sent Message -->
                                    <div class="flex items-start justify-end group">
                                        <div class="mr-4 w-full">
                                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl py-4 px-5 max-w-[75%] ml-auto shadow-sm hover:shadow-md transition-shadow duration-200">
                                                <!-- Subject -->
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="text-sm font-semibold text-white">Re: {reply.log_subject || "No Subject"}</h3>
                                                </div>
                                                
                                                <!-- Description -->
                                                <p class="text-sm text-white/90 leading-relaxed mb-3">{reply.logs_description || "No Content"}</p>

                                                <!-- Logged By -->
                                                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-blue-400/30">
                                                    <span class="text-xs text-blue-100">Replied by:</span>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs font-medium text-white">{reply.logged_by || "N/A"}</span>
                                                        <span class="text-xs text-blue-200">•</span>
                                                        <span class="text-xs text-blue-100">Support Team Lead</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Timestamp -->
                                            <div class="flex items-center gap-2 mt-2 mr-1 justify-end">
                                                <span class="text-xs text-gray-500">{formatDateTime(reply.created_at).date}</span>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500">{formatDateTime(reply.created_at).time}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white font-semibold shadow-md">
                                                {reply.logged_by?.[0] || 'A'}
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            {/if}
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="mt-4 border-t pt-4">
                        <div class="flex items-center gap-3">
                            <button class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </button>
                            <input 
                                type="text" 
                                placeholder="Type your message..." 
                                class="flex-1 border border-gray-300 rounded-full px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            />
                            <button class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-full hover:shadow-md transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>

  