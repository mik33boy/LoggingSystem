<script lang="ts">
	import '../app.css';
	import 'flowbite/dist/flowbite.css';
	import UserSidebar from './user-sidebar/+page.svelte';
	import UserHeader from './user-header/+page.svelte';
	import { page } from '$app/stores';
</script>

<div class="flex h-screen">
	{#if $page.url.pathname !== '/login'}
		<UserSidebar/>
	{/if}
	<div class="flex-1 flex flex-col overflow-hidden">
		{#if $page.url.pathname !== '/login'}
			<UserHeader />
		{/if}
		<main class="flex-1 h-screen p-8">
			<slot />
		</main>
	</div>
</div>