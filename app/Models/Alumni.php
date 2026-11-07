<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Alumni extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function scopeGraduatedInYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('graduation_year', $year);
    }

    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    public function scopeWithEmploymentStatus(Builder $query, string $employmentStatus): Builder
    {
        return $query->where('employment_status', $employmentStatus);
    }

    public function scopeLatestPerAccount(Builder $query): Builder
    {
        return $query->whereNotExists(function (QueryBuilder $newerAlumni): void {
            $newerAlumni->selectRaw('1')
                ->from('alumnis as newer_alumni')
                ->whereColumn('newer_alumni.user_id', 'alumnis.user_id')
                ->whereNull('newer_alumni.deleted_at')
                ->where(function (QueryBuilder $timestamp): void {
                    $timestamp->whereColumn('newer_alumni.updated_at', '>', 'alumnis.updated_at')
                        ->orWhere(function (QueryBuilder $tie): void {
                            $tie->whereColumn('newer_alumni.updated_at', 'alumnis.updated_at')
                                ->whereColumn('newer_alumni.id', '>', 'alumnis.id');
                        });
                });
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function alumniEmployment(): HasMany
    {
        return $this->hasMany(AlumniEmployment::class);
    }

    public function alumniEducation(): HasMany
    {
        return $this->hasMany(AlumniEducation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
