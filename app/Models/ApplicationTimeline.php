<?php

namespace App\Models;

use App\Models\Admin;
use App\Traits\HasUuid;
use App\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationTimeline extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];
    protected $casts = ['data'=>'array'];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }

    public function assignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to_id', 'id');
    }
}
