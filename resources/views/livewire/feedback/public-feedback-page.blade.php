{{-- resources/views/livewire/feedback/public-feedback-page.blade.php --}}

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-tooth text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold text-slate-800 dark:text-white mb-2">Nice Smile Dental Clinic</h1>
            <p class="text-lg text-slate-600 dark:text-slate-300">We'd love to hear your feedback!</p>
        </div>

        @if($submitted)
            {{-- Success Message --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full mb-4">
                    <i class="fas fa-check text-3xl text-green-600 dark:text-green-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Thank You!</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-6">
                    Your feedback has been submitted successfully.
                </p>
                <div class="flex gap-4 justify-center flex-wrap">
                    <button wire:click="submitAnother"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Submit Another
                    </button>
                    <a href="{{ route('login') }}"
                        class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        Staff Login
                    </a>
                </div>
            </div>
        @else
            {{-- Feedback Form --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8">
                <form wire:submit.prevent="submit" class="space-y-6">
                    {{-- Rating --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                            How would you rate your experience? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    wire:click="setRating({{ $i }})"
                                    wire:mouseenter="setHoveredRating({{ $i }})"
                                    wire:mouseleave="resetHoveredRating"
                                    class="text-4xl transition-all duration-200 transform hover:scale-110 focus:outline-none">
                                    <i class="fa{{ ($hoveredRating >= $i || (!$hoveredRating && $rating >= $i)) ? 's' : 'r' }} fa-star {{ ($hoveredRating >= $i || (!$hoveredRating && $rating >= $i)) ? 'text-yellow-400' : 'text-slate-300 dark:text-slate-600' }}"></i>
                                </button>
                            @endfor
                            @if($rating > 0)
                                <span class="ml-3 text-slate-600 dark:text-slate-400 font-semibold">{{ $rating }} / 5</span>
                            @endif
                        </div>
                        @error('rating')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email (Optional) --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Your Email <span class="text-slate-400">(Optional)</span>
                        </label>
                        <input type="email" id="email" wire:model="email"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="john@example.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Feedback Content --}}
                    <div>
                        <label for="content" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Your Feedback <span class="text-red-500">*</span>
                        </label>
                        <textarea id="content" wire:model="content" rows="6"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                            placeholder="Tell us about your experience..."></textarea>
                        <div class="flex justify-between items-center mt-2">
                            @error('content')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400">Minimum 10 characters</p>
                            @enderror
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ strlen($content) }} / 1000</p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="w-full px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-paper-plane"></i>
                        Submit Feedback
                    </button>
                </form>

                {{-- Footer --}}
                <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 text-center">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Staff member? 
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-semibold">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>