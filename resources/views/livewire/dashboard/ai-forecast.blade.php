<div class="mt-8 mb-10">
    <!-- Main Card: Deep Blue Gradient Theme -->
    <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-900 border border-indigo-500/30 shadow-2xl shadow-indigo-900/20">

        <!-- Decorative Background Glows -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-cyan-500/20 blur-3xl opacity-40">
        </div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl opacity-40">
        </div>

        <!-- Header -->
        <div
            class="relative z-10 border-b border-white/10 bg-white/5 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 backdrop-blur-sm">
            <div class="flex items-center">
                <div>
                    <h3 class="text-xl font-bold text-white tracking-wide drop-shadow-sm">
                        AI Business Forecast
                    </h3>
                    <div class="flex items-center gap-2 text-sm mt-1">
                        @if ($hasGenerated && $forecast)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                                <i class="fas fa-check-circle mr-1.5"></i>Analysis Ready
                            </span>
                            <span class="text-indigo-300/50">•</span>
                            <span class="text-indigo-200">{{ $generatedAt }}</span>
                        @elseif ($isLoading)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-500/10 text-yellow-300 border border-yellow-500/20">
                                <i class="fas fa-spinner fa-spin mr-1.5"></i>Generating...
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-500/10 text-slate-300 border border-slate-500/20">
                                <i class="fas fa-info-circle mr-1.5"></i>Not Generated
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                @if ($hasGenerated && $forecast)
                    <button wire:click="refreshForecast" wire:loading.attr="disabled" type="button"
                        class="group relative inline-flex items-center justify-center px-5 py-2 text-sm font-medium text-white transition-all duration-200 bg-white/10 border border-white/10 rounded-lg hover:bg-white/20 hover:border-white/20 hover:shadow-lg hover:shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-cyan-400 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-sync-alt mr-2 text-cyan-300 transition-transform group-hover:rotate-180"
                            wire:loading.class="animate-spin" wire:target="refreshForecast"></i>
                        <span wire:loading.remove wire:target="refreshForecast">Update Analysis</span>
                        <span wire:loading wire:target="refreshForecast">Updating...</span>
                    </button>
                @else
                    <button wire:click="generateForecast" wire:loading.attr="disabled" type="button"
                        class="group relative inline-flex items-center justify-center px-5 py-2 text-sm font-medium text-white transition-all duration-200 bg-gradient-to-r from-cyan-500 to-blue-600 border border-cyan-400/30 rounded-lg hover:from-cyan-400 hover:to-blue-500 hover:shadow-lg hover:shadow-cyan-500/30 focus:outline-none focus:ring-2 focus:ring-cyan-400 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-magic mr-2" wire:loading.class="fa-spinner fa-spin"
                            wire:target="generateForecast"></i>
                        <span wire:loading.remove wire:target="generateForecast">Generate Forecast</span>
                        <span wire:loading wire:target="generateForecast">Generating...</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Content Body -->
        <div class="relative z-10 p-6 md:p-8">
            @if ($isLoading)
                <!-- Loading State -->
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="relative">
                        <div class="w-20 h-20 border-4 border-cyan-500/20 border-t-cyan-400 rounded-full animate-spin">
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i class="fas fa-brain text-2xl text-cyan-400"></i>
                        </div>
                    </div>
                    <p class="mt-6 text-lg font-medium text-white">Analyzing Your Clinic Data...</p>
                    <p class="text-sm text-indigo-300 mt-2">This may take a few moments</p>
                </div>
            @elseif ($error)
                <!-- Error State -->
                <div
                    class="flex flex-col items-center justify-center py-10 text-center bg-red-900/20 rounded-xl border border-dashed border-red-700/50">
                    <div class="w-16 h-16 bg-red-800/30 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white">Analysis Unavailable</h4>
                    <p class="text-red-200 text-sm mt-1 max-w-md">{{ $error }}</p>
                    <button wire:click="generateForecast"
                        class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm transition-colors">
                        Try Again
                    </button>
                </div>
            @elseif ($forecast)
                <!-- Forecast Content -->
                <div
                    class="text-indigo-100 leading-relaxed space-y-4
                    [&>h1]:text-2xl [&>h1]:font-bold [&>h1]:text-white [&>h1]:mb-6
                    [&>h2]:text-xl [&>h2]:font-bold [&>h2]:text-cyan-200 [&>h2]:mt-8 [&>h2]:mb-4
                    [&>h3]:text-lg [&>h3]:font-bold [&>h3]:text-indigo-300 [&>h3]:mt-6 [&>h3]:mb-3 [&>h3]:uppercase [&>h3]:tracking-wider
                    [&>ul]:grid [&>ul]:grid-cols-1 [&>ul]:md:grid-cols-2 [&>ul]:gap-4 [&>ul]:my-4
                    [&>ul>li]:relative [&>ul>li]:bg-white/5 [&>ul>li]:backdrop-blur-md [&>ul>li]:p-5 [&>ul>li]:rounded-xl [&>ul>li]:border [&>ul>li]:border-white/5 [&>ul>li]:transition [&>ul>li]:duration-300 [&>ul>li]:hover:bg-white/10 [&>ul>li]:hover:border-cyan-400/30 [&>ul>li]:list-none
                    [&_strong]:text-cyan-300 [&_strong]:font-bold [&_strong]:text-lg [&_strong]:drop-shadow-sm
                    [&>p]:mb-4 [&>p]:text-blue-100/90">
                    {!! Str::markdown($forecast) !!}
                </div>
            @else
                <!-- Initial State -->
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-cyan-500/20 to-blue-600/20 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-cyan-500/10">
                        <i class="fas fa-chart-line text-4xl text-cyan-400"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Ready to Generate AI Insights</h4>
                    <p class="text-indigo-200 text-sm max-w-md mb-6">
                        Get AI-powered analysis of your clinic's performance, trends, and actionable recommendations.
                    </p>
                    <button wire:click="generateForecast" type="button"
                        class="group inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white transition-all duration-200 bg-gradient-to-r from-cyan-500 to-blue-600 border border-cyan-400/30 rounded-lg hover:from-cyan-400 hover:to-blue-500 hover:shadow-lg hover:shadow-cyan-500/30 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <i class="fas fa-magic mr-2"></i>
                        Generate Forecast Now
                    </button>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div
            class="px-6 py-3 bg-black/20 border-t border-white/5 flex items-center justify-between text-xs text-indigo-300/70">
            <span class="flex items-center">
                <i class="fas fa-shield-alt mr-1.5 text-cyan-500/70"></i>
                Secure AI Analysis
            </span>
            <span>Powered by Gemini</span>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('forecast-success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="mt-4 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-300 text-sm">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('forecast-success') }}
        </div>
    @endif
</div>
