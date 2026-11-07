<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function userAllocations()
    {
        return $this->belongsToMany(
            UserAllocation::class,
            'campus_user_allocations',
            'campus_id',
            'user_allocation_id'
        );
    }
}
