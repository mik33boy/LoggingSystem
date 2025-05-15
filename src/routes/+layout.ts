// Tauri doesn't have a Node.js server to do proper SSR
// so we will use adapter-static to prerender the app (SSG)
// See: https://v2.tauri.app/start/frontend/sveltekit/ for more info
import { browser } from '$app/environment';
import { goto } from '$app/navigation';
import type { LayoutLoad } from './$types';

export const prerender = false;
export const ssr = false;

export const load: LayoutLoad = ({ url }) => {
  // Check if the user is logged in
  const isLoggedIn = browser ? localStorage.getItem('token') !== null : false;
  
  // Login page accessible to everyone
  if (url.pathname === '/login') {
    return {};
  }
  
  // Redirect to login if not logged in (except for the root path which shows landing page)
  if (!isLoggedIn && url.pathname !== '/') {
    if (browser) {
      goto('/login');
    }
    return {};
  }
  
  // Return user data for authenticated routes
  if (isLoggedIn && browser) {
    try {
      const userData = JSON.parse(localStorage.getItem('user') || '{}');
      return {
        user: userData
      };
    } catch (e) {
      console.error('Failed to parse user data', e);
      return {};
    }
  }
  
  return {};
};
