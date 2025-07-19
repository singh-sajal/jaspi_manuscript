<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Consultant extends Authenticatable
{
    use HasFactory, HasUuid, Notifiable;
    protected $guarded = ['id'];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'profile' => 'object',
        'attributes' => 'object',
        'specialization' => 'array'
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }
    // Morphing to activity model   Crate simple as
    // $this->activities()->create(['data' => $data]);

    public function activities()
    {
        return $this->morphMany(Activity::class, 'loggable');
    }
}
