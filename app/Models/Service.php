<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, HasUuid, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'attributes' => 'object',
    ];
    // Add Scope for uuid
    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
    // Add scope active
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // add scope slug
    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id', 'id');
    }
    public function subservices()
    {
        return $this->hasMany(Service::class, 'parent_id', 'id');
    }
}
