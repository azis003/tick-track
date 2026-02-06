<template>
  <div class="relative flex items-center justify-center">
    <!-- Menggunakan gambar asli dari user -->
    <img 
      src="/images/logo-icon.png" 
      alt="Loading..." 
      class="w-24 h-24 object-contain animate-logo-pulse"
    />
    
    <!-- Efek glow di belakang logo -->
    <div class="absolute inset-0 bg-blue-400/20 blur-3xl rounded-full -z-10 animate-pulse"></div>
  </div>
</template>

<style scoped>
.animate-logo-pulse {
  animation: logo-pulse 2s infinite ease-in-out;
}

@keyframes logo-pulse {
  0%, 100% {
    transform: scale(1);
    filter: drop-shadow(0 0 0px rgba(37, 99, 235, 0));
  }
  50% {
    transform: scale(1.1);
    filter: drop-shadow(0 0 20px rgba(37, 99, 235, 0.3));
  }
}
</style>
