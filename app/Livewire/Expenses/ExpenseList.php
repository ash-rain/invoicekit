<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ExpenseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $expenseId): void
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($expenseId);
        $expense->delete();
    }

    public function render()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->with(['client', 'project'])
            ->when($this->search, fn ($q) => $q->where('description', 'like', '%'.$this->search.'%'))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderByDesc('date')
            ->paginate(20);

        $monthlySummary = Expense::where('user_id', Auth::id())
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return view('livewire.expenses.expense-list', compact('expenses', 'monthlySummary'));
    }
}
