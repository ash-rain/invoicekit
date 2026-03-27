<div class="bg-white rounded-2xl border border-[#eaecf0] overflow-hidden">
    <div class="px-6 py-4 border-b border-[#eaecf0]">
        <h2 class="text-sm font-bold text-gray-900">{{ __('Time Tracker') }}</h2>
    </div>

    <div class="p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Project') }}</label>
            <select
                wire:model="projectId"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $isRunning ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                {{ $isRunning ? 'disabled' : '' }}
            >
                <option value="">{{ __('— Select project —') }}</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->client->name }} / {{ $project->name }}</option>
                @endforeach
            </select>
            @error('projectId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
            <input
                wire:model="description"
                type="text"
                placeholder="{{ __('What are you working on?') }}"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>

        <div class="flex items-center justify-between pt-2">
            @if($isRunning)
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse shrink-0"></span>
                    <span
                        class="text-4xl font-mono font-bold text-[#0f1117]"
                        x-data="elapsedTimer('{{ $startedAt }}')"
                        x-text="display"
                    >{{ $elapsedTime }}</span>
                </div>
                <button
                    wire:click="stopTimer"
                    class="px-6 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 font-bold text-sm"
                >
                    {{ __('Stop') }}
                </button>
            @else
                <span class="text-4xl font-mono font-bold text-gray-300">00:00:00</span>
                <button
                    wire:click="startTimer"
                    class="px-6 py-2.5 bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] font-bold text-sm"
                >
                    {{ __('Start') }}
                </button>
            @endif
        </div>

        @if($isRunning)
            <p class="text-xs text-green-600 font-semibold">{{ __('Timer is running...') }}</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
function elapsedTimer(startedAt) {
    return {
        display: '00:00:00',
        interval: null,
        init() {
            const start = new Date(startedAt).getTime();
            const update = () => {
                const elapsed = Math.floor((Date.now() - start) / 1000);
                const h = Math.floor(elapsed / 3600);
                const m = Math.floor((elapsed % 3600) / 60);
                const s = elapsed % 60;
                this.display = [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
            };
            update();
            this.interval = setInterval(update, 1000);
        },
        destroy() {
            clearInterval(this.interval);
        }
    };
}
</script>
@endpush
