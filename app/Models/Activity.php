<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];
    protected $casts = [
        'data' => 'object',
    ];
    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
    public function loggable()
    {
        return $this->morphTo('loggable');
    }
}
