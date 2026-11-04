<?php

namespace App\Models;

// use App\Traits\HasAuditUsers;
use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    // use HasAuditUsers;
    protected $fillable = [
        'user_id',
        'token',
        'fingerprint',
        'ip_address',
        'user_agent',
        'device_model',
        'device_platform',
        'device_platform_version',
        'expires_at',
    ];

    protected $casts = ['expires_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}