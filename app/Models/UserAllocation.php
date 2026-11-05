<?php

namespace App\Models;

use CachingIterator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAllocation extends Model
{
    
    use SoftDeletes;

    protected $guarded = [];
    
    protected static function booted()
    {
        static::creating(function ($userallocation) {

            if (empty($userallocation->code)) {
                $userallocation->code = self::generateCode();
            }
        });

        static::saved(function ($userallocation) {
            if ($userallocation->management_scope !== 'specified_departments') {
                $userallocation->campuses()->detach();
                $userallocation->programs()->detach();
            }
        });
    }

    protected static function generateCode()
    {
        $prefix = 'CTU-UA';

        $lastId = self::withTrashed()->max('id') ?? 0;

        $nextNumber = $lastId + 1;

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class)->withTrashed();
    }

    // public function department()
    // {
    //     return $this->belongsTo(Department::class,)->withTrashed();
    // }

    public function campuses()
    {
        return $this->belongsToMany(
            Campus::class,
            'campus_user_allocations',
            'user_allocation_id',
            'campus_id'
        )->withTimestamps();
    }

    public function programs()
    {
        return $this->belongsToMany(
            Program::class,
            'program_user_allocations',
            'user_allocation_id',
            'program_id'
        )->withTimestamps();
    }


    public function role()
    {
        return $this->belongsTo(Role::class)->withTrashed();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    

}
