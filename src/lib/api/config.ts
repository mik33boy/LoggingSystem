// API Configuration
const API_BASE_URL = 'http://localhost/LoggingSystem/backend/api';

export const API_ENDPOINTS = {
  AUTH: {
    REGISTER: `${API_BASE_URL}/auth/auth.php`,
    LOGIN: `${API_BASE_URL}/auth/auth.php`,
  },
  LOGS: `${API_BASE_URL}/logs/logs.php`,
  // Add other API endpoints here as needed
};

// API request helper function
export async function apiRequest(endpoint: string, options: RequestInit = {}) {
  // Get token from localStorage
  const token = localStorage.getItem('token');
  
  // Set default headers
  const headers = {
    'Content-Type': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
    ...(options.headers || {})
  };

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