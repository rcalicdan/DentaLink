<div class="space-y-8">
    @foreach ($services as $index => $service)
        <div class="relative bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 transition-all duration-200 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600"
            wire:key="service-item-{{ $index }}">

            {{-- Service Number Badge --}}
            <div
                class="absolute -top-3 -left-3 w-9 h-9 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-md border-2 border-white dark:border-slate-900">
                {{ $index + 1 }}
            </div>

            {{-- Remove Button (Always Visible) --}}
            @if (count($services) > 1)
                <button type="button" wire:click="removeService({{ $index }})"
                    class="absolute -top-3 -right-3 w-9 h-9 bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg transition-transform duration-200 hover:scale-110 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                    aria-label="Remove Service">
                    <i class="fas fa-trash-alt w-4 h-4"></i>
                </button>
            @endif

            {{-- Main Service Content --}}
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">

                    {{-- Service Search & Selection (Left Column) --}}
                    <div class="md:col-span-8">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Service
                            <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" wire:model.live="serviceSearches.{{ $index }}"
                                placeholder="Search and select a service..." onfocus="if(this.value) this.select()"
                                class="w-full pl-4 pr-10 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            <i class="fas fa-search text-slate-400 absolute top-1/2 right-3 -translate-y-1/2"></i>
                        </div>

                        {{-- Service Dropdown --}}
                        @if (isset($showServiceDropdowns[$index]) && $showServiceDropdowns[$index])
                            <div
                                class="absolute z-50 mt-2 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-64 overflow-auto">
                                @php
                                    $searchedServices = $this->getSearchedServicesByIndex($index);
                                @endphp
                                @forelse($searchedServices as $searchService)
                                    <button type="button"
                                        wire:click="selectService({{ $searchService->id }}, {{ $index }})"
                                        class="w-full px-4 py-3 text-left hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-slate-100 dark:border-slate-700 last:border-b-0 transition-colors duration-150">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                    {{ $searchService->name }}
                                                </div>
                                                <div class="text-sm text-slate-500 dark:text-slate-400 truncate">
                                                    {{ $searchService->serviceTypeName }}
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ₱{{ number_format($searchService->price, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fas fa-search mb-2 text-slate-300 dark:text-slate-600"></i>
                                        <p>No services found</p>
                                    </div>
                                @endforelse
                            </div>
                        @endif

                        {{-- Selected Service Display --}}
                        @if (!empty($service['dental_service_id']))
                            @php
                                $selectedService = \App\Models\DentalService::with('dentalServiceType')->find(
                                    $service['dental_service_id'],
                                );
                            @endphp
                            @if ($selectedService)
                                <div
                                    class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 dark:border-green-600 rounded-r-md">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-green-800 dark:text-green-200 truncate">
                                                {{ $selectedService->name }}
                                            </p>
                                            <p class="text-sm text-green-600 dark:text-green-300">
                                                {{ $selectedService->serviceTypeName }}
                                            </p>
                                        </div>
                                        <div class="ml-2 flex-shrink-0">
                                            <span class="font-semibold text-green-800 dark:text-green-200">
                                                ₱{{ number_format($selectedService->price, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @error("services.{$index}.dental_service_id")
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quantity Section (Right Column) --}}
                    @if (!empty($service['dental_service_id']))
                        @php
                            $selectedService = \App\Models\DentalService::find($service['dental_service_id']);
                        @endphp
                        @if ($selectedService && $selectedService->is_quantifiable && !$service['use_manual_total'])
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model.live="services.{{ $index }}.quantity"
                                        min="1"
                                        class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-hashtag text-slate-400 text-sm"></i>
                                    </div>
                                </div>
                                @error("services.{$index}.quantity")
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif(!$service['use_manual_total'])
                            {{-- Show quantity as 1 for non-quantifiable services --}}
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Quantity
                                </label>
                                <div class="relative">
                                    <input type="text" value="1" disabled
                                        class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-600 border border-slate-300 dark:border-slate-600 rounded-md text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-lock text-slate-400 text-sm"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">This service is not
                                    quantifiable</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Service Notes --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Service Notes (Optional)
                    </label>
                    <textarea wire:model="services.{{ $index }}.service_notes" rows="2"
                        placeholder="Add optional service notes..."
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm resize-none"></textarea>
                    @error("services.{$index}.service_notes")
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price Display & Manual Total Toggle --}}
                @if (!empty($service['dental_service_id']) && !empty($service['service_price']))
                    <div class="space-y-3">
                        {{-- Manual Total Toggle --}}
                        <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-700">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                                <span class="text-sm font-medium text-amber-900 dark:text-amber-100">
                                    Use Manual Total
                                </span>
                            </div>
                            <button type="button" 
                                wire:click="toggleManualTotal({{ $index }})"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 {{ $service['use_manual_total'] ? 'bg-amber-600' : 'bg-slate-300 dark:bg-slate-600' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $service['use_manual_total'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>

                        @if ($service['use_manual_total'])
                            {{-- Manual Total Input --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Manual Total Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-400">₱</span>
                                    <input type="number" 
                                        wire:model.live="services.{{ $index }}.manual_total"
                                        step="0.01"
                                        min="0"
                                        class="w-full pl-8 pr-3 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition text-sm">
                                </div>
                                @error("services.{$index}.manual_total")
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                    <i class="fas fa-info-circle"></i> Manual total overrides automatic calculation
                                </p>
                            </div>
                        @endif

                        {{-- Price Display --}}
                        <div class="bg-sky-50 dark:bg-sky-900/20 rounded-lg p-4 border border-sky-200 dark:border-sky-700">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-sky-600 dark:text-sky-400">
                                    Service Total:
                                </div>
                                <div class="font-semibold text-lg text-sky-900 dark:text-sky-100">
                                    ₱{{ number_format($this->getServiceTotal($index), 2) }}
                                </div>
                            </div>
                            @if (!$service['use_manual_total'] && (int) $service['quantity'] > 1)
                                <div class="text-xs text-sky-500 dark:text-sky-400 mt-1">
                                    ₱{{ number_format((float) $service['service_price'], 2) }} ×
                                    {{ (int) $service['quantity'] }}
                                </div>
                            @elseif($service['use_manual_total'])
                                <div class="text-xs text-amber-500 dark:text-amber-400 mt-1">
                                    <i class="fas fa-edit"></i> Manual total applied
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <input type="hidden" wire:model="services.{{ $index }}.dental_service_id">
                <input type="hidden" wire:model="services.{{ $index }}.service_price">
                <input type="hidden" wire:model="services.{{ $index }}.use_manual_total">
            </div>
        </div>
    @endforeach
</div>