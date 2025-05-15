<script>
  import '../app.css';
  import Navbar from '$lib/components/Navbar.svelte';
</script>

<div class="min-h-screen bg-gray-50 flex flex-col">
  <Navbar />
  <main class="flex-grow">
    <slot />
  </main>
  
  <footer class="bg-gray-800 text-white py-6">
    <div class="container mx-auto px-4">
      <div class="text-center">
        <p>&copy; {new Date().getFullYear()} Communication Logger System. All rights reserved.</p>
      </div>
    </div>
  </footer>
</div> 