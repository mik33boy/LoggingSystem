<script lang="ts">
    import { API_ENDPOINTS, apiRequest } from '$lib/api/config';
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';

    let log: any = null;
    let loading = true;
    let error: string | null = null;

    onMount(async () => {
        const logId = $page.url.searchParams.get('id');
        if (!logId) {
            error = 'No log ID provided';
            loading = false;
            return;
        }

        try {
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?id=${logId}`, {
                method: 'GET'
            });
            
            if (response.success) {
                log = response.data;
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

    function goBack() {
        goto('/user-logs');
    }
</script>

<div class="flex h-screen bg-gray-50">
    <div class="flex-1 flex flex-col overflow-hidden items-center justify-start">
        <div class="w-full px-2 py-1">
            <!-- Header Section -->
            <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-md mb-4 border border-gray-200 w-full">
                <div class="flex items-center gap-4">
                    <button 
                        on:click={goBack}
                        class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </button>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Log Details</h1>
                </div>
                <div class="flex items-center text-gray-600 text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
                        <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
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

            <!-- Content Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                {#if loading}
                    <div class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                    </div>
                {:else if error}
                    <div class="text-red-600 text-center py-8">{error}</div>
                {:else if log}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Basic Information</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.fullName || '--'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Timestamp</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.timestamp}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Type</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.type}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Direction</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.direction}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Communication Details -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Communication Details</h2>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">From / To</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.sender || log.recipient || '--'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Subject</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.subject || '--'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Details</label>
                                    <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{log.content || '--'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Confidential</label>
                                    <p class="mt-1 text-sm text-gray-900">{log.confidential ? 'Yes' : 'No'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {:else}
                    <div class="text-gray-600 text-center py-8">No log details found</div>
                {/if}
            </div>
        </div>
    </div>
</div>

  