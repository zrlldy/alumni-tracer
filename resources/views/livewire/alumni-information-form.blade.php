<div class="space-y-6">
    <header class="space-y-1">
        <h2 class="text-2xl font-semibold text-gray-950 dark:text-white">My alumni dashboard</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Your profile, work, and further education in one place.</p>
    </header>

    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Core profile</x-slot>
            <p class="text-3xl font-semibold text-gray-950 dark:text-white">{{ $overview['profileCompletion'] }}%</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $overview['profileCompletion'] === 100 ? 'Core profile information recorded' : ($hasRecord ? 'Core profile needs attention' : 'Complete your alumni information to reach 100%') }}</p>
            <div role="progressbar" aria-label="Core profile completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $overview['profileCompletion'] }}" class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-full rounded-full bg-primary-600 {{ $overview['profileCompletion'] === 100 ? 'w-full' : 'w-0' }}"></div>
            </div>
            @if (! $hasRecord && $this->canSave())
                <x-filament::button class="mt-4" wire:click="openEditor" aria-controls="alumni-information-editor" aria-expanded="{{ $editing ? 'true' : 'false' }}">
                    Complete my information
                </x-filament::button>
            @elseif ($hasRecord && $overview['profileCompletion'] === 0 && $this->canSave())
                <x-filament::button class="mt-4" wire:click="openEditor" aria-controls="alumni-information-editor" aria-expanded="{{ $editing ? 'true' : 'false' }}">
                    Review my information
                </x-filament::button>
            @elseif ($hasRecord && ! $this->canSave())
                <x-filament::button class="mt-4" wire:click="openEditor" aria-controls="alumni-information-editor" aria-expanded="{{ $editing ? 'true' : 'false' }}">
                    View my information
                </x-filament::button>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Reported employment status</x-slot>
            <p class="text-xl font-semibold text-gray-950 dark:text-white">
                {{ $overview['employmentStatus'] ? ucfirst($overview['employmentStatus']) : 'Employment status not recorded' }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Shown as saved. A job entry does not change this status automatically.</p>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Work history</x-slot>
        <x-slot name="description">Current and past jobs you have recorded. These entries are not marked as school verified.</x-slot>

        @if ($overview['currentJobs'] === [] && $overview['pastJobs'] === [])
            <p class="text-sm text-gray-600 dark:text-gray-400">No work history recorded</p>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-3">
                    <h3 class="font-medium text-gray-950 dark:text-white">Current work</h3>
                    @forelse ($overview['currentJobs'] as $job)
                        <article class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-950 dark:text-white">{{ $job['company_name'] }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $job['position'] ?: 'Position not recorded' }}</p>
                            <dl class="mt-3 grid gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <div><dt class="font-medium">Employment type</dt><dd>{{ $job['employment_type'] }}</dd></div>
                                <div><dt class="font-medium">Date hired</dt><dd>{{ $job['date_hired'] }}</dd></div>
                                <div><dt class="font-medium">Document reference</dt><dd class="break-all">{{ $job['supported_documents'] }}</dd></div>
                            </dl>
                        </article>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">No current work recorded</p>
                    @endforelse
                </div>
                <div class="space-y-3">
                    <h3 class="font-medium text-gray-950 dark:text-white">Past work</h3>
                    @forelse ($overview['pastJobs'] as $job)
                        <article class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-950 dark:text-white">{{ $job['company_name'] }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $job['position'] ?: 'Position not recorded' }}</p>
                            <dl class="mt-3 grid gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <div><dt class="font-medium">Employment type</dt><dd>{{ $job['employment_type'] }}</dd></div>
                                <div><dt class="font-medium">Date hired</dt><dd>{{ $job['date_hired'] }}</dd></div>
                                <div><dt class="font-medium">End date</dt><dd>{{ $job['ended_at'] ?: 'Not recorded' }}</dd></div>
                                <div><dt class="font-medium">Document reference</dt><dd class="break-all">{{ $job['supported_documents'] }}</dd></div>
                            </dl>
                        </article>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">No past work recorded</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if ($hasRecord && $this->canSave())
            <x-filament::button class="mt-4" wire:click="openEditor" aria-controls="alumni-information-editor" aria-expanded="{{ $editing ? 'true' : 'false' }}">
                Update work
            </x-filament::button>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Education history</x-slot>
        @forelse ($overview['education'] as $education)
            <article class="border-b border-gray-200 py-3 last:border-0 dark:border-gray-700">
                <h3 class="font-semibold text-gray-950 dark:text-white">{{ $education['instituion'] }}</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $education['program'] }} · {{ $education['degree_level'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $education['status'] }} · {{ $education['started_at'] }} to {{ $education['ended_at'] }}</p>
            </article>
        @empty
            <p class="text-sm text-gray-600 dark:text-gray-400">No education history recorded</p>
        @endforelse

        @if ($hasRecord && $this->canSave())
            <x-filament::button class="mt-4" wire:click="openEditor" aria-controls="alumni-information-editor" aria-expanded="{{ $editing ? 'true' : 'false' }}">
                Update education
            </x-filament::button>
        @endif
    </x-filament::section>

    <div id="alumni-information-editor">
        @if ($editing)
            <x-filament::section>
                <x-slot name="heading">{{ $hasRecord ? 'My alumni information' : 'Complete my alumni information' }}</x-slot>
                <x-slot name="description">
                    Keep your contact details, further studies, and employment history current.
                    @if ($hasRecord)
                        Contact the administrator to correct your name, Student ID, program, or graduation date.
                    @endif
                </x-slot>

                @if ($saved)
                    <p role="status" class="text-sm text-success-600 dark:text-success-400">Your alumni information has been saved.</p>
                @endif

                @error('data')
                    <p role="alert" class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </x-filament::section>

            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                @if ($this->canSave())
                    <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                        {{ $hasRecord ? 'Save changes' : 'Save my information' }}
                    </x-filament::button>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your account has view-only access to this information.</p>
                @endif
            </form>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
