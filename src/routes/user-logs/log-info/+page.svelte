<script lang="ts">
    import { API_ENDPOINTS, apiRequest } from '$lib/api/config';
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';
    import DeleteConfirmationModal from '$components/DeleteConfirmationModal.svelte';
    import AlertModal from '$components/AlertModal.svelte';

    let log: any = null;
    let conversationLogs: any[] = [];
    let loading = true;
    let error: string | null = null;
    let selectedDate: string = '';
    let activeOptionsId: string | null = null;
    let messageInput: string = '';
    let isReplying: boolean = false;
    let isSettingConfidential: boolean = false;

    // Custom Alert Modal State
    let showAlertModal: boolean = false;
    let alertMessage: string = '';
    let alertType: 'success' | 'error' | 'info' = 'info';

    // Modal state
    let showModal = false;
    let clientName = '';
    let commType = '';
    let otherType = '';
    let direction = '';
    let fromTo = '';
    let subject = '';
    let details = '';
    let confidential = false;
    let attachmentName = '';
    let attachments: File[] = [];
    let dateTime = '';

    // Delete Modal State
    let showDeleteModal = false;
    let logToDeleteId: string | null = null;

    // Share Modal State
    let showShareModal = false;
    let authorizedUsers: string[] = [];
    let newUserEmail = '';
    let allUsers: string[] = [];
    let filteredUsers: string[] = [];
    let showUserSuggestions = false;

    onMount(async () => {
        const logId = localStorage.getItem('logId') || $page.params.id;
        console.log('Fetching conversation logs with initial ID:', logId);
        if (!logId) {
            error = 'Log ID not provided';
            loading = false;
            return;
        }

        try {
            // Fetch main logs related to the client_name of the provided logId
            const logsResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogMessage&log_id=${logId}`);

            // Fetch replies for the provided logId
            const repliesResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogReplies&log_id=${logId}`);

            if (logsResponse.success && repliesResponse.success) {
                // Merge the two arrays and sort by created_at timestamp
                const allMessages = [...logsResponse.data, ...repliesResponse.data];
                allMessages.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());

                conversationLogs = allMessages;

                // Find the specific log that was originally clicked (optional but useful)
                // Search in the original logsResponse data to ensure we find the correct parent log details
                log = logsResponse.data.find((l: any) => l.log_id === logId);

                if (!log && conversationLogs.length > 0) {
                     // If the specific log wasn't found but others for the client were, default to the first one from the merged list
                     // Note: This might pick a reply if it's the earliest, consider if this is desired behavior
                     log = conversationLogs[0];
                }

                if (!log || conversationLogs.length === 0) {
                    error = 'No logs or replies found for this log ID or client name';
                    loading = false;
                    return;
                }
            } else {
                error = (logsResponse.error || repliesResponse.error) || 'Failed to fetch conversation details or replies';
            }
        } catch (err: any) {
            error = err.message || 'Error fetching conversation details';
            console.error('Error fetching conversation details:', err);
        } finally {
            loading = false;
        }
    });

    function toggleOptions(e: Event, id: string) {
        e.stopPropagation();
        if (activeOptionsId === id) {
            activeOptionsId = null;
        }
        // Close other options if open
        if (id !== 'conversation-options') activeOptionsId = null;
        if (id !== 'message-options') activeOptionsId = null;
        // Add other option IDs here if needed
        if (activeOptionsId !== id) {
             activeOptionsId = id;
         }
    }

    // Function to close any open options menu when clicking elsewhere
    function closeOptions() {
        activeOptionsId = null;
    }
    // Add a click event listener to the window to close options
    // This needs to be done after the component is mounted
    onMount(() => {
        window.addEventListener('click', closeOptions);
        // Clean up the event listener when the component is destroyed
        return () => {
            window.removeEventListener('click', closeOptions);
        };
    });

    function handleReply(logEntry: any) {
        console.log(`Replying to message with id: ${logEntry.id}`);
        isReplying = true;
        // Scroll to the message input area
        const inputArea = document.getElementById('message-input-area');
        if (inputArea) {
            inputArea.scrollIntoView({ behavior: 'smooth' });
        }
    }

    async function handleConfidential(logEntry: any) {
        if (!logEntry || !logEntry.log_id) return;

        const isCurrentlyPrivate = logEntry.confidentiality_level === 'private';
        const newConfidentialityLevel = isCurrentlyPrivate ? 'public' : 'private';
        const actionText = isCurrentlyPrivate ? 'Set as Public' : 'Set as Confidential';

        console.log(`Attempting to set log with log_id: ${logEntry.log_id} to ${newConfidentialityLevel}`);
        try {
            if (isSettingConfidential) {
                console.log('Confidentiality update is already in progress.');
                return;
            }

            isSettingConfidential = true;

            // Prepare the body for the API request
            const requestBody: any = { log_id: logEntry.log_id, confidentiality_level: newConfidentialityLevel };

            // If setting to private and current user email was fetched, include it in authorize_users
            if (newConfidentialityLevel === 'private') {
                // Add current user's email to the existing authorized users, or just use current user's email if none exist
                const existingUsers = logEntry.authorize_users ? logEntry.authorize_users.split(',').filter((email: string) => email.trim() !== '') : [];
                const updatedUsers = new Set([...existingUsers, 'current_user_email']); // Use a Set to avoid duplicates
                requestBody.authorize_users = Array.from(updatedUsers).join(',');
            } else {
                // If setting to public, ensure authorize_users is null in the request body
                 requestBody.authorize_users = null;
            }


            // Call the backend API to set the confidentiality_level and authorize_users field
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?action=setConfidential`, {
                method: 'PUT',
                body: JSON.stringify(requestBody)
            });

            if (response.success) {
                console.log(`Log marked as ${newConfidentialityLevel} successfully.`);
                // Update the local log object to reflect the change
                logEntry.confidentiality_level = newConfidentialityLevel;
                // Show success alert
                alertMessage = `Conversation marked as ${newConfidentialityLevel}.`;
                alertType = 'success';
                showAlertModal = true;
            } else {
                console.error(`Failed to mark log as ${newConfidentialityLevel}:`, response.error);
                // Show error alert
                alertMessage = `Failed to mark conversation as ${newConfidentialityLevel}: ` + (response.error || 'Unknown error');
                alertType = 'error';
                showAlertModal = true;
            }
        } catch (err: any) {
            console.error(`Error marking log as ${newConfidentialityLevel}:`, err);
            // Show error alert
            alertMessage = `Error marking conversation as ${newConfidentialityLevel}: ` + (err.message || 'Network error');
            alertType = 'error';
            showAlertModal = true;
        } finally {
            isSettingConfidential = false;
        }
    }

    // Function to open the delete confirmation modal
    function openDeleteModal(id: string) {
        logToDeleteId = id;
        showDeleteModal = true;
    }

    // Function to close the delete confirmation modal
    function closeDeleteModal() {
        showDeleteModal = false;
        logToDeleteId = null;
    }

    // Function to handle the delete confirmation and perform the deletion
    async function confirmDelete() {
        if (!logToDeleteId) return;

        try {
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?id=${logToDeleteId}`, {
                method: 'DELETE'
            });

            if (response.success) {
                conversationLogs = conversationLogs.filter(item => item.id !== logToDeleteId);
                if (log && log.id === logToDeleteId) {
                    log = null;
                }
                 goto('/user-logs');
                 closeDeleteModal();
            } else {
                 // Show error alert
                 alertMessage = 'Failed to delete log';
                 alertType = 'error';
                 showAlertModal = true;
            }
        } catch (err) {
            console.error('Error deleting log:', err);
             // Show error alert
             alertMessage = 'Error deleting log: ' + ((err instanceof Error) ? err.message : String(err));
             alertType = 'error';
             showAlertModal = true;
        }
    }

    function formatDateTime(dateTimeString: string): { date: string, time: string } {
        const date = new Date(dateTimeString);
        const optionsDate: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const optionsTime: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
        return {
            date: date.toLocaleDateString(undefined, optionsDate),
            time: date.toLocaleTimeString(undefined, optionsTime)
        };
    }

    function clearReply() {
        isReplying = false;
        messageInput = '';
    }

    async function sendReply() {
        if (!messageInput.trim() || !log) {
            return;
        }

        const replyData = {
            log_id: log.log_id,
            content: messageInput,
            subject: `Re: ${log.subject || 'No Subject'}`,
            client_name: log.client_name,
            client_email: log.sender
        };

        try {
            loading = true;
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?action=addReply`, {
                method: 'POST',
                body: JSON.stringify(replyData)
            });

            if (response.success) {
                console.log('Reply log entry created successfully:', response.data);
                messageInput = '';
                isReplying = false;
                if (log && log.log_id) {
                     const refreshLogsResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogMessage&log_id=${log.log_id}`);
                     const refreshRepliesResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogReplies&log_id=${log.log_id}`);

                    if (refreshLogsResponse.success && refreshRepliesResponse.success) {
                         const allMessages = [...refreshLogsResponse.data, ...refreshRepliesResponse.data];
                        allMessages.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
                        conversationLogs = allMessages;
                    } else {
                        console.error('Failed to refresh conversation logs after sending reply:', refreshLogsResponse.error || refreshRepliesResponse.error);
                    }
                }
            } else {
                console.error('Failed to send reply log entry:', response.error);
                // Show error alert
                alertMessage = 'Failed to send reply: ' + (response.error || 'Unknown error');
                alertType = 'error';
                showAlertModal = true;
            }
        } catch (err: any) {
            console.error('Error sending reply log entry:', err);
            // Show error alert
            alertMessage = 'Error sending reply: ' + (err.message || 'Network error');
            alertType = 'error';
            showAlertModal = true;
        } finally {
            loading = false;
        }
    }

    function closeModal() {
        showModal = false;
        clientName = '';
        commType = '';
        otherType = '';
        direction = '';
        fromTo = '';
        subject = '';
        details = '';
        confidential = false;
        attachments = [];
        attachmentName = '';
        dateTime = '';
    }

    function openModal() {
        if (log) {
            clientName = log.client_name || '';
            direction = 'Incoming'; // Default to Incoming when adding from log details
            fromTo = log.sender || ''; // Default sender to the log's sender
        }
        // Set current date and time in the format required by datetime-local input (YYYY-MM-DDThh:mm)
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        dateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        showModal = true;
    }

    function handleFileChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files) {
            attachments = Array.from(input.files);
            attachmentName = attachments.map(file => file.name).join(', ');
        }
    }

    async function handleSubmit(event: Event) {
        event.preventDefault();
        console.log('Add Log Entry Form submitted:', {
            clientName,
            commType: commType === 'other' ? otherType : commType,
            direction,
            fromTo,
            subject,
            details,
            confidential,
            attachments,
            dateTime
        });

        const formData = new FormData();
        formData.append('direction', direction);
        formData.append('type', commType === 'other' ? otherType : commType);
        formData.append('subject', subject);
        formData.append('timestamp', new Date(dateTime).toISOString());
        formData.append('content', details);
        formData.append('sender', direction === 'Incoming' ? fromTo : '');
        formData.append('recipient', direction === 'Outgoing' ? fromTo : '');
        formData.append('client_name', clientName);
        formData.append('confidential', confidential ? 'true' : 'false');
        formData.append('status', 'active');

        // Append each file to the FormData with unique keys
        attachments.forEach((file, index) => {
            formData.append(`attachment_${index}`, file);
        });

        try {
            loading = true;
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}`, {
                method: 'POST',
                body: formData
            });

            if (response.success) {
                console.log('New log entry created successfully:', response.data);
                closeModal();
                // After adding, re-fetch all logs and replies to update the conversation view
                if (log && log.log_id) {
                    const refreshLogsResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogMessage&log_id=${log.log_id}`);
                    const refreshRepliesResponse = await apiRequest(`${API_ENDPOINTS.LOGS}?action=getLogReplies&log_id=${log.log_id}`);

                    if (refreshLogsResponse.success && refreshRepliesResponse.success) {
                        const allMessages = [...refreshLogsResponse.data, ...refreshRepliesResponse.data];
                        allMessages.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
                        conversationLogs = allMessages;
                    } else {
                        console.error('Failed to refresh conversation logs after adding entry:', refreshLogsResponse.error || refreshRepliesResponse.error);
                    }
                }
            } else {
                console.error('Failed to add log entry:', response.error);
                alertMessage = 'Failed to add log entry: ' + (response.error || 'Unknown error');
                alertType = 'error';
                showAlertModal = true;
            }
        } catch (err: any) {
            console.error('Error adding log entry:', err);
            alertMessage = 'Error adding log entry: ' + (err.message || 'Network error');
            alertType = 'error';
            showAlertModal = true;
        } finally {
            loading = false;
        }
    }

    // Function to fetch all users
    async function fetchAllUsers() {
        try {
            const response = await apiRequest(`${API_ENDPOINTS.AUTH.GET_ALL_USERS}?action=getAllUsers`);
            if (response.success) {
                allUsers = response.data.map((user: any) => user.email);
            }
        } catch (err) {
            console.error('Error fetching users:', err);
        }
    }

    // Function to filter users based on input
    function filterUsers(input: string) {
        if (!input.trim()) {
            filteredUsers = [];
            showUserSuggestions = false;
            return;
        }
        
        const searchTerm = input.toLowerCase();
        filteredUsers = allUsers.filter(email => 
            email.toLowerCase().includes(searchTerm) && 
            !authorizedUsers.includes(email)
        );
        showUserSuggestions = filteredUsers.length > 0;
    }

    // Function to select a user from suggestions
    function selectUser(email: string) {
        newUserEmail = email;
        showUserSuggestions = false;
    }

    // Modify openShareModal to fetch users
    async function openShareModal() {
        if (log && log.authorize_users) {
            authorizedUsers = log.authorize_users.split(',').filter((email: string) => email.trim() !== '');
        } else {
            authorizedUsers = [];
        }
        await fetchAllUsers();
        showShareModal = true;
    }

    // Add input handler for email field
    function handleEmailInput(e: Event) {
        const input = (e.target as HTMLInputElement).value;
        newUserEmail = input;
        filterUsers(input);
    }

    // Function to close share modal
    function closeShareModal() {
        showShareModal = false;
        newUserEmail = '';
    }

    // Function to add authorized user
    async function addAuthorizedUser() {
        if (!newUserEmail.trim() || !log) return;

        try {
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?action=setConfidential`, {
                method: 'PUT',
                body: JSON.stringify({
                    log_id: log.log_id,
                    confidentiality_level: 'private',
                    authorize_users: [...authorizedUsers, newUserEmail].join(',')
                })
            });

            if (response.success) {
                authorizedUsers = [...authorizedUsers, newUserEmail];
                newUserEmail = '';
            } else {
                alertMessage = 'Failed to add user: ' + (response.error || 'Unknown error');
                alertType = 'error';
                showAlertModal = true;
            }
        } catch (err: any) {
            alertMessage = 'Error adding user: ' + (err.message || 'Network error');
            alertType = 'error';
            showAlertModal = true;
        }
    }

    // Function to remove authorized user
    async function removeAuthorizedUser(email: string) {
        if (!log) return;

        try {
            const updatedUsers = authorizedUsers.filter(user => user !== email);
            const response = await apiRequest(`${API_ENDPOINTS.LOGS}?action=setConfidential`, {
                method: 'PUT',
                body: JSON.stringify({
                    log_id: log.log_id,
                    confidentiality_level: 'private',
                    authorize_users: updatedUsers.join(',')
                })
            });

            if (response.success) {
                authorizedUsers = updatedUsers;
            } else {
                alertMessage = 'Failed to remove user: ' + (response.error || 'Unknown error');
                alertType = 'error';
                showAlertModal = true;
            }
        } catch (err: any) {
            alertMessage = 'Error removing user: ' + (err.message || 'Network error');
            alertType = 'error';
            showAlertModal = true;
        }
    }
