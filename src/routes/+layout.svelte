<script lang="ts">
	import '../app.css';
	import 'flowbite/dist/flowbite.css';
	import UserSidebar from './user-sidebar/+page.svelte';
	import UserHeader from './user-header/+page.svelte';
</script>

<div class="flex flex-col h-screen">
	<UserHeader />
	<div class="flex flex-1 overflow-hidden">
		<UserSidebar />
		<main class="flex-1 overflow-y-auto p-8">
			<slot />
		</main>
	</div>
</div>

