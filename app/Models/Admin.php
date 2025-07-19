<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasUuid, Notifiable, HasRoles;
    protected $guarded = ['id'];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [];
    // Getting the profile Attribute to convert as json

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
    // Morphing to activity model
    public function activities()
    {
        return $this->morphMany(Activity::class, 'loggable');
    }

    // public function role(){
    //     return $this->hasMany(Package::class,'destination_id','id');
    // }
}
