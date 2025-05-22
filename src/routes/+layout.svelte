<script lang="ts">
	import '../app.css';
	import 'flowbite/dist/flowbite.css';
	import UserSidebar from './user-sidebar/+page.svelte';
	import UserHeader from './user-header/+page.svelte';
</script>

<div class="flex h-screen">
	<UserSidebar class="h-screen" />
	<div class="flex-1 flex flex-col overflow-hidden">
		<UserHeader />
		<main class="flex-1 overflow-y-auto p-8">
			<slot />
		</main>
	</div>
</div>

