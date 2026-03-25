<x-app-layout>
    <x-slot name="header">{{ __('Timer') }}</x-slot>

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
</x-app-layout>
