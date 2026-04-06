<div class="p-6 lg:p-8" @if ($this->hasActiveImports) wire:poll.2s="$refresh" @endif>

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight"
                style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">
                {{ $documentType === 'invoice' ? __('Import Invoices') : __('Import Expenses') }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Upload files to import') }}</p>
        </div>
        <a href="{{ $documentType === 'invoice' ? route('invoices.index') : route('expenses.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ $documentType === 'invoice' ? __('Back to Invoices') : __('Back to Expenses') }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upload zone --}}
        <div>
            <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
                <h2 class="text-sm font-bold text-gray-700 mb-4">{{ __('Upload Documents') }}</h2>

                <form wire:submit="startImport">
                    {{-- Drop zone --}}
                    <label for="file-upload" x-data="{ dragging: false }" @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $wire.files = Array.from($event.dataTransfer.files)"
                        :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 bg-gray-50 hover:bg-gray-100'"
                        class="flex flex-col items-center justify-center gap-3 border-2 border-dashed rounded-2xl px-6 py-10 cursor-pointer transition-all">
                        <div
                            class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $documentType === 'invoice' ? 'bg-indigo-100' : 'bg-orange-100' }}">
                            <svg class="w-6 h-6 {{ $documentType === 'invoice' ? 'text-indigo-600' : 'text-orange-600' }}"
                                fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-700">{{ __('Drag & drop or click to upload') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ __('PDF, JPG, PNG — up to 10 MB each, max 10 files') }}</p>
                        </div>
                        <input id="file-upload" x-ref="fileInput" wire:model="files" type="file" multiple
                            accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                    </label>

                    @error('files')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('files.*')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Selected files preview --}}
                    @if (count($files) > 0)
                        <ul class="mt-4 space-y-2">
                            @foreach ($files as $file)
                                <li class="flex items-center gap-3 px-3 py-2 rounded-xl bg-gray-50"
                                    style="border:1px solid #f3f4f6;">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span
                                        class="text-xs font-medium text-gray-700 truncate flex-1">{{ $file->getClientOriginalName() }}</span>
                                    <span class="text-xs text-gray-400">{{ number_format($file->getSize() / 1024, 0) }}
                                        KB</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <button type="submit" wire:loading.attr="disabled" wire:target="startImport"
                        wire:loading.class="opacity-60 cursor-not-allowed" wire:target="startImport"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
                        style="background:#0f1117;color:white;" onmouseover="this.style.background='#1e2130'"
                        onmouseout="this.style.background='#0f1117'">
                        <span wire:loading.remove wire:target="startImport">{{ __('Start Import') }}</span>
                        <span wire:loading wire:target="startImport">{{ __('Uploading...') }}</span>
                    </button>
                </form>
            </div>

            {{-- AI notice --}}
            <div class="mt-4 flex items-start gap-3 px-4 py-3 rounded-xl bg-indigo-50"
                style="border:1px solid #e0e7ff;">
                <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347A3.75 3.75 0 0113.5 21h-3a3.75 3.75 0 01-2.652-1.098l-.347-.347z" />
                </svg>
                <p class="text-xs text-indigo-700">
                    {{ __('AI will extract the data from your documents. Review and confirm before saving.') }}</p>
            </div>
        </div>

        {{-- Import queue / status list --}}
        <div>
            <div class="bg-white rounded-2xl" style="border:1px solid #eaecf0;">
                <div class="px-5 py-4" style="border-bottom:1px solid #f3f4f6;">
                    <h2 class="text-sm font-bold text-gray-700">{{ __('Extraction Queue') }}</h2>
                </div>

                @if ($this->imports->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-200 mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm text-gray-400">{{ __('No files uploaded yet') }}</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach ($this->imports as $import)
                            <li class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Status icon --}}
                                    @if ($import->isPending())
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @elseif ($import->isProcessing())
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-100 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-blue-500 animate-spin" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </div>
                                    @elseif ($import->isExtracted())
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-100 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none"
                                                stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                    @elseif ($import->isCompleted())
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-green-100 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-green-600" fill="none"
                                                stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    @else
                                        {{-- failed --}}
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-red-100 shrink-0">
                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none"
                                                stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- File info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $import->original_filename }}</p>
                                        <p class="text-xs text-gray-400">
                                            @if ($import->isPending())
                                                {{ __('Waiting...') }}
                                            @elseif ($import->isProcessing())
                                                {{ __('Processing...') }}
                                            @elseif ($import->isExtracted())
                                                {{ __('Extraction complete') }}
                                            @elseif ($import->isCompleted())
                                                {{ __('Saved') }}
                                            @else
                                                {{ __('Import failed') }}
                                            @endif
                                        </p>
                                        @if ($import->isFailed() && $import->error_message)
                                            <p class="text-xs text-red-500 mt-0.5 truncate"
                                                title="{{ $import->error_message }}">{{ $import->error_message }}</p>
                                        @endif
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if ($import->isExtracted())
                                            @php
                                                $reviewRoute =
                                                    $import->document_type === 'invoice'
                                                        ? route('invoices.import.review', $import)
                                                        : route('expenses.import.review', $import);
                                            @endphp
                                            <a href="{{ $reviewRoute }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all"
                                                style="background:#4f46e5;"
                                                onmouseover="this.style.background='#4338ca'"
                                                onmouseout="this.style.background='#4f46e5'">
                                                {{ __('Review') }}
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        @elseif ($import->isFailed())
                                            <button wire:click="retryImport({{ $import->id }})"
                                                class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                                                {{ __('Retry') }}
                                            </button>
                                        @endif
                                        <button wire:click="deleteImport({{ $import->id }})"
                                            wire:confirm="{{ __('Delete this import?') }}"
                                            title="{{ __('Delete') }}"
                                            class="w-6 h-6 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
