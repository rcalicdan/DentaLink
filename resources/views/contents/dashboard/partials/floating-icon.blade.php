<!-- Floating AI Icon -->
<button @click="toggleModal()"
    class="group relative bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 hover:from-purple-700 hover:via-blue-700 hover:to-cyan-600 text-white rounded-full p-4 shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-purple-500/50">

    <!-- Animated Background Glow -->
    <div
        class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-400 to-cyan-400 opacity-75 blur-md group-hover:opacity-100 transition-opacity duration-300 animate-pulse">
    </div>

    <!-- AI Icon with Animation -->
    <div class="relative z-10 flex items-center justify-center w-6 h-6">
        <svg class="w-6 h-6 transform group-hover:rotate-12 transition-transform duration-300" fill="currentColor"
            viewBox="0 0 24 24">
            <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
        </svg>
    </div>

    <!-- Notification Badge -->
    <div
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-bounce">
        <span class="text-xs font-bold">AI</span>
    </div>

    <!-- Tooltip -->
    <div
        class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-slate-900 dark:bg-slate-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
        Ask AI Assistant
        <div
            class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-slate-900 dark:border-t-slate-700">
        </div>
    </div>
</button>
