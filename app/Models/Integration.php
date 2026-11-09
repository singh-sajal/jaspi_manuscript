<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_integration');
    }
}