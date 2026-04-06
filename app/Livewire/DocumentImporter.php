<?php

namespace App\Livewire;

use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class DocumentImporter extends Component
{
    use WithFileUploads;

    #[Url]
    public string $documentType = 'invoice';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $files = [];

    public string $batchId = '';

    public bool $uploading = false;

    public function mount(string $type = 'invoice'): void
    {
        $this->documentType = in_array($type, ['invoice', 'expense']) ? $type : 'invoice';
        $this->batchId = (string) Str::uuid();
    }

    public function updatedFiles(): void
    {
        $this->validate([
            'files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'files' => ['array', 'max:10'],
        ]);
    }

    public function startImport(): void
    {
        $this->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $userId = Auth::id();

        foreach ($this->files as $file) {
            $filename = $file->getClientOriginalName();
            $mime = $file->getMimeType() ?? 'application/octet-stream';
            $path = $file->storeAs(
                "imports/{$userId}/{$this->batchId}",
                Str::uuid().'.'.$file->getClientOriginalExtension(),
                'minio',
            );

            $import = DocumentImport::create([
                'user_id' => $userId,
                'batch_id' => $this->batchId,
                'original_filename' => $filename,
                'stored_path' => $path,
                'mime_type' => $mime,
                'document_type' => $this->documentType,
                'status' => 'pending',
            ]);

            ProcessDocumentImport::dispatch($import);
        }

        $this->files = [];
        $this->uploading = false;
    }

    public function retryImport(int $importId): void
    {
        $import = DocumentImport::where('user_id', Auth::id())->findOrFail($importId);

        if ($import->isFailed()) {
            $import->update(['status' => 'pending', 'error_message' => null]);
            ProcessDocumentImport::dispatch($import);
        }
    }

    public function deleteImport(int $importId): void
    {
        $import = DocumentImport::where('user_id', Auth::id())->findOrFail($importId);

        if ($import->stored_path) {
            Storage::disk('minio')->delete($import->stored_path);
        }

        $import->delete();
    }

    #[Computed]
    public function imports()
    {
        return DocumentImport::where('user_id', Auth::id())
            ->where('document_type', $this->documentType)
            ->whereNotIn('status', ['completed'])
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function hasActiveImports(): bool
    {
        return $this->imports->contains(
            fn (DocumentImport $i) => $i->isPending() || $i->isProcessing(),
        );
    }

    public function render()
    {
        return view('livewire.document-importer');
    }
}
