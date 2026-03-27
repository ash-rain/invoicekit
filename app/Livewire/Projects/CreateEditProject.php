<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateEditProject extends Component
{
    public ?Project $project = null;

    public string $name = '';

    public ?int $client_id = null;

    public string $hourly_rate = '';

    public string $currency = 'EUR';

    public string $status = 'active';

    public const CURRENCIES = ['EUR', 'USD', 'BGN', 'RON', 'PLN', 'CZK', 'HUF'];

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->authorize('update', $project);
            $this->project = $project;
            $this->name = $project->name;
            $this->client_id = $project->client_id;
            $this->hourly_rate = $project->hourly_rate ? (string) $project->hourly_rate : '';
            $this->currency = $project->currency;
            $this->status = $project->status;
        }
    }

    public function updatedClientId(?int $value): void
    {
        if ($value && ! ($this->project && $this->project->exists)) {
            $client = Client::where('user_id', Auth::id())->find($value);
            if ($client) {
                $this->currency = $client->currency;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'currency' => ['required', 'string', 'in:' . implode(',', self::CURRENCIES)],
            'status' => ['required', 'in:active,archived'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Verify client belongs to the authenticated user
        if (! empty($validated['client_id'])) {
            $clientExists = Client::where('id', $validated['client_id'])
                ->where('user_id', Auth::id())
                ->exists();

            if (! $clientExists) {
                $this->addError('client_id', __('Invalid client selected.'));

                return;
            }
        }

        $data = array_merge($validated, [
            'user_id' => Auth::id(),
            'hourly_rate' => $validated['hourly_rate'] !== '' && $validated['hourly_rate'] !== null
                ? $validated['hourly_rate']
                : null,
        ]);

        if ($this->project && $this->project->exists) {
            $this->project->update($data);
            session()->flash('success', __('Project updated successfully.'));
        } else {
            Project::create($data);
            session()->flash('success', __('Project created successfully.'));
        }

        $this->redirect(route('projects.index'), navigate: true);
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'currency']);

        return view('livewire.projects.create-edit-project', [
            'clients' => $clients,
            'currencies' => self::CURRENCIES,
        ]);
    }
}
