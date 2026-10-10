@php
    $employmentStatusLabel = match ($alumni->employment_status) {
        'employed' => 'Employed',
        'unemployed' => 'Unemployed',
        'untraced' => 'Untraced',
        default => 'Not specified',
    };

    $employmentStatusClasses = match ($alumni->employment_status) {
        'employed' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-300 dark:ring-success-400/20',
        'unemployed' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/20',
        'untraced' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-300 dark:ring-danger-400/20',
        default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-white/10 dark:text-gray-300 dark:ring-white/20',
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-white/5">
        <div class="min-w-0">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Student number</p>
            <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $alumni->student_number }}</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $alumni->program?->program_name ?? 'No department assigned' }}</p>
        </div>

        <span @class([
            'inline-flex w-fit items-center rounded-md px-2.5 py-1 text-sm font-medium ring-1 ring-inset',
            $employmentStatusClasses,
        ])>
            {{ $employmentStatusLabel }}
        </span>
    </div>

    <x-filament::section heading="Profile and tracing" icon="heroicon-o-user">
        <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="mt-1 break-words text-sm text-gray-950 dark:text-white">{{ $alumni->email ?: 'Not provided' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone number</dt>
                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $alumni->phone_number ?: 'Not provided' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Graduation date</dt>
                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $alumni->graduation_year ? \Illuminate\Support\Carbon::parse($alumni->graduation_year)->format('M d, Y') : 'Not provided' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current address</dt>
                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $alumni->current_address ?: 'Not provided' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last traced</dt>
                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $alumni->date_traced ? \Illuminate\Support\Carbon::parse($alumni->date_traced)->format('M d, Y') : 'Not traced' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Traced by</dt>
                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $alumni->trace_by ?: 'Not specified' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Remarks</dt>
                <dd class="mt-1 whitespace-pre-line text-sm text-gray-950 dark:text-white">{{ $alumni->remarks ?: 'No remarks' }}</dd>
            </div>
        </dl>
    </x-filament::section>

    <x-filament::section heading="Education history" icon="heroicon-o-academic-cap">
        <div class="grid gap-4">
            @forelse ($alumni->alumniEducation as $education)
                <article class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-950 dark:text-white">{{ $education->instituion }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $education->program }}</p>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $education->status }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Degree level</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $education->degree_level }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Units completed</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $education->units_completed }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Period</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $education->started_at }} to {{ $education->ended_at ?: 'Present' }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No education records.</p>
            @endforelse
        </div>
    </x-filament::section>

    <x-filament::section heading="Employment history" icon="heroicon-o-briefcase">
        <div class="grid gap-4">
            @forelse ($alumni->alumniEmployment as $employment)
                <article class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-950 dark:text-white">{{ $employment->company_name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $employment->position ?: 'Position not specified' }}</p>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $employment->is_current ? 'Current role' : 'Previous role' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Company address</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->company_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Industry</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->industry ?: 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Employment type</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->employment_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Course related</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->is_course_related === null ? 'Not specified' : ($employment->is_course_related ? 'Yes' : 'No') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Date hired</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->date_hired ?: 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Period</dt>
                            <dd class="mt-1 text-gray-950 dark:text-white">{{ $employment->starting_date ?: 'Not specified' }} to {{ $employment->ended_at ?: 'Present' }}</dd>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <dt class="text-gray-500 dark:text-gray-400">Supporting documents</dt>
                            <dd class="mt-1 break-words text-gray-950 dark:text-white">{{ $employment->supported_documents ?: 'None attached' }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No employment records.</p>
            @endforelse
        </div>
    </x-filament::section>
</div>
