// API Configuration
const API_BASE_URL = 'http://localhost/LoggingSystem/backend/api';

export const API_ENDPOINTS = {
  AUTH: {
    REGISTER: `${API_BASE_URL}/auth/auth.php`,
    LOGIN: `${API_BASE_URL}/auth/auth.php`,
  },
  // Add other API endpoints here as needed
};

// API request helper function
export async function apiRequest(endpoint: string, options: RequestInit = {}) {
  const defaultOptions: RequestInit = {
    headers: {
      'Content-Type': 'application/json',
    },
  };

  const response = await fetch(endpoint, {
    ...defaultOptions,
    ...options,
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.error || 'API request failed');
  }

  return data;
} 