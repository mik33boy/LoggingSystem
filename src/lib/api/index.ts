import { API_ENDPOINTS, apiRequest } from './config';

/**
 * API service for authentication and data operations
 */
export const API = {
  /**
   * Login user with username and password
   * @param username - The username
   * @param password - The password
   * @returns Promise with token and user data
   */
  async login(username: string, password: string) {
    const data = await apiRequest(API_ENDPOINTS.AUTH.LOGIN, {
      method: 'POST',
      body: JSON.stringify({ username, password })
    });
    
    return data;
  }
}; 