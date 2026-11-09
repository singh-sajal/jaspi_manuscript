<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'slug',
        'title',
        'description',
        'img',
        'class',
        'link',
        'alt',
        'status',
    ];
}
