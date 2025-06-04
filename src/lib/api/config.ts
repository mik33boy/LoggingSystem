// API Configuration
const API_BASE_URL = 'http://localhost/LoggingSystem/backend/api';

export const API_ENDPOINTS = {
  AUTH: {
    REGISTER: `${API_BASE_URL}/auth/auth.php`,
    LOGIN: `${API_BASE_URL}/auth/auth.php`,
    GET_ALL_USERS: `${API_BASE_URL}/auth/auth.php`,
  },
  LOGS: `${API_BASE_URL}/logs/logs.php`,
  USERS: `${API_BASE_URL}/users/users.php`,
  // Add other API endpoints here as needed
};

// API request helper function
export async function apiRequest(endpoint: string, options: RequestInit = {}) {
  // Get token from localStorage
  const token = localStorage.getItem('token');

  // Initialize headers object
  const headers: Record<string, string> = {
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      // Spread existing headers from options, ensuring keys and values are strings
      ...(options.headers ? Object.fromEntries(
          Object.entries(options.headers).map(([key, value]) => [
              key, String(value) // Convert values to string
          ])
      ) : {})
  };

  // Set Content-Type based on body, unless explicitly set in options.headers
  if (!(options.body instanceof FormData)) {
      if (!headers['Content-Type']) {
          headers['Content-Type'] = 'application/json';
      }
  } else {
      // If body is FormData, remove Content-Type header if it was explicitly set,
      // as the browser will set the correct multipart/form-data header.
      delete headers['Content-Type'];
  }

  try {
    console.log('Making request to:', endpoint);
    console.log('With headers:', headers);
    
    const response = await fetch(endpoint, {
      ...options,
      headers,
      credentials: 'include',
      mode: 'cors'
    });

    const data = await response.json();
    console.log('Response:', data);

    if (!response.ok) {
      throw new Error(data.error || `HTTP error! status: ${response.status}`);
    }

    return data;
  } catch (error: any) {
    console.error('API request failed:', error);
    throw new Error(error.message || 'Network error');
  }
} 