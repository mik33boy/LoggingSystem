/**
 * API Interface Module
 * Provides a centralized interface for communicating with the backend API
 * Handles authentication, error handling, and all CRUD operations
 */
import { browser } from '$app/environment';

// Base URL for all API requests - updated for proper XAMPP path
const API_BASE_URL = 'http://localhost/LoggingSystem/backend/api';

// TypeScript interface for HTTP request options
interface RequestOptions {
    method: string;
    headers: {
        'Content-Type': string;
        'Authorization'?: string;
    };
    body?: string;
}

// User model interface
export interface User {
    id: number;
    username: string;
    role: string;
}

// Response structure for login requests
export interface LoginResponse {
    token: string;
    user: User;
}

// Interface for filtering log entries
export interface LogFilters {
    direction?: string;
    type?: string;
    fromDate?: string;
    toDate?: string;
    search?: string;
    confidentiality?: string;
}

// Log entry data model
export interface Log {
    id?: number;
    direction: string;
    type: string;
    subject: string;
    content?: string;
    sender?: string;
    recipient?: string;
    timestamp: string;
    confidentiality_level?: string;
}

export const API = {
    /**
     * Generic request method that handles all API communication
     * @param endpoint - API endpoint path
     * @param method - HTTP method (GET, POST, PUT, DELETE)
     * @param data - Optional data to send with request
     * @returns Promise with typed response data
     */
    async request<T>(endpoint: string, method = 'GET', data: any = null): Promise<T> {
        const url = `${API_BASE_URL}/${endpoint}`;
        const options: RequestOptions = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        // Only add authentication token if in browser context
        if (browser && localStorage.getItem('token')) {
            options.headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
        }
        
        // Add request body for methods that need it
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        try {
            console.log(`Sending ${method} request to ${url} with data:`, data);
            
            const response = await fetch(url, options);
            
            const responseText = await response.text();
            console.log('Response status:', response.status);
            console.log('Response text:', responseText);
            
            // Parse JSON response, with error handling
            let responseData;
            try {
                responseData = JSON.parse(responseText);
            } catch (e) {
                throw new Error(`Invalid JSON response: ${responseText}`);
            }
            
            // Handle error responses
            if (!response.ok) {
                if (responseData.errors) {
                    throw new Error(`Validation error: ${JSON.stringify(responseData.errors)}`);
                } else if (responseData.error) {
                    throw new Error(responseData.error);
                } else {
                    throw new Error('Request failed with status ' + response.status);
                }
            }
            
            return responseData;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // ----- Authentication Methods -----
    
    /**
     * User login
     * @param username - User's username 
     * @param password - User's password
     * @returns Promise with login response (token and user data)
     */
    login(username: string, password: string): Promise<LoginResponse> {
        return this.request('auth.php', 'POST', { username, password });
    },
    
    /**
     * User logout
     * Removes token from localStorage
     */
    logout(): void {
        if (browser) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        }
    },
    
    /**
     * Get current authenticated user info
     * @returns Promise with user data
     */
    getCurrentUser(): Promise<{success: boolean, user: User}> {
        return this.request('user.php');
    },
    
    /**
     * User registration
     * @param username - New user's username
     * @param email - New user's email
     * @param password - New user's password
     * @returns Promise with registration response
     */
    register(username: string, email: string, password: string): Promise<{id: number, message: string}> {
        return this.request('register.php', 'POST', { username, email, password });
    },
    
    // ----- Log Management Methods -----
    
    /**
     * Get filtered logs
     * @param filters - Optional filters to apply
     * @returns Promise with array of logs
     */
    getLogs(filters: LogFilters = {}): Promise<Log[]> {
        const query = new URLSearchParams(filters as Record<string, string>).toString();
        return this.request(`logs.php?${query}`);
    },
    
    /**
     * Get a specific log by ID
     * @param id - Log ID
     * @returns Promise with log data
     */
    getLog(id: number): Promise<Log> {
        return this.request(`logs.php?id=${id}`);
    },
    
    /**
     * Create a new log entry
     * @param log - Log data
     * @returns Promise with creation response
     */
    createLog(log: Log): Promise<{id: number, message: string}> {
        return this.request('logs.php', 'POST', log);
    },
    
    /**
     * Update an existing log
     * @param id - Log ID to update
     * @param log - Updated log data
     * @returns Promise with update response
     */
    updateLog(id: number, log: Log): Promise<{message: string}> {
        return this.request(`logs.php?id=${id}`, 'PUT', log);
    },
    
    /**
     * Delete a log
     * @param id - Log ID to delete
     * @returns Promise with deletion response
     */
    deleteLog(id: number): Promise<{message: string}> {
        return this.request(`logs.php?id=${id}`, 'DELETE');
    },
    
    // ----- User Management Methods (Admin Only) -----
    
    /**
     * Get all users
     * @returns Promise with array of users
     */
    getUsers(): Promise<User[]> {
        return this.request('users.php');
    },
    
    /**
     * Create a new user (admin function)
     * @param user - User data
     * @returns Promise with creation response
     */
    createUser(user: {username: string, password: string, role: string}): Promise<{id: number, message: string}> {
        return this.request('users.php', 'POST', user);
    },
    
    /**
     * Update a user
     * @param id - User ID to update
     * @param user - Updated user data
     * @returns Promise with update response
     */
    updateUser(id: number, user: {username?: string, password?: string, role?: string}): Promise<{message: string}> {
        return this.request(`users.php?id=${id}`, 'PUT', user);
    },
    
    /**
     * Delete a user
     * @param id - User ID to delete
     * @returns Promise with deletion response
     */
    deleteUser(id: number): Promise<{message: string}> {
        return this.request(`users.php?id=${id}`, 'DELETE');
    }
}; 