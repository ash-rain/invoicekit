---
name: filament-resource
description: Scaffold or modify a Filament v5 resource for InvoiceKit following the project's namespace conventions and avoiding common Filament 5 namespace traps (Actions/Tables/Forms). Use when the user asks to "add a Filament resource", "create a Filament page", "add a column/field to <Resource>", or wires up an admin CRUD for a model. Covers resource + page + form/table schema + Livewire test.
---

# Filament v5 Resource Scaffolder (InvoiceKit)

Filament v5 namespaces are a common source of bugs because v4 and earlier had different layouts. This skill encodes the **correct** namespaces and the InvoiceKit-specific conventions for resources under `app/Filament/Resources/`.

## Correct namespaces — never get these wrong

| Component type | Correct namespace |
|---|---|
| Form fields (`TextInput`, `Select`, `DatePicker`, `Toggle`, `Textarea`) | `Filament\Forms\Components\` |
| Infolist entries (`TextEntry`, `IconEntry`, `RepeatableEntry`) | `Filament\Infolists\Components\` |
| Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`) | `Filament\Schemas\Components\` |
| Schema utilities (`Get`, `Set`) | `Filament\Schemas\Components\Utilities\` |
| Actions (`DeleteAction`, `CreateAction`, `EditAction`, `BulkAction`) | `Filament\Actions\` — **NEVER** `Filament\Tables\Actions\` or `Filament\Forms\Actions\` |
| Table columns (`TextColumn`, `IconColumn`, `BadgeColumn`) | `Filament\Tables\Columns\` |
| Table filters (`SelectFilter`, `TernaryFilter`) | `Filament\Tables\Filters\` |
| Icons | `Filament\Support\Icons\Heroicon` (enum, e.g. `Heroicon::PencilSquare`) |

## InvoiceKit conventions

- Resources live at `app/Filament/Resources/<Model>Resource.php`.
- Pages live at `app/Filament/Resources/<Model>Resource/Pages/` (List, Create, Edit).
- All resources scope by `user_id` — override `getEloquentQuery()` to add `->where('user_id', auth()->id())` unless the model is global (rare).
- All forms use `make()` static constructors; configuration uses Closures with `Get` / `Set`.
- Money fields use `numeric()->prefix('€')` (or the user's currency from `Settings`) and are stored as `decimal(12,2)`.
- Date fields use `displayFormat('d.m.Y')` (EU format).
- Every new resource must have a feature test under `tests/Feature/Filament/`.

## Workflow

### 1. Confirm requirements
Ask the user:
- Model name (singular, PascalCase) and table.
- Fields to expose in the form (with types).
- Columns to show in the table.
- Soft-deletes? Searchable? Sortable defaults?

### 2. Run the generator
```bash
php artisan make:filament-resource <Model> --generate --no-interaction
```
Use `--generate` to seed form/table from the model's fillable/casts.

### 3. Hand-edit for conventions

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExampleResource\Pages;
use App\Models\Example;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ExampleResource extends Resource
{
    protected static ?string $model = Example::class;

    protected static Heroicon $navigationIcon = Heroicon::DocumentText;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Details')->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('type')
                    ->options(ExampleType::class)
                    ->required()
                    ->live(),
                TextInput::make('reference')
                    ->visible(fn (Get $get): bool => $get('type') === 'business'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('created_at')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExamples::route('/'),
            'create' => Pages\CreateExample::route('/create'),
            'edit'   => Pages\EditExample::route('/{record}/edit'),
        ];
    }
}
```

### 4. Generate the feature test

```bash
php artisan make:test Filament/ExampleResourceTest
```

Test template (PHPUnit, not Pest — per project rules):

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ExampleResource\Pages\CreateExample;
use App\Filament\Resources\ExampleResource\Pages\ListExamples;
use App\Models\Example;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_their_examples(): void
    {
        $user = User::factory()->create();
        $mine = Example::factory()->for($user)->create();
        $theirs = Example::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListExamples::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    public function test_user_can_create_example(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateExample::class)
            ->fillForm(['name' => 'Test', 'type' => 'business'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('examples', [
            'name' => 'Test',
            'user_id' => $user->id,
        ]);
    }
}
```

### 5. Run the test
```bash
php artisan test --compact --filter=ExampleResource
```

### 6. Format
```bash
vendor/bin/pint --dirty --format agent
```

## Common pitfalls

- **`Filament\Tables\Actions\EditAction` (WRONG)** vs `Filament\Actions\EditAction` (RIGHT). The Tables namespace is deprecated in v5.
- **Forgetting `->columns(2)` on `Section`** — sections do NOT span full width by default.
- **Forgetting `getEloquentQuery()` scope** — this leaks other users' data into the panel.
- **Storing money as `float`** — always `decimal(12,2)` in migration, `decimal:2` cast in model.
- **File uploads default to `private` visibility** — pass `->visibility('public')` if PDFs/images need direct URLs.
