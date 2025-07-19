<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickLink extends Model
{
    use HasFactory, HasUuid;
    protected $guarded = ['id'];
    protected $casts = [
        'attributes' => 'array',
    ];

    // Adding Builder Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }



    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
}
