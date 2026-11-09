<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InactivityMail extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_BULK = 'bulk';
    public const TYPE_INDIVIDUAL = 'individual';

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sent_by', 'id');
    }
}