</script>

<div class="flex h-screen bg-gray-50">
    <div class="flex-1 flex flex-col overflow-hidden items-center justify-start">
        <div class="w-full px-2 py-1">
            <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-md mb-4 border border-gray-200 w-full">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Log Details</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center text-gray-600 text-sm">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">
                            <svg class="w-4 h-4 mr-1 text-ggray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2h2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 012-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
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

            {#if loading && conversationLogs.length === 0 && !error}
                <p>Loading conversation history...</p>
            {:else if error}
                <p class="text-red-500">Error: {error}</p>
            {:else if conversationLogs && conversationLogs.length > 0}
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-md font-semibold text-gray-800">Client Information</h2>
                    </div>
                     <!-- Use the 'log' variable which should hold the initial log details -->
                    <p class="text-sm text-gray-700">Client Name: {log?.client_name || "N/A"}</p>
                    <p class="text-sm text-gray-700">Client Email: {log?.sender || "N/A"}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 flex flex-col h-[700px]">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-md font-semibold text-gray-800">Conversation History</h2>
                        <div class="flex items-center gap-4">
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
                                                    on:click={openModal}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Add Log Entry
                                                </button>
                                            </li>
                                             <!-- Delete button should likely delete the *conversation* (all logs/replies for this client), not just a single log entry by its potentially non-unique ID in the merged list. Requires backend change. -->
                                             <!-- For now, keeping the current delete that targets a log entry by ID in the logs table -->
                                            <li>
                                                <button
                                                    class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                     on:click={() => openDeleteModal(log.log_id)}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete Conversation
                                                </button>
                                            </li>
                                            <li>
                                                <button
                                                    class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"

                                                >
                                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Generate Report
                                                </button>
                                            </li>
                                             <!-- Confidentiality on a single message level needs re-evaluation if merging logs and replies -->
                                            <li>
                                                <button class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                     on:click|stopPropagation={() => handleConfidential(log)}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                    {log?.confidentiality_level === 'private' ? 'Set as Public' : 'Set as Confidential'}
                                                </button>
                                            </li>
                                            {#if log?.confidentiality_level === 'private'}
                                            <li>
                                                <button class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100"
                                                     on:click|stopPropagation={openShareModal}
                                                >
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                    </svg>
                                                    Share Access
                                                </button>
                                            </li>
                                            {/if}
                                        </ul>
                                    </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 overflow-y-auto p-4 bg-gray-50 rounded-xl flex-1">
                        {#each conversationLogs as message (message.id)}
                             <!-- Render based on the direction property from either logs or logs_replies (aliased) -->
                            {#if message.direction === 'Incoming'}
                                <div class="flex items-start group">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold shadow-md">
                                            {message.client_name?.[0] || 'C'}
                                        </div>
                                    </div>
                                    <div class="ml-4 w-full">
                                        <div class="bg-white rounded-2xl py-4 px-5 max-w-[75%] shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                                            <div class="flex items-center justify-between mb-2">
                                                 <!-- Display subject if available, otherwise show Type -->
                                                 <h3 class="text-sm font-semibold text-gray-900">{message.subject || message.type || "No Subject"}</h3>
                                                 <!-- Reply button only for Incoming messages -->
                                                 {#if message.direction === 'Incoming'}
                                                    <div class="relative">
                                                        <button class="flex items-center w-full text-left px-4 py-2 hover:bg-gray-100" on:click={() => handleReply(message)}>
                                                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4-4m0 0l-4-4m4 4H9a7 7 0 00-7 7v2" />
                                                            </svg>
                                                            Reply
                                                        </button>
                                                    </div>
                                                {/if}
                                            </div>

                                            <p class="text-sm text-gray-700 leading-relaxed mb-3 whitespace-pre-wrap break-words">{message.content || "No Content"}</p>

                                            {#if message.attachment}
                                            <div class="space-y-2 mt-3">
                                                {#each message.attachment.split(',') as file}
                                                <div class="flex items-center gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors duration-200 cursor-pointer">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                    <span class="text-xs font-medium text-gray-700">{file.split('_').slice(1).join('_')}</span>
                                                    <button class="ml-auto p-1 hover:bg-gray-200 rounded-full transition-colors duration-200" on:click={async () => {
                                                        try {
                                                            const response = await fetch(`${API_ENDPOINTS.LOGS}?action=downloadFile&filename=${encodeURIComponent(file)}`, {
                                                                method: 'GET',
                                                                headers: {
                                                                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                                                                }
                                                            });
                                                            
                                                            if (!response.ok) {
                                                                throw new Error('Failed to download file');
                                                            }
                                                            
                                                            const blob = await response.blob();
                                                            const url = window.URL.createObjectURL(blob);
                                                            const link = document.createElement('a');
                                                            link.href = url;
                                                            link.download = file.split('_').slice(1).join('_');
                                                            document.body.appendChild(link);
                                                            link.click();
                                                            document.body.removeChild(link);
                                                            window.URL.revokeObjectURL(url);
                                                        } catch (err) {
                                                            console.error('Error downloading file:', err);
                                                            alertMessage = 'Error downloading file: ' + (err instanceof Error ? err.message : 'Unknown error');
                                                            alertType = 'error';
                                                            showAlertModal = true;
                                                        }
                                                    }}>
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                {/each}
                                            </div>
                                            {/if}

                                            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100">
                                                <span class="text-xs text-gray-500">Logged by:</span>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-medium text-gray-700">{message.log_by || "N/A"}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 mt-2 ml-1">
                                            <span class="text-xs text-gray-500">{formatDateTime(message.created_at).date}</span>
                                            <span class="text-xs text-gray-400">•</span>
                                            <span class="text-xs text-gray-500">{formatDateTime(message.created_at).time}</span>
                                        </div>
                                    </div>
                                </div>
                            {:else}
                                 <!-- Render as outgoing -->
                                <div class="flex items-start justify-end group">
                                    <div class="mr-4 w-full">
                                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl py-4 px-5 max-w-[75%] ml-auto shadow-sm hover:shadow-md transition-shadow duration-200">
                                            <div class="flex items-center justify-between mb-2">
                                                 <!-- Display subject if available, otherwise show Type -->
                                                 <h3 class="text-sm font-semibold text-white">{message.subject || message.type || "No Subject"}</h3>
                                            </div>

                                            <p class="text-sm text-white/90 leading-relaxed mb-3 whitespace-pre-wrap break-words">{message.content || "No Content"}</p>

                                            {#if message.attachment}
                                            <div class="space-y-2 mt-3">
                                                {#each message.attachment.split(',') as file}
                                                <div class="flex items-center gap-2 p-2.5 bg-blue-400/30 rounded-lg border border-blue-300/50 hover:bg-blue-400/50 transition-colors duration-200 cursor-pointer">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                    <span class="text-xs font-medium text-white/90">{file.split('_').slice(1).join('_')}</span>
                                                    <button class="ml-auto p-1 hover:bg-blue-700 rounded-full transition-colors duration-200" on:click={async () => {
                                                        try {
                                                            const response = await fetch(`${API_ENDPOINTS.LOGS}?action=downloadFile&filename=${encodeURIComponent(file)}`, {
                                                                method: 'GET',
                                                                headers: {
                                                                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                                                                }
                                                            });
                                                            
                                                            if (!response.ok) {
                                                                throw new Error('Failed to download file');
                                                            }
                                                            
                                                            const blob = await response.blob();
                                                            const url = window.URL.createObjectURL(blob);
                                                            const link = document.createElement('a');
                                                            link.href = url;
                                                            link.download = file.split('_').slice(1).join('_');
                                                            document.body.appendChild(link);
                                                            link.click();
                                                            document.body.removeChild(link);
                                                            window.URL.revokeObjectURL(url);
                                                        } catch (err) {
                                                            console.error('Error downloading file:', err);
                                                            alertMessage = 'Error downloading file: ' + (err instanceof Error ? err.message : 'Unknown error');
                                                            alertType = 'error';
                                                            showAlertModal = true;
                                                        }
                                                    }}>
                                                        <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                {/each}
                                            </div>
                                            {/if}

                                            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-blue-400/30">
                                                <span class="text-xs text-blue-100">Logged by:</span>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-medium text-white">{message.log_by || "N/A"}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 mt-2 mr-1 justify-end">
                                            <span class="text-xs text-gray-500">{formatDateTime(message.created_at).date}</span>
                                            <span class="text-xs text-gray-400">•</span>
                                            <span class="text-xs text-gray-500">{formatDateTime(message.created_at).time}</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                         <!-- Use log_by for the initial letter, assuming it's available from both logs and logs_replies -->
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white font-semibold shadow-md">
                                            {message.log_by?.[0] || 'U'}
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        {/each}
                    </div>

                    {#if isReplying && log}
                     <!-- The reply bar indicates replying to the overall log/conversation, not a specific message -->
                    <div class="flex items-center justify-between text-sm text-white bg-gray-700 p-2 mb-4 rounded-md">
                        <span>Replying to: {log.subject || 'No Subject'}</span>
                        <button class="text-gray-300 hover:text-white" on:click={clearReply}>×</button>
                    </div>
                    {/if}
                    <div id="message-input-area" class="mt-4 border-t pt-4">
                        <div class="flex items-center gap-3">
                            <button class="p-2 hover:bg-gray-100 rounded-full transition-colors duration-200" on:click={() => alert('Attachment upload not implemented.')}>
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </button>
                            <textarea
                                placeholder="Type your message..."
                                class="flex-1 border border-gray-300 rounded-xl px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none min-h-[50px] max-h-[150px]"
                                bind:value={messageInput}
                                rows={messageInput.split('\n').length > 3 ? 3 : 1}
                            ></textarea>
                            <button
                                class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-full hover:shadow-md transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                                on:click={sendReply}
                                disabled={!messageInput.trim() || loading}
                            >
                                {#if loading}
                                    Sending...
                                {:else}
                                    <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                                    </svg>
                                {/if}
                            </button>
                        </div>
                    </div>
                </div>
             {:else}
                <p>No conversation history found for this log ID.</p>
            {/if}
        </div>
    </div>
</div>

{#if showModal}
    <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-40 animate-fadeIn">
        <div class="modal-content bg-white p-8 rounded-2xl w-full max-w-[1000px] relative shadow-2xl border border-blue-100 max-h-[90vh] overflow-y-auto">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl font-bold transition-colors" on:click={closeModal}>&times;</button>
            <h2 class="text-2xl font-extrabold mb-1 text-blue-700 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Log Entry
            </h2>
            <p class="text-gray-500 mb-6 text-sm">Fill in the details below to add a new communication log.</p>
            <form on:submit|preventDefault={handleSubmit} class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Client Name</label>
                    <input type="text" bind:value={clientName} class="form-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" placeholder="Name of the client" required />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-700 mb-2 border-b pb-1 border-gray-200">Communication Details</h3>
                    <div class="grid grid-cols-1 gap-4 mt-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                            <select bind:value={commType} class="form-select w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" required>
                                <option value="">Select Type</option>
                                <option value="Email">Email</option>
                                <option value="Fax">Fax</option>
                                <option value="Call">Call</option>
                                <option value="other">Other</option>
                            </select>
                            {#if commType === 'other'}
                                <input type="text" bind:value={otherType} class="form-input w-full mt-2 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" placeholder="Specify other type" required />
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Direction <span class="text-red-500">*</span></label>
                            <select bind:value={direction} class="form-select w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" required>
                                <option value="">Select Direction</option>
                                <option value="Incoming">Incoming</option>
                                <option value="Outgoing">Outgoing</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">From / To <span class="text-red-500">*</span></label>
                            <input type="text" bind:value={fromTo} class="form-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" placeholder="Email or phone" required />
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-700 mb-2 border-b pb-1 border-gray-200">Message Details</h3>
                    <div class="grid grid-cols-1 gap-4 mt-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Subject <span class="text-red-500">*</span></label>
                            <input type="text" bind:value={subject} class="form-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" placeholder="Subject of the communication" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Details <span class="text-red-500">*</span></label>
                            <textarea bind:value={details} class="form-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" rows="3" placeholder="Enter details here..." required></textarea>
                        </div>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" bind:checked={confidential} id="confidential" class="accent-blue-600" />
                            <label for="confidential" class="block text-sm font-medium">Mark as Confidential</label>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-700 mb-2 border-b pb-1 border-gray-200">Attachment</h3>
                    <div class="mt-2">
                        <label class="block text-sm font-medium mb-1" for="attachment">Upload Files</label>
                        <input
                            id="attachment"
                            type="file"
                            multiple
                            class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-300"
                            on:change={(e) => handleFileChange(e)}
                        />
                        {#if attachmentName}
                            <div class="mt-2 space-y-1">
                                {#each attachments as file}
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <span>{file.name}</span>
                                        <span class="text-gray-400">({(file.size / 1024).toFixed(1)} KB)</span>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-700 mb-2 border-b pb-1 border-gray-200">Date & Time</h3>
                    <div class="mt-2">
                        <label class="block text-sm font-medium mb-1">Date & Time <span class="text-red-500">*</span></label>
                        <input type="datetime-local" bind:value={dateTime} class="form-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500 transition" required />
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-8">
                    <button type="button" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition" on:click={closeModal}>Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold shadow transition">Add Log</button>
                </div>
            </form>
        </div>
    </div>
{/if}

{#if showDeleteModal}
    <DeleteConfirmationModal
        show={showDeleteModal}
        message="Are you sure you want to delete this log entry and all its replies? This action cannot be undone."
        on:confirm={confirmDelete}
        on:cancel={closeDeleteModal}
    />
{/if}

<!-- Custom Alert Modal -->
<AlertModal show={showAlertModal} message={alertMessage} type={alertType} on:close={() => showAlertModal = false} />

<!-- Add Share Modal -->
{#if showShareModal}
    <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-40 animate-fadeIn">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full mx-4 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl font-bold transition-colors" on:click={closeShareModal}>&times;</button>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Share Access</h3>
            
            <!-- Add User Form -->
            <div class="mb-6">
                <div class="flex gap-2 relative">
                    <div class="flex-1 relative">
                        <input
                            type="email"
                            bind:value={newUserEmail}
                            on:input={handleEmailInput}
                            placeholder="Enter user email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-500"
                        />
                        {#if showUserSuggestions}
                            <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                {#each filteredUsers as email}
                                    <button
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                        on:click={() => selectUser(email)}
                                    >
                                        {email}
                                    </button>
                                {/each}
                            </div>
                        {/if}
                    </div>
                    <button
                        on:click={addAuthorizedUser}
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Add
                    </button>
                </div>
            </div>

            <!-- Authorized Users List -->
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Authorized Users:</h4>
                {#if authorizedUsers.length === 0}
                    <p class="text-sm text-gray-500">No users have access yet</p>
                {:else}
                    {#each authorizedUsers as email}
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-700">{email}</span>
                            <button
                                on:click={() => removeAuthorizedUser(email)}
                                class="p-1 text-red-500 hover:text-red-700 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    {/each}
                {/if}
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition"
                    on:click={closeShareModal}
                >
                    Close
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
  }
  .animate-fadeIn {
    animation: fadeIn 0.25s cubic-bezier(0.4,0,0.2,1);
  }
</style>

  