<x-app-layout>
    <div class="p-6">
        <div class="mb-8">
            <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">{{ __('Timer') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Track time across your projects') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <livewire:timer.active-timer />
                <livewire:timer.manual-time-entry />
                <livewire:timer.time-entry-list />
            </div>

            <div>
                <livewire:timer.weekly-summary />
            </div>
        </div>
    </div>
</x-app-layout>
