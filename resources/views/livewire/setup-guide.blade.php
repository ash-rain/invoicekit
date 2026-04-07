<div>
    @if (!$user->hasSetupGuideDismissed() && !$this->allStepsCompleted())
        <div class="rounded-2xl overflow-hidden"
            style="border:1px solid #e0e7ff;background:linear-gradient(135deg,#f5f3ff 0%,#eef2ff 100%);">

            {{-- ─── Header ─────────────────────────────────── --}}
            <div class="flex items-start justify-between px-6 pt-5 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background:#6366f1;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 leading-tight"
                            style="font-family:'Syne',sans-serif;">{{ __('Setup Guide') }}</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __(':completed of :total steps completed', ['completed' => $completedCount, 'total' => $totalCount]) }}
                        </p>
                    </div>
                </div>
                <button wire:click="dismissGuide"
                    wire:confirm="{{ __('Hide the setup guide? You can still access all these settings at any time.') }}"
                    class="text-gray-400 hover:text-gray-600 transition-colors rounded-lg p-1 -mr-1 mt-0.5"
                    title="{{ __('Hide Setup Guide') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- ─── Progress bar ────────────────────────────── --}}
            <div class="px-6 pb-4">
                <div class="w-full rounded-full overflow-hidden" style="height:6px;background:#e0e7ff;">
                    <div class="h-full rounded-full transition-all duration-500"
                        style="width:{{ $progressPercent }}%;background:#6366f1;"></div>
                </div>
            </div>

            {{-- ─── Steps ───────────────────────────────────── --}}
            <div class="px-6 pb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($steps as $step)
                    @php $completed = $this->isStepCompleted($step['key']); @endphp
                    <div class="rounded-xl p-4 flex flex-col gap-3 transition-all"
                        style="background:{{ $completed ? 'rgba(255,255,255,0.6)' : 'white' }};border:1px solid {{ $completed ? '#d1fae5' : '#e5e7eb' }};">

                        {{-- Step header --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                {{-- Status indicator --}}
                                @if ($completed)
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                                        style="background:#dcfce7;">
                                        <svg class="w-3 h-3" fill="none" stroke="#16a34a" stroke-width="2.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full flex-shrink-0" style="border:2px solid #d1d5db;">
                                    </div>
                                @endif

                                <p
                                    class="text-sm font-semibold leading-snug {{ $completed ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                    {{ __($step['title']) }}
                                </p>
                            </div>

                            {{-- Dismiss button for optional incomplete steps --}}
                            @if ($step['dismissible'] && !$completed)
                                <button wire:click="dismissStep('{{ $step['key'] }}')"
                                    class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0 rounded p-0.5"
                                    title="{{ __('Dismiss') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Description --}}
                        <p class="text-xs leading-relaxed {{ $completed ? 'text-gray-400' : 'text-gray-500' }}">
                            {{ __($step['description']) }}
                        </p>

                        {{-- CTA --}}
                        @if (!$completed)
                            <a href="{{ $step['url'] }}" wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-semibold transition-colors"
                                style="color:#6366f1;" onmouseover="this.style.color='#4f46e5'"
                                onmouseout="this.style.color='#6366f1'">
                                {{ __($step['cta']) }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color:#16a34a;">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('Completed') }}
                            </span>
                        @endif

                    </div>
                @endforeach
            </div>

        </div>
    @endif
</div>
