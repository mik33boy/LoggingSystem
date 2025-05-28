import type { Theme } from '../stores/theme';

/**
 * Get the current theme from localStorage or system preference
 */
export function getInitialTheme(): Theme {
  if (typeof window === 'undefined') return 'light';
  
  const stored = localStorage.getItem('theme') as Theme;
  if (stored && (stored === 'light' || stored === 'dark')) {
    return stored;
  }
  
  // Check system preference
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  return prefersDark ? 'dark' : 'light';
}

/**
 * Apply theme classes to the document
 */
export function applyThemeToDocument(theme: Theme) {
  if (typeof document === 'undefined') return;
  
  const root = document.documentElement;
  const body = document.body;
  
  if (theme === 'dark') {
    root.classList.add('dark');
    body.classList.add('dark');
  } else {
    root.classList.remove('dark');
    body.classList.remove('dark');
  }
}

/**
 * Save theme preference to localStorage
 */
export function saveThemePreference(theme: Theme) {
  if (typeof localStorage === 'undefined') return;
  localStorage.setItem('theme', theme);
}

/**
 * Listen for system theme changes
 */
export function watchSystemTheme(callback: (theme: Theme) => void) {
  if (typeof window === 'undefined') return;
  
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  
  const handleChange = (e: MediaQueryListEvent) => {
    const theme: Theme = e.matches ? 'dark' : 'light';
    callback(theme);
  };
  
  mediaQuery.addEventListener('change', handleChange);
  
  // Return cleanup function
  return () => {
    mediaQuery.removeEventListener('change', handleChange);
  };
}
