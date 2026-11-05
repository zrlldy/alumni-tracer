<?php

namespace App\Livewire;

use App\Filament\Resources\Alumnis\AlumniResource;
use App\Filament\Resources\Alumnis\Schemas\AlumniForm;
use App\Models\Alumni;
use App\Models\User;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AlumniInformationForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use RestrictsFileUploadsToSchemaComponents;
    use RoleBasedAccessControl;

    /** @var array<string, mixed> */
    public array $data = [];

    #[Locked]
    public bool $hasRecord = false;

    public bool $saved = false;

    public static function getSlug(): string
    {
        return AlumniResource::getSlug();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->fresh()) {
            return false;
        }

        $user->refresh();
        $allocation = $user->userAllocation;
        $role = $allocation?->role;

        return ! $user->trashed()
            && $allocation !== null
            && ! $allocation->trashed()
            && $allocation->management_scope === 'my_records'
            && $role !== null
            && ! $role->trashed()
            && static::hasPageAccess()
            && (static::hasPermission('view') || static::hasPermission('create') || static::hasPermission('edit'));
    }

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->fillForm();
    }

    public function hydrate(): void
    {
        $this->authorizeAccess();
    }

    public function canSave(): bool
    {
        return static::canAccess()
            && static::hasPermission($this->hasRecord ? 'edit' : 'create');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Alumni::class)
            ->statePath('data')
            ->disabled(! $this->canSave())
            ->components([
                ...AlumniForm::informationSections(lockIdentity: $this->hasRecord),
                Section::make('Employment status')
                    ->schema([
                        Select::make('employment_status')
                            ->options(['employed' => 'Employed', 'unemployed' => 'Unemployed', 'untraced' => 'Untraced'])
                            ->placeholder('Not specified'),
                    ]),
                Section::make('Further education')
                    ->description('Add your further studies. Existing entries can be updated; saved history is kept.')
                    ->schema([
                        $this->historyRepeater('education')
                            ->label('Education history')
                            ->addActionLabel('Add education')
                            ->schema([
                                Hidden::make('id')->nullable()->rules(['integer']),
                                TextInput::make('instituion')->label('Institution')->required()->maxLength(255),
                                TextInput::make('program')->label('Program or course')->required()->maxLength(255),
                                TextInput::make('degree_level')->label('Degree level')->required()->maxLength(255),
                                TextInput::make('units_completed')->numeric()->integer()->minValue(0)->maxValue(4294967295)->required(),
                                TextInput::make('status')->label('Study status')->required()->maxLength(255),
                                DatePicker::make('started_at')->label('Start date')->required(),
                                DatePicker::make('ended_at')->label('End date')->required()->afterOrEqual('started_at')
                                    ->helperText('An end date is required. Leave ongoing studies out until an end date is known.'),
                            ]),
                    ]),
                Section::make('Employment history')
                    ->description('Add your jobs, including concurrent employment. Saved history is kept.')
                    ->schema([
                        $this->historyRepeater('employments')
                            ->label('Jobs')
                            ->addActionLabel('Add employment')
                            ->schema([
                                Hidden::make('id')->nullable()->rules(['integer']),
                                TextInput::make('company_name')->label('Company or organization')->required()->maxLength(255),
                                TextInput::make('position')->maxLength(255),
                                TextInput::make('company_address')->required()->maxLength(255),
                                TextInput::make('industry')->maxLength(255),
                                TextInput::make('employment_type')->required()->maxLength(255),
                                Select::make('is_course_related')->label('Related to your course?')
                                    ->options([1 => 'Yes', 0 => 'No'])->placeholder('Not specified'),
                                DatePicker::make('date_hired')->label('Date hired')->required(),
                                DatePicker::make('starting_date')->label('Work start date'),
                                DatePicker::make('ended_at')->label('Work end date')->afterOrEqual('date_hired')->afterOrEqual('starting_date'),
                                TextInput::make('supported_documents')->label('Supporting document reference')
                                    ->helperText('Enter an existing document reference or URL.')
                                    ->required()->maxLength(255),
                                Toggle::make('is_current')->label('I currently work here')->default(false)->required(),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $user = $this->authorizeAccess();
        $this->saved = false;
        $record = $this->ownedRecord($user);
        abort_unless(static::hasPermission($record ? 'edit' : 'create'), 403);
        $data = $this->form->getState();

        try {
            DB::transaction(function () use ($user, $data): void {
                User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $this->authorizeAccess();
                $record = $this->ownedRecord($user, lock: true);
                abort_unless(static::hasPermission($record ? 'edit' : 'create'), 403);

                $fields = ['email', 'phone_number', 'current_address', 'employment_status'];

                if (! $record) {
                    $fields = [...$fields, 'student_number', 'first_name', 'middle_name', 'last_name', 'program_id', 'graduation_year'];
                }

                $attributes = Arr::only($data, $fields);
                $studentNumber = $record?->student_number ?? $attributes['student_number'];
                $email = $attributes['email'];
                $hasMatch = Alumni::withTrashed()
                    ->when($record, fn (Builder $query): Builder => $query->whereKeyNot($record->id))
                    ->where(fn (Builder $query): Builder => $query->where('student_number', $studentNumber)->orWhereRaw('LOWER(email) = ?', [mb_strtolower($email)]))
                    ->exists();

                if ($hasMatch) {
                    throw ValidationException::withMessages([
                        $record ? 'data.email' : 'data.student_number' => 'These details may match an existing record. Ask the administrator to review your account link.',
                    ]);
                }

                if ($record) {
                    $record->update($attributes);
                } else {
                    $attributes['middle_name'] ??= '';
                    $record = $user->alumnis()->create($attributes);
                }

                $this->saveHistory($record->alumniEducation(), $data['education'] ?? [], 'education', [
                    'instituion', 'program', 'degree_level', 'units_completed', 'status', 'started_at', 'ended_at',
                ]);
                $this->saveHistory($record->alumniEmployment(), $data['employments'] ?? [], 'employments', [
                    'company_name', 'position', 'company_address', 'industry', 'employment_type', 'is_course_related',
                    'date_hired', 'starting_date', 'ended_at', 'supported_documents', 'is_current',
                ]);
            });
        } catch (QueryException) {
            Log::warning('Alumni information save failed.', ['user_id' => $user->id]);

            throw ValidationException::withMessages([
                'data' => 'Your information could not be saved. Please try again or contact the administrator.',
            ]);
        }

        $this->resetValidation();
        $this->fillForm();
        $this->saved = true;
    }

    protected function authorizeAccess(): User
    {
        abort_unless(static::canAccess(), 403);

        return Auth::user();
    }

    protected function ownedRecord(User $user, bool $lock = false): ?Alumni
    {
        $records = $user->alumnis()->withTrashed()
            ->when($lock, fn (Builder $query): Builder => $query->lockForUpdate())
            ->limit(2)->get();

        abort_if($records->count() > 1 || $records->first()?->trashed(), 403, 'Ask the administrator to review your account link.');

        return $records->first();
    }

    protected function fillForm(): void
    {
        $user = $this->authorizeAccess();
        $record = $this->ownedRecord($user);
        $this->hasRecord = $record !== null;
        $data = $record?->only([
            'student_number', 'first_name', 'middle_name', 'last_name', 'email', 'phone_number', 'current_address',
            'program_id', 'graduation_year', 'employment_status',
        ]) ?? ['email' => $user->email];

        $data['education'] = $record?->alumniEducation()->whereNull('deleted_at')->orderBy('id')->get()->map->only([
            'id', 'instituion', 'program', 'degree_level', 'units_completed', 'status', 'started_at', 'ended_at',
        ])->all() ?? [];
        $data['employments'] = $record?->alumniEmployment()->whereNull('deleted_at')->orderBy('id')->get()->map->only([
            'id', 'company_name', 'position', 'company_address', 'industry', 'employment_type', 'is_course_related',
            'date_hired', 'starting_date', 'ended_at', 'supported_documents', 'is_current',
        ])->all() ?? [];

        $this->cacheSchema('form', null);
        $this->form->fill($data);
    }

    protected function historyRepeater(string $name): Repeater
    {
        return Repeater::make($name)
            ->defaultItems(0)
            ->maxItems(100)
            ->columns(2)
            ->reorderable(false)
            ->deleteAction(fn (Action $action): Action => $action->visible(
                fn (array $arguments, Repeater $component): bool => blank($component->getState()[$arguments['item']]['id'] ?? null),
            ));
    }

    /**
     * @param  array<array-key, array<string, mixed>>  $rows
     * @param  array<string>  $fields
     */
    protected function saveHistory(HasMany $relationship, array $rows, string $field, array $fields): void
    {
        foreach ($rows as $key => $row) {
            $attributes = Arr::only($row, $fields);

            if (filled($row['id'] ?? null)) {
                $history = (clone $relationship)->whereNull('deleted_at')->whereKey($row['id'])->lockForUpdate()->first();

                if (! $history) {
                    throw ValidationException::withMessages(["data.{$field}.{$key}.id" => 'This history entry is unavailable. Reload the form.']);
                }

                $history->update($attributes);
            } else {
                $relationship->create($attributes);
            }
        }
    }

    public function render(): View
    {
        return view('livewire.alumni-information-form');
    }
}
