<?php

namespace App\Livewire\Expenses;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateExpense extends Component
{
    use WithFileUploads;

    public ?Expense $expense = null;

    public string $description = '';

    public string $amount = '';

    public string $currency = 'EUR';

    public string $category = 'other';

    public string $date = '';

    public ?int $clientId = null;

    public ?int $projectId = null;

    public bool $billable = false;

    public $receipt = null;

    public function mount(?Expense $expense = null): void
    {
        if ($expense && $expense->exists) {
            if ($expense->user_id !== Auth::id()) {
                abort(403);
            }

            $this->expense = $expense;
            $this->description = $expense->description;
            $this->amount = (string) $expense->amount;
            $this->currency = $expense->currency;
            $this->category = $expense->category;
            $this->date = $expense->date->format('Y-m-d');
            $this->clientId = $expense->client_id;
            $this->projectId = $expense->project_id;
            $this->billable = $expense->billable;
        } else {
            $this->date = now()->toDateString();
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function projects()
    {
        return Project::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        $this->validate([
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:10'],
            'category' => ['required', 'in:software,hardware,travel,hosting,marketing,other'],
            'date' => ['required', 'date'],
            'clientId' => ['nullable', 'integer', 'exists:clients,id'],
            'projectId' => ['nullable', 'integer', 'exists:projects,id'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $receiptPath = $this->expense?->receipt_file;

        if ($this->receipt) {
            $receiptPath = $this->receipt->store('receipts/'.Auth::id(), 'minio');
        }

        $data = [
            'user_id' => Auth::id(),
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'category' => $this->category,
            'date' => $this->date,
            'client_id' => $this->clientId,
            'project_id' => $this->projectId,
            'billable' => $this->billable,
            'receipt_file' => $receiptPath,
        ];

        if ($this->expense && $this->expense->exists) {
            $this->expense->update($data);
        } else {
            Expense::create($data);
        }

        $this->redirect(route('expenses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.expenses.create-expense');
    }
}
