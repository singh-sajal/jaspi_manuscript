<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteReviewer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

     protected $casts = [
        'last_invited_at' => 'datetime',
    ];

    // True if this reviewer has already registered as an Admin
    public function getIsRegisteredAttribute(): bool
    {
        return Admin::where('email', $this->email)->exists();
    }
}
