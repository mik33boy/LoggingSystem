<script lang="ts">
<<<<<<< Updated upstream
  import "../app.css"; // or "./app.css" depending on file location
</script>

<slot />
=======
	import '../app.css';
	import 'flowbite/dist/flowbite.css';
	import { onMount } from 'svelte';
	import { themeStore } from '$lib/stores/theme';

	// Initialize theme on mount
	onMount(() => {
		themeStore.init();
	});
</script>
<div class="flex h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
		<main class="flex-1 overflow-y-auto p-8">
			<slot />
		</main>
</div>
>>>>>>> Stashed changes
