import { writable } from 'svelte/store';
import { browser } from '$app/environment';

export type Theme = 'light' | 'dark';

// Create a writable store for the theme
function createThemeStore() {
  // Default to light theme
  const { subscribe, set, update } = writable<Theme>('light');

  return {
    subscribe,
    set,
    update,
    // Initialize theme from localStorage or system preference
    init: () => {
      if (!browser) return;
      
      const stored = localStorage.getItem('theme') as Theme;
      if (stored) {
        set(stored);
        applyTheme(stored);
      } else {
        // Check system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const systemTheme: Theme = prefersDark ? 'dark' : 'light';
        set(systemTheme);
        applyTheme(systemTheme);
      }
    },
    // Toggle between light and dark
    toggle: () => {
      update(currentTheme => {
        const newTheme: Theme = currentTheme === 'light' ? 'dark' : 'light';
        if (browser) {
          localStorage.setItem('theme', newTheme);
          applyTheme(newTheme);
        }
        return newTheme;
      });
    },
    // Set specific theme
    setTheme: (theme: Theme) => {
      set(theme);
      if (browser) {
        localStorage.setItem('theme', theme);
        applyTheme(theme);
      }
    }
  };
}

// Apply theme to document
function applyTheme(theme: Theme) {
  if (!browser) return;
  
  const root = document.documentElement;
  if (theme === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
}

export const themeStore = createThemeStore();
