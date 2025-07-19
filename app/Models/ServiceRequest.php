<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];
    protected $casts = [
        'timelines' => 'array',
        'attributes' => 'array',
    ];
}
