<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniEducation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
